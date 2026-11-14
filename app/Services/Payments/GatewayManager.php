<?php

namespace App\Services\Payments;

use App\Models\Payment;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

/**
 * Bank payments through the gateways the admin manages (admin › درگاه‌های پرداخت, table payment_gateways).
 *
 *   start():        tries the enabled gateways in the admin's order until one opens a payment session, and records
 *                   on the payment which gateway (payments.gateway) and its reference (payments.authority).
 *   fromCallback(): finds the payment a gateway sends the buyer back with (Zarinpal ?Authority=, Zibal ?trackId=).
 *   verify():       confirms it with that same gateway.
 *
 * Merchant IDs are stored encrypted with APP_KEY. In test mode a gateway uses its public test merchant (Zibal:
 * "zibal"). Zarinpal falls back to ZARINPAL_MERCHANT_ID in .env while none is saved here.
 */
class GatewayManager
{
    public const GATEWAYS = [
        'zibal' => ['label' => 'زیبال', 'class' => ZibalGateway::class, 'testMerchant' => 'zibal'],
        'zarinpal' => ['label' => 'زرین‌پال', 'class' => ZarinpalGateway::class, 'testMerchant' => null],
    ];

    public function gateway(string $key): ?Gateway
    {
        $class = self::GATEWAYS[$key]['class'] ?? null;
        return $class ? app($class) : null;
    }

    /** Every known gateway with its settings, in the admin's order. */
    public function rows(): Collection
    {
        $saved = DB::table('payment_gateways')->get()->keyBy('key');
        return collect(self::GATEWAYS)->map(function ($meta, $key) use ($saved) {
            $row = $saved[$key] ?? null;
            return (object) [
                'key' => $key,
                'label' => $meta['label'],
                'enabled' => (bool) ($row->enabled ?? false),
                'sort' => (int) ($row->sort ?? 99),
                'sandbox' => (bool) ($row->sandbox ?? false),
                'merchant' => $this->decrypt($row->merchant ?? null),
                'testMerchant' => $meta['testMerchant'],
                'testedAt' => $row->tested_at ?? null,
                'testResult' => $row->test_result ?? null,
            ];
        })->sortBy('sort')->values();
    }

    /** The merchant a gateway uses now: its test merchant in test mode, else the saved one (Zarinpal: else .env). */
    public function merchant(object $row): ?string
    {
        if ($row->sandbox && $row->testMerchant) {
            return $row->testMerchant;
        }
        if ($row->merchant) {
            return $row->merchant;
        }
        return $row->key === 'zarinpal' ? (config('services.zarinpal.merchant_id') ?: null) : null;
    }

    /** Gateways that can take payments now, in order. */
    public function active(): Collection
    {
        return $this->rows()->filter(fn ($r) => $r->enabled && $this->merchant($r))->values();
    }

    /**
     * Opens a payment session for `$payment` and returns the address to send the buyer to, or null when no gateway
     * could (the reasons are saved on the payment's `error`). Amount in Rial.
     */
    public function start(Payment $payment, int $rial, string $callbackUrl, string $description, array $extra = []): ?string
    {
        $errors = [];
        foreach ($this->active() as $row) {
            $result = $this->gateway($row->key)->start($this->merchant($row), $rial, $callbackUrl, $description, $extra + ['orderId' => $payment->id]);
            if ($result->ok) {
                $payment->gateway = $row->key;
                $payment->authority = $result->reference;
                $payment->error = $errors ? implode(' | ', $errors) : null; // earlier gateways that failed, for the record
                $payment->save();
                return $result->url;
            }
            $errors[] = $result->error;
        }
        $payment->error = $errors ? implode(' | ', $errors) : 'هیچ درگاه پرداخت فعالی تنظیم نشده است.';
        $payment->save();
        return null;
    }

    /** [Payment, gateway key] the callback request belongs to, or null. */
    public function fromCallback(Request $request): ?array
    {
        foreach (array_keys(self::GATEWAYS) as $key) {
            $reference = $this->gateway($key)->referenceFrom($request);
            if ($reference === null) {
                continue;
            }
            $payment = Payment::where('authority', $reference)
                ->where(fn ($q) => $q->where('gateway', $key)->when($key === 'zarinpal', fn ($q) => $q->orWhereNull('gateway')))
                ->first();
            if ($payment) {
                return [$payment, $key];
            }
        }
        return null;
    }

    public function callbackPaid(Request $request, string $key): bool
    {
        return $this->gateway($key)->callbackPaid($request);
    }

    /** Confirms a payment with the gateway it went through. */
    public function verify(Payment $payment, int $rial): VerifyResult
    {
        $key = $payment->gateway ?: 'zarinpal';
        $row = $this->rows()->firstWhere('key', $key);
        $merchant = $row ? $this->merchant($row) : null;
        if (!$merchant) {
            return new VerifyResult(false, message: 'درگاه این پرداخت دیگر تنظیم نیست. با پشتیبانی تماس بگیرید.');
        }
        return $this->gateway($key)->verify($merchant, (string) $payment->authority, $rial);
    }

    /**
     * Admin's "test connection": opens a 10,000 Rial session (nothing is charged; it expires unpaid) to check the
     * merchant, and records the outcome.
     */
    public function test(string $key, string $callbackUrl): array
    {
        $row = $this->rows()->firstWhere('key', $key);
        $merchant = $row ? $this->merchant($row) : null;
        if (!$merchant) {
            $result = [false, 'مرچنت این درگاه وارد نشده است.'];
        } else {
            $start = $this->gateway($key)->start($merchant, 10000, $callbackUrl, 'تست اتصال درگاه', []);
            $result = $start->ok ? [true, 'اتصال برقرار است.' . ($row->sandbox ? ' (حالت آزمایشی)' : '')] : [false, $start->error];
        }
        DB::table('payment_gateways')->updateOrInsert(['key' => $key], ['tested_at' => now(), 'test_result' => ($result[0] ? '✓ ' : '✗ ') . mb_substr($result[1], 0, 240), 'updated_at' => now()]);
        return $result;
    }

    public function encrypt(?string $merchant): ?string
    {
        return $merchant === null || $merchant === '' ? null : Crypt::encryptString($merchant);
    }

    private function decrypt(?string $value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (DecryptException) {
            return null;
        }
    }
}
