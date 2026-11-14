<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

/** Zarinpal v4 (https://api.zarinpal.com/pg/v4). ZarinPalMethod() also serves the local fake (DevFakeProviders). */
class ZarinpalGateway implements Gateway
{
    public function start(string $merchant, int $rial, string $callbackUrl, string $description, array $extra): StartResult
    {
        $response = ZarinPalMethod('https://api.zarinpal.com/pg/v4/payment/request.json', [
            'merchant_id' => $merchant,
            'amount' => $rial,
            'description' => $description,
            'callback_url' => $callbackUrl,
            'metadata' => array_filter(['email' => (string) ($extra['email'] ?? ''), 'mobile' => (string) ($extra['mobile'] ?? '')]),
        ]);
        if (is_array($response) && (int) ($response['data']['code'] ?? 0) === 100) {
            $authority = (string) $response['data']['authority'];
            $base = DevFakeProviders() ? DevFakeZarinPalPage() : 'https://www.zarinpal.com/pg/StartPay/';
            return new StartResult(true, $base . $authority, $authority);
        }
        $error = is_array($response) ? ($response['errors']['message'] ?? $response['error']['message'] ?? json_encode($response['errors'] ?? $response, JSON_UNESCAPED_UNICODE)) : 'no response';
        $code = is_array($response) ? ($response['errors']['code'] ?? $response['error']['code'] ?? null) : null;
        return new StartResult(false, error: 'Zarinpal' . ($code !== null ? " ($code)" : '') . ': ' . $error);
    }

    public function referenceFrom(Request $request): ?string
    {
        return $request->filled('Authority') ? (string) $request->input('Authority') : null;
    }

    public function callbackPaid(Request $request): bool
    {
        return $request->input('Status') === 'OK';
    }

    public function verify(string $merchant, string $reference, int $rial): VerifyResult
    {
        $response = ZarinPalMethod('https://api.zarinpal.com/pg/v4/payment/verify.json', [
            'merchant_id' => $merchant, 'authority' => $reference, 'amount' => $rial,
        ]);
        $data = is_array($response) ? ($response['data'] ?? null) : null;
        if (is_array($data) && in_array((int) ($data['code'] ?? 0), [100, 101], true)) {
            return new VerifyResult((int) $data['code'] === 100, (int) $data['code'] === 101,
                (string) ($data['ref_id'] ?? ''), $data['card_pan'] ?? null, isset($data['fee']) ? (int) $data['fee'] : null, (string) $data['code']);
        }
        $error = is_array($response) ? ($response['errors'] ?? $response['error'] ?? []) : [];
        return new VerifyResult(false, code: isset($error['code']) ? (string) $error['code'] : null,
            message: (string) ($error['message'] ?? 'Zarinpal: پاسخی از درگاه دریافت نشد.'));
    }
}
