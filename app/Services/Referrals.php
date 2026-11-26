<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Setting;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
 * Referral (معرفی دوستان).
 *
 *   - Every user has a code (users.token) and a link (/user/login?ref=<code>; CheckRefCode keeps it in the session).
 *     A new account can type the code on the sign-up step (filled in from the link); it is saved as users.parent_id.
 *   - settings › referral: {enabled, percents: {family: percent}}, set in admin › معرفی دوستان. Off stops everything:
 *     the sign-up field, the dashboard tile and new commission.
 *   - users.referral_percents: one referrer's own percents (admin › user › معرفی‌شده‌ها); a family not set there
 *     uses the default.
 *   - Commission is paid once per order, when the order's status becomes complete and it was paid (Order::saved
 *     calls completed()): percent × (order price − VAT), credited to the referrer's blocked wallet (spendable, not
 *     withdrawable) as a type-5 payment. If the order later turns failed / refunded, reverse() takes it back.
 *   - Admin-made orders (type 9) earn nothing.
 */
class Referrals
{
    public const PAYMENT_TYPE = 5;
    // Product families by order type (orders.type). Keys are stored in settings and users.referral_percents.
    public const FAMILIES = [
        'numbers' => 'شماره مجازی',
        'services' => 'خدمات شبکه‌های اجتماعی',
        'apple_id' => 'اپل آیدی',
        'static_ip' => 'آی‌پی ثابت',
        'google_voice' => 'گوگل ویس',
        'subscriptions' => 'اشتراک‌ها و اکانت‌ها',
        'verification' => 'وریفای',
    ];
    private const TYPES = [
        '1' => 'numbers', '2' => 'services', '3' => 'apple_id', '4' => 'apple_id', '5' => 'static_ip',
        '6' => 'google_voice', '7' => 'subscriptions', '10' => 'verification',
    ];
    private const UNDONE = ['failed', 'back money to wallet', 'cancelled', 'canceled', 'refunded'];

    private static ?array $settings = null;

    /* ---------- Settings ---------- */

    /** @return array{enabled: bool, percents: array<string, float>} */
    public static function settings(): array
    {
        if (self::$settings === null) {
            $Raw = json_decode((string)optional(Setting::where('name', 'referral')->first())->key, true) ?: [];
            self::$settings = [
                'enabled' => (bool)($Raw['enabled'] ?? false),
                'percents' => self::percents($Raw['percents'] ?? [], true),
            ];
        }
        return self::$settings;
    }

    public static function saveSettings(array $Input): array
    {
        $Row = Setting::where('name', 'referral')->first() ?? new Setting();
        $Row->name = 'referral';
        $Row->key = json_encode([
            'enabled' => (bool)($Input['enabled'] ?? false),
            'percents' => (object)self::percents($Input['percents'] ?? [], true),
        ]);
        $Row->save();
        self::$settings = null;
        return self::settings();
    }

    public static function enabled(): bool
    {
        return self::settings()['enabled'];
    }

    /** A percent typed by an admin: 0–100, two decimals; null for an empty box. */
    public static function percent($Value): ?float
    {
        $Text = trim(convertPersianNumbersToEnglish((string)$Value));
        if ($Text === '') {
            return null;
        }
        $N = (float)str_replace(['٫', '،', ','], '.', $Text);
        return round(max(0, min(100, $N)), 2);
    }

    /**
     * {family: percent} with known families only. $All: every family (empty = 0, for the defaults); otherwise
     * empty boxes are left out (a user's own percents, where missing = use the default).
     */
    public static function percents($Raw, bool $All): array
    {
        $Raw = is_string($Raw) ? (json_decode($Raw, true) ?: []) : (array)$Raw;
        $Out = [];
        foreach (array_keys(self::FAMILIES) as $Family) {
            $P = self::percent($Raw[$Family] ?? '');
            if ($P !== null) {
                $Out[$Family] = $P;
            } elseif ($All) {
                $Out[$Family] = 0.0;
            }
        }
        return $Out;
    }

    /** This referrer's own percents (only the families an admin set). */
    public static function ownPercents($User): array
    {
        return self::percents($User->referral_percents ?? [], false);
    }

    public static function saveOwnPercents($User, array $Input): array
    {
        $Own = self::percents($Input, false);
        $User->referral_percents = $Own ? json_encode($Own) : null;
        $User->save();
        return $Own;
    }

    /** The percent $Referrer earns on a family: their own, else the default. */
    public static function percentFor($Referrer, string $Family): float
    {
        return self::ownPercents($Referrer)[$Family] ?? (self::settings()['percents'][$Family] ?? 0.0);
    }

    public static function family($Order): ?string
    {
        return self::TYPES[(string)$Order->type] ?? null;
    }

    /* ---------- Sign-up ---------- */

    /** The user a typed or linked code belongs to, or null. */
    public static function referrerByCode($Code): ?User
    {
        $Code = trim(convertPersianNumbersToEnglish((string)$Code));
        return $Code === '' ? null : User::where('token', $Code)->first();
    }

    /* ---------- Commission ---------- */

    /** Order::saved: the status just changed. Never breaks the order's own save. */
    public static function statusChanged(Order $Order): void
    {
        try {
            $Status = strtolower(trim((string)$Order->status));
            if ($Status === 'complete') {
                self::completed($Order);
            } elseif (in_array($Status, self::UNDONE, true)) {
                self::reverse($Order);
            }
        } catch (\Throwable $e) {
            Log::error('Referral commission failed for order ' . $Order->id . ': ' . $e->getMessage());
        }
    }

    /** Pays the commission for a completed, paid order once. Returns the payment, or null when nothing is due. */
    public static function completed(Order $Order): ?Payment
    {
        if (!self::enabled() || strtolower((string)$Order->pay_status) !== 'complete') {
            return null;
        }
        $Family = self::family($Order);
        $Buyer = $Order->user_id ? User::find($Order->user_id) : null;
        if (!$Family || !$Buyer || (int)$Buyer->parent_id <= 0 || (int)$Buyer->parent_id === (int)$Buyer->id) {
            return null;
        }
        $Referrer = User::find($Buyer->parent_id);
        if (!$Referrer) {
            return null;
        }
        $Base = max(0, (int)$Order->price - (int)$Order->vat_price);
        $Amount = (int)floor($Base * self::percentFor($Referrer, $Family) / 100);
        if ($Amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($Order, $Referrer, $Amount, $Family) {
            $Referrer = User::lockForUpdate()->find($Referrer->id);
            if (Payment::where('order_id', $Order->id)->where('type', self::PAYMENT_TYPE)->where('status', 'Complete')->lockForUpdate()->exists()) {
                return null; // already paid (the order was saved as complete again)
            }
            $Payment = new Payment();
            $Payment->price = $Amount;
            $Payment->type = self::PAYMENT_TYPE;
            $Payment->order_id = $Order->id;
            $Payment->user_id = $Referrer->id;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->help = "پورسانت معرفی سفارش شماره {$Order->id} (" . self::FAMILIES[$Family] . ')';
            $Payment->paid_at = now();
            $Payment->save();

            $Referrer->blocked_wallet = (int)$Referrer->blocked_wallet + $Amount;
            $Referrer->save();
            return $Payment;
        });
    }

    /** Takes back an order's commission when the order is undone (from the blocked wallet, as far as it goes). */
    public static function reverse(Order $Order): void
    {
        DB::transaction(function () use ($Order) {
            $Payment = Payment::where('order_id', $Order->id)->where('type', self::PAYMENT_TYPE)->where('status', 'Complete')->lockForUpdate()->first();
            if (!$Payment) {
                return;
            }
            $Referrer = User::lockForUpdate()->find($Payment->user_id);
            $Taken = 0;
            if ($Referrer) {
                $Taken = min((int)$Referrer->blocked_wallet, (int)$Payment->price);
                $Referrer->blocked_wallet = (int)$Referrer->blocked_wallet - $Taken;
                $Referrer->save();
            }
            $Payment->status = 'Cancelled';
            $Payment->help = trim($Payment->help . ' — لغو شد، سفارش برگشت خورد' . ($Taken < (int)$Payment->price ? ' (' . number_format($Taken) . ' تومان از موجودی مسدود کم شد)' : ''));
            $Payment->save();
        });
    }

    /* ---------- Numbers ---------- */

    public static function earned($User): int
    {
        return (int)Payment::where('user_id', $User->id)->where('type', self::PAYMENT_TYPE)->where('status', 'Complete')->sum('price');
    }
}
