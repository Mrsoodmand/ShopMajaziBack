<?php

namespace App\Services;

use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\GoogleVoice;
use App\Models\History;
use App\Models\Ip;
use App\Models\Order;
use App\Models\Product;
use App\User;
use Carbon\Carbon;

/**
 * What was handed to the customer for the stock order types, for admin › order («تحویل»): Apple ID (3 ready,
 * 4 custom), static IP (5) and Google Voice (6). Logins are decrypted for support, as the old Blade order page did.
 * Also finishes a custom Apple ID order (support types in the login it made for the customer).
 */
class OrderDelivery
{
    const TYPES = ['3', '4', '5', '6'];

    /** null for other order types. */
    public static function for(Order $order): ?array
    {
        $type = (string) $order->type;
        if (!in_array($type, self::TYPES, true)) {
            return null;
        }
        $items = match ($type) {
            '3', '4' => self::appleIds($order),
            '5' => self::ips($order),
            '6' => self::googleVoices($order),
        };

        return [
            'kind' => match ($type) { '3', '4' => 'apple', '5' => 'ip', '6' => 'gv' },
            'items' => $items,
            // Custom Apple ID: support makes the account by hand, then types its login in here.
            'canCompleteAppleId' => $type === '4' && count($items) === 0
                && !in_array($order->status, ['failed', 'back money to wallet'], true),
        ];
    }

    private static function decrypt($serial, $code): array
    {
        if (!$serial || !$code) {
            return [];
        }
        return json_decode((string) PhpEncrypt::decrypt($serial, $code), true) ?: [];
    }

    private static function row(string $label, $value, bool $secret = false): array
    {
        $value = trim((string) $value);
        return ['label' => $label, 'value' => $value !== '' ? $value : null, 'copy' => true, 'ltr' => true, 'secret' => $secret];
    }

    private static function appleIds(Order $order): array
    {
        return AppleId::where('order_id', $order->id)->where('status', 1)->orderBy('id')->get()->map(function ($a) {
            $d = self::decrypt($a->serial, $a->code);
            return [
                'id' => $a->id,
                'title' => (string) ($d['appleid'] ?? $a->apple_id),
                'link' => '/apple-ids/' . $a->id,
                'rows' => [
                    self::row('اپل آیدی', $d['appleid'] ?? $a->apple_id),
                    self::row('کلمه عبور', $d['password'] ?? null, true),
                    self::row('تاریخ تولد', $d['brithday'] ?? null),
                    self::row('سوال امنیتی ۱', $d['q1'] ?? null, true),
                    self::row('سوال امنیتی ۲', $d['q2'] ?? null, true),
                    self::row('سوال امنیتی ۳', $d['q3'] ?? null, true),
                ],
            ];
        })->values()->all();
    }

    private static function googleVoices(Order $order): array
    {
        return GoogleVoice::where('order_id', $order->id)->where('status', 1)->orderBy('id')->get()->map(function ($g) {
            $d = self::decrypt($g->serial, $g->code);
            return [
                'id' => $g->id,
                'title' => (string) ($d['number'] ?? $g->number),
                'link' => '/google-voice/' . $g->id,
                'rows' => [
                    self::row('شماره', $d['number'] ?? $g->number),
                    self::row('ایمیل', $d['email'] ?? null),
                    self::row('کلمه عبور', $d['password'] ?? null, true),
                    self::row('ایمیل بازیابی', $d['backup-email'] ?? null),
                    self::row('کد دومرحله‌ای', $d['2fa'] ?? null, true),
                ],
            ];
        })->values()->all();
    }

    /** The client config made for the order (Ip rows of type client; parent_id = its server). */
    private static function ips(Order $order): array
    {
        return Ip::where('order_id', $order->id)->orderBy('id')->get()->map(function ($ip) {
            $d = json_decode((string) $ip->detail, true) ?: [];
            $country = $ip->category_id ? Category::find($ip->category_id) : null;
            $plan = $ip->product_id ? Product::find($ip->product_id) : null;
            $rows = [
                ['label' => 'کشور', 'value' => $country ? ($country->name ?: null) : null],
                ['label' => 'پلن', 'value' => $plan ? ($plan->faName ?: null) : null],
                ['label' => 'حجم', 'value' => $ip->volume !== null ? number_format((int) $ip->volume) . ' گیگ' : null],
                ['label' => 'انقضا', 'value' => $ip->expire_date ? (new \App\lib\Jdf())->jdate('Y/m/d', strtotime((string) $ip->expire_date)) : null],
                self::row('Remark', $d['user_remark'] ?? null),
                self::row('Port', $d['user_port'] ?? null),
                self::row('کد', $d['user_code'] ?? null, true),
            ];
            return [
                'id' => $ip->id,
                'title' => (string) ($d['user_remark'] ?? ('#' . $ip->id)),
                'link' => $ip->category_id ? '/ip/' . $ip->category_id : null,
                'rows' => $rows,
            ];
        })->values()->all();
    }

    /**
     * Custom Apple ID (type 4): stores the login support made (encrypted, like AppleIdApiController), completes the
     * order, texts the customer and writes the order history. Same steps as the old Admin\OrderController.
     */
    public static function completeAppleId(Order $order, array $login, int $adminId): void
    {
        $serial = (string) random_int(1111111111111111, 9999999999999999); // same key/IV scheme as the old page

        $apple = new AppleId();
        $apple->apple_id = $login['appleid'];
        $apple->code = PhpEncrypt::encrypt($serial, $serial, json_encode($login));
        $apple->serial = $serial;
        $apple->status = 1;
        $apple->type = 2;
        $apple->order_id = $order->id;
        $apple->user_id = $order->user_id;
        $apple->operator_id = $adminId;
        $apple->save();

        $old = $order->status;
        $order->status = 'complete';
        $order->save();

        $user = User::find($order->user_id);
        if ($user && $user->mobile) {
            sendSmsWithSmsir($user->mobile, ['NUM' => $order->id, 'STATUS' => 'تکمیل شده'], 138554);
        }

        $history = new History();
        $history->type = 'order';
        $history->post_id = $order->id;
        $history->detail = json_encode([
            'old_status' => trim((string) __('order.status.' . $old)) ?: $old,
            'new_status' => 'تکمیل شده',
            'detail' => 'تکمیل سفارش اپل آیدی سفارشی',
            'changed_date' => Carbon::now(),
        ]);
        $history->admin_id = $adminId;
        $history->save();
    }
}
