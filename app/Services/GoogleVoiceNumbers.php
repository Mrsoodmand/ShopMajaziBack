<?php

namespace App\Services;

use App\lib\PhpEncrypt;
use App\Models\GoogleVoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Models\Payment;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/*
 * Google Voice numbers for the Next.js storefront, sold from Numberland's "special numbers" (spnumberslist /
 * getspnumber). Numberland's price is our cost; we sell at cost + GOOGLE_VOICE_MARKUP_PERCENT, rounded up to 1,000
 * Toman. A bought number is saved as a sold GoogleVoice row (same encrypted login format the admin panel uses), so
 * the old user panel and admin order pages show it too.
 *
 * Orders are type 6. OrderItem detail: {numberland_id, number, cost} until delivered, then also product_id (the
 * GoogleVoice row). Order status: complete = delivered, failed = Numberland refused (money back in the wallet),
 * review = Numberland gave no clear answer (the number may have been bought: support checks before refunding),
 * preorder = paid and waiting for a number.
 *
 * Pre-orders: when Numberland can't sell (no answer, an error, our balance too low, nothing in stock) the buyer can
 * pay the admin's pre-order price instead. A paid order Numberland then fails also becomes a pre-order (when
 * pre-orders are on), instead of a refund or `review`. fillPreorders() (command gv:fill-preorders, every 5 minutes)
 * buys a number for each, oldest first, when one sells at or under what the buyer paid. A pre-order made from an
 * unclear Numberland answer has detail.unclear set: that number may already be ours, so it is never filled
 * automatically; an admin checks Numberland first (admin › order). Pre-order detail: {preorder: true, wanted?,
 * tries, unclear?}; once a number is picked for it, numberland_id/number/cost are added as for a normal order.
 */
class GoogleVoiceNumbers
{
    public const ORDER_TYPE = 6;
    public const PREORDER_PRICE = 1500000; // Toman, until an admin sets one
    private const PREORDER_TRIES = 20; // automatic fill attempts before only an admin retries
    private const LIST_SECONDS = 60;
    private const API = 'https://api.numberland.ir/v2.php/?';

    /* Numbers for sale: [['id', 'number', 'price', 'round' (0 not round, 1 half, 2 fully)]]. */
    public function list(bool $Fresh = false): array
    {
        $Load = function () {
            $Body = $this->call(['method' => 'spnumberslist']);
            if (!is_array($Body) || (int)($Body['RESULT'] ?? 0) !== 1 || !is_array($Body['DESCRIPTION'] ?? null)) {
                throw new \RuntimeException('Numberland spnumberslist: ' . json_encode($Body));
            }
            return array_values(array_map(fn($n) => [
                'id' => (int)$n['ID'],
                'number' => (string)$n['NUMBER'],
                'cost' => (int)$n['PRICE'],
                'round' => (int)($n['TYPE'] ?? 0),
            ], $Body['DESCRIPTION']));
        };
        // The cache keeps Numberland's costs; the markup is added on every read, so an admin change shows at once.
        if ($Fresh) {
            $List = $Load();
            Cache::put('gv-numberland-costs', $List, self::LIST_SECONDS);
        } else {
            $List = Cache::remember('gv-numberland-costs', self::LIST_SECONDS, $Load);
        }
        $Markup = $this->settings()['markup'];
        return array_map(fn($n) => $n + ['price' => $this->sellPrice($n['cost'], $Markup)], $List);
    }

    /* One number, asked fresh from Numberland (before taking money). */
    public function find(int $Id): ?array
    {
        foreach ($this->list(true) as $Number) {
            if ($Number['id'] === $Id) {
                return $Number;
            }
        }
        return null;
    }

    /* Our Numberland balance covers this cost? Checked before taking the buyer's money. */
    public function canAfford(int $Cost): bool
    {
        $Body = $this->call(['method' => 'balance']);
        if (!is_array($Body) || (int)($Body['RESULT'] ?? 0) !== 1) {
            throw new \RuntimeException('Numberland balance: ' . json_encode($Body));
        }
        $Balance = (int)$Body['BALANCE'];
        if ($Balance < $Cost) {
            // Tell the team once an hour, so someone tops up Numberland.
            if (Cache::add('gv-low-balance-alert', true, 3600)) {
                SendTelegramMessage("⚠️ موجودی نامبرلند برای فروش گوگل ویس کافی نیست\n💰 موجودی: " . number_format($Balance) . " تومان\n📞 قیمت هر شماره: " . number_format($Cost) . " تومان\n#گوگل_ویس", -1001509696194);
            }
            Log::warning('Google Voice: Numberland balance too low', ['balance' => $Balance, 'cost' => $Cost]);
            return false;
        }
        return true;
    }

    /*
     * Admin settings (admin › Google Voice, saved in pages #4 detail): sales on/off and the markup %. Until an admin
     * saves them, sales are on and the markup is GOOGLE_VOICE_MARKUP_PERCENT.
     */
    public function settings(): array
    {
        $Detail = json_decode((string)optional(Pages::find(4))->detail, true) ?: [];
        return [
            'enabled' => (int)($Detail['numberland_enabled'] ?? 1) === 1,
            'markup' => (float)($Detail['numberland_markup'] ?? config('services.numberland.gv_markup', 20)),
            // Pre-orders: taken (paid) when Numberland can't sell right now; filled later (see fillPreorders()).
            'preorder' => (int)($Detail['preorder_enabled'] ?? 1) === 1,
            'preorder_price' => (int)($Detail['preorder_price'] ?? self::PREORDER_PRICE),
            'preorder_autofill' => (int)($Detail['preorder_autofill'] ?? 1) === 1,
        ];
    }

    /* The pre-order offer the storefront shows, or null when pre-orders are off (or sales are switched off). */
    public function preorderOffer(): ?array
    {
        $S = $this->settings();
        return $S['enabled'] && $S['preorder'] && $S['preorder_price'] > 0 ? ['price' => $S['preorder_price']] : null;
    }

    public function enabled(): bool
    {
        return $this->settings()['enabled'];
    }

    public function sellPrice(int $Cost, ?float $Markup = null): int
    {
        $Markup ??= $this->settings()['markup'];
        return (int)(ceil($Cost * (1 + $Markup / 100) / 1000) * 1000);
    }

    /* A wallet purchase: takes the money, then buys from Numberland. Returns the order. */
    public function buyWithWallet($User, array $Number, bool $Deliver = true): Order
    {
        $Order = DB::transaction(function () use ($User, $Number) {
            $Order = $this->newOrder($User, $Number, empty($Number['preorder']) ? 'pending' : 'preorder', 'Complete');
            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = $User->id;
            $Payment->type = 1;
            $Payment->status = 'Complete';
            $Payment->gate = 'Wallet';
            $Payment->creator = 'System';
            $Payment->price = $Order->price;
            $Payment->paid_at = Carbon::now();
            $Payment->save();
            $Order->pay_id = $Payment->id;
            $Order->save();
            DecrementWallet($Order->price, $User);
            return $Order;
        });
        if ($Deliver) {
            $this->deliver($Order);
        }
        return $Order->fresh();
    }

    /*
     * A pre-order paid from the wallet. $Wanted: the number the buyer had picked when Numberland failed (a hint for
     * support; any number may fill the order). Returns the order (status preorder).
     */
    public function preorderWithWallet($User, ?array $Wanted = null): Order
    {
        $Order = $this->buyWithWallet($User, $this->preorderItem($Wanted), false);
        $this->notify($Order, 'پیش‌سفارش ثبت شد (پرداخت از کیف پول)');
        return $Order;
    }

    /* A pre-order paid by card: the callback marks it paid (preorderPaid()). Returns [order, payment]. */
    public function preorderBankOrder($User, ?array $Wanted = null): array
    {
        return $this->bankOrder($User, $this->preorderItem($Wanted));
    }

    /* The bank paid a pre-order: it now waits for a number. */
    public function preorderPaid(Order $Order): void
    {
        if ($Order->status === 'waiting to pay') {
            $Order->status = 'preorder';
            $Order->save();
            $this->notify($Order, 'پیش‌سفارش ثبت شد (درگاه بانک)');
        }
    }

    public static function isPreorder(?OrderItem $Item): bool
    {
        return $Item && !empty((json_decode((string)$Item->detail, true) ?: [])['preorder']);
    }

    private function preorderItem(?array $Wanted): array
    {
        return [
            'id' => 0,
            'number' => null,
            'cost' => null,
            'price' => $this->settings()['preorder_price'],
            'preorder' => true,
            'wanted' => $Wanted ? ['id' => (int)$Wanted['id'], 'number' => (string)$Wanted['number']] : null,
        ];
    }

    /*
     * Fills pre-orders, oldest first: buys a number selling at or under what the buyer paid. Skips unclear ones
     * (see the class comment) and stops when Numberland can't be reached or our balance is short. With $Only, just
     * that order (admin › order › retry), unclear or not, however many tries it had. Returns [order id => result].
     */
    public function fillPreorders(?int $Only = null): array
    {
        $Results = [];
        $Orders = Order::where('type', self::ORDER_TYPE)->where('status', 'preorder')
            ->when($Only, fn($q) => $q->where('id', $Only))->orderBy('id')->limit(20)->get();
        if ($Orders->isEmpty()) {
            return $Results;
        }
        try {
            $List = $this->list(true);
        } catch (\Throwable $e) {
            Log::info('Google Voice pre-orders: Numberland list unavailable', ['error' => $e->getMessage()]);
            return $Only ? [$Only => 'unavailable'] : $Results;
        }
        usort($List, fn($a, $b) => $a['cost'] <=> $b['cost']);
        $Used = [];
        foreach ($Orders as $Order) {
            $Item = OrderItem::where('order_id', $Order->id)->first();
            $Detail = json_decode((string)optional($Item)->detail, true) ?: [];
            if (!$Item || (!$Only && (!empty($Detail['unclear']) || ($Detail['tries'] ?? 0) >= self::PREORDER_TRIES))) {
                continue;
            }
            // The number the buyer picked, if it is still for sale at their price; else the cheapest that fits.
            $Pick = null;
            $WantedId = (int)($Detail['wanted']['id'] ?? 0);
            foreach ($WantedId ? array_merge(array_filter($List, fn($n) => $n['id'] === $WantedId), $List) : $List as $N) {
                if (!in_array($N['id'], $Used, true) && $N['price'] <= (int)$Order->price) {
                    $Pick = $N;
                    break;
                }
            }
            if (!$Pick) {
                $Results[$Order->id] = 'no_number';
                continue;
            }
            try {
                if (!$this->canAfford($Pick['cost'])) {
                    $Results[$Order->id] = 'balance';
                    break; // the next ones won't fit either
                }
            } catch (\Throwable $e) {
                $Results[$Order->id] = 'unavailable';
                break;
            }
            $Used[] = $Pick['id'];
            $Claimed = Cache::lock('gv-deliver:' . $Order->id, 60)->get(function () use ($Order, $Item, $Detail, $Pick) {
                if ($Order->fresh()->status !== 'preorder') {
                    return false;
                }
                unset($Detail['unclear']);
                $Item->detail = json_encode(['numberland_id' => $Pick['id'], 'number' => $Pick['number'], 'cost' => $Pick['cost'], 'tries' => ($Detail['tries'] ?? 0) + 1] + $Detail);
                $Item->save();
                Order::where('id', $Order->id)->update(['status' => 'pending']);
                return true;
            });
            $Results[$Order->id] = $Claimed ? $this->deliver($Order->fresh()) : 'busy';
        }
        return $Results;
    }

    /*
     * Delivers a pre-order by hand (admin › order): a login the admin typed, e.g. from Numberland's panel or our
     * own stock. Returns false when the order is no longer waiting.
     */
    public function deliverByHand(Order $Order, array $Login): bool
    {
        return (bool)Cache::lock('gv-deliver:' . $Order->id, 60)->block(10, function () use ($Order, $Login) {
            $Order = $Order->fresh();
            if (!in_array($Order->status, ['preorder', 'review', 'pending'], true)) {
                return false;
            }
            $Item = OrderItem::where('order_id', $Order->id)->first();
            $Detail = json_decode((string)$Item->detail, true) ?: [];
            $Detail['number'] = $Login['number'];
            $this->saveDelivered($Order, $Item, $Detail, [
                'NUMBER' => $Login['number'], 'USERNAME' => $Login['email'], 'PASSWORD' => $Login['password'] ?? '', 'RECOVERY' => $Login['recovery'] ?? '',
            ]);
            $this->notify($Order, 'پیش‌سفارش به‌صورت دستی تحویل شد');
            return true;
        });
    }

    /* A card purchase: the order waits for the bank; the callback calls deliver(). Returns [order, payment]. */
    public function bankOrder($User, array $Number): array
    {
        return DB::transaction(function () use ($User, $Number) {
            $Order = $this->newOrder($User, $Number, 'waiting to pay', 'waiting to pay');
            $Payment = new Payment();
            $Payment->order_id = $Order->id;
            $Payment->user_id = $User->id;
            $Payment->type = 1;
            $Payment->status = 'waiting to pay';
            $Payment->gate = 'Bank';
            $Payment->creator = 'System';
            $Payment->price = $Order->price;
            $Payment->save();
            $Order->pay_id = $Payment->id;
            $Order->save();
            return [$Order, $Payment];
        });
    }

    /*
     * Buys the order's number from Numberland (the order is already paid). Returns 'ok', 'refunded' (Numberland said
     * no: the price went back to the wallet), 'review' (no clear answer: nothing refunded, support checks) or, with
     * pre-orders on, 'preorder' instead of either (the order waits for another number; see the class comment).
     * Runs once per order.
     */
    public function deliver(Order $Order): string
    {
        return Cache::lock('gv-deliver:' . $Order->id, 60)->block(30, function () use ($Order) {
            $Order = $Order->fresh();
            if ($Order->status === 'complete') {
                return 'ok';
            }
            if ($Order->status !== 'pending') {
                return match ($Order->status) { 'failed' => 'refunded', 'preorder' => 'preorder', default => 'review' };
            }
            $Item = OrderItem::where('order_id', $Order->id)->first();
            $Detail = json_decode($Item->detail, true) ?: [];

            try {
                $Body = $this->call(['method' => 'getspnumber', 'id' => $Detail['numberland_id']]);
            } catch (\Throwable $e) {
                $Body = null;
            }
            $Result = is_array($Body) ? (int)($Body['result'] ?? $Body['RESULT'] ?? 0) : 0;
            $Bought = $Result === 1 ? ($Body['numbers'][0] ?? $Body['NUMBERS'][0] ?? null) : null;

            if ($Bought && !empty($Bought['USERNAME'])) {
                $this->saveDelivered($Order, $Item, $Detail, $Bought);
                $this->notify($Order, 'تحویل شد');
                return 'ok';
            }
            if ($this->settings()['preorder']) {
                // Pre-orders on: the buyer keeps a paid order and gets the next number that fits (fillPreorders()).
                // Refused: that number is gone. Unclear: it may be ours, so an admin checks before any refill.
                $Tried = ['id' => (int)$Detail['numberland_id'], 'number' => (string)($Detail['number'] ?? '')];
                $Keep = array_diff_key($Detail, array_flip(['numberland_id', 'number', 'cost']));
                $Item->detail = json_encode(['preorder' => true, 'wanted' => $Keep['wanted'] ?? $Tried, 'tries' => $Keep['tries'] ?? 0]
                    + ($Result < 0 ? [] : ['unclear' => $Tried]) + $Keep);
                $Item->save();
                $Order->status = 'preorder';
                $Order->detail = 'پیش‌سفارش: شماره شما به محض موجود شدن تحویل می‌شود.';
                $Order->save();
                Log::warning('Google Voice: Numberland ' . ($Result < 0 ? 'refused' : 'unclear') . ', now a pre-order', ['order' => $Order->id, 'response' => $Body]);
                $this->notify($Order, $Result < 0 ? 'نامبرلند رد کرد؛ به پیش‌سفارش تبدیل شد' : 'پاسخ نامبرلند نامشخص؛ پیش‌سفارش (قبل از تحویل مجدد، خرید قبلی را در نامبرلند بررسی کنید)');
                return 'preorder';
            }
            if ($Result < 0) {
                // Numberland refused (sold to someone else, no balance, …): give the money back.
                DB::transaction(function () use ($Order) {
                    $User = User::lockForUpdate()->find($Order->user_id);
                    $User->wallet = $User->wallet + $Order->price;
                    $User->save();
                    $Order->status = 'failed';
                    $Order->wallet_status = 1;
                    $Order->detail = 'این شماره دیگر موجود نبود؛ مبلغ به کیف پول شما برگشت.';
                    $Order->save();
                    // The refund as a payment (type 2), like virtual numbers, so the statement and /account show it.
                    $Refund = new \App\Models\Payment();
                    $Refund->user_id = $Order->user_id;
                    $Refund->order_id = $Order->id;
                    $Refund->type = 2;
                    $Refund->status = 'Complete';
                    $Refund->creator = 'system';
                    $Refund->help = "برگشت وجه سفارش شماره $Order->id به کیف پول (شماره گوگل ویس دیگر موجود نبود)";
                    $Refund->price = $Order->price;
                    $Refund->save();
                });
                Log::warning('Google Voice: Numberland refused', ['order' => $Order->id, 'response' => $Body]);
                return 'refunded';
            }
            // No answer, or one we don't understand: the number may be bought. Keep the money; support checks
            // Numberland (method myspnumbers) and delivers or refunds by hand.
            $Order->status = 'review';
            $Order->detail = 'تحویل شماره با تاخیر انجام می‌شود؛ پشتیبانی در حال بررسی است.';
            $Order->save();
            Log::error('Google Voice: unclear Numberland answer', ['order' => $Order->id, 'response' => $Body]);
            $this->notify($Order, 'نیاز به بررسی دستی (پاسخ نامبرلند نامشخص)');
            return 'review';
        });
    }

    /* The login of a delivered order: ['number', 'email', 'password', 'recovery', 'twofa'] or null. */
    public function login(Order $Order): ?array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Id = $Item ? (json_decode($Item->detail, true)['product_id'] ?? null) : null;
        $Voice = $Id ? GoogleVoice::find($Id) : null;
        if (is_null($Voice) || $Voice->order_id != $Order->id) {
            return null;
        }
        $Data = json_decode((new PhpEncrypt())->decrypt($Voice->serial, $Voice->code), true) ?: [];
        return [
            'number' => (string)($Data['number'] ?? $Voice->number),
            'email' => (string)($Data['email'] ?? ''),
            'password' => (string)($Data['password'] ?? ''),
            'recovery' => (string)($Data['backup-email'] ?? ''),
            'twofa' => (string)($Data['2fa'] ?? ''),
        ];
    }

    private function newOrder($User, array $Number, string $Status, string $PayStatus): Order
    {
        $Order = new Order();
        $Order->user_id = $User->id;
        $Order->price = $Number['price'];
        $Order->status = $Status;
        $Order->pay_status = $PayStatus;
        $Order->type = self::ORDER_TYPE;
        $Order->save();

        $Item = new OrderItem();
        $Item->order_id = $Order->id;
        $Item->product_id = $Number['id'];
        $Item->user_id = $User->id;
        $Item->type = self::ORDER_TYPE;
        $Item->price = $Number['price'];
        $Item->detail = json_encode(empty($Number['preorder'])
            ? ['numberland_id' => $Number['id'], 'number' => $Number['number'], 'cost' => $Number['cost']]
            : ['preorder' => true, 'wanted' => $Number['wanted'], 'tries' => 0]);
        $Item->save();
        return $Order;
    }

    private function saveDelivered(Order $Order, OrderItem $Item, array $Detail, array $Bought): void
    {
        DB::transaction(function () use ($Order, $Item, $Detail, $Bought) {
            $Login = [
                'number' => (string)$Bought['NUMBER'],
                'email' => (string)$Bought['USERNAME'],
                'password' => (string)($Bought['PASSWORD'] ?? ''),
                'backup-email' => (string)($Bought['RECOVERY'] ?? ''),
                '2fa' => '',
            ];
            $Serial = (string)random_int(1111111111111111, 9999999999999999);
            $Voice = new GoogleVoice();
            $Voice->number = $Login['number'];
            $Voice->serial = $Serial;
            $Voice->code = (new PhpEncrypt())->encrypt($Serial, $Serial, json_encode($Login));
            $Voice->status = 1;
            $Voice->order_id = $Order->id;
            $Voice->user_id = $Order->user_id;
            $Voice->operator_id = 0; // bought through the API, not added by an admin
            $Voice->save();

            $Item->detail = json_encode($Detail + ['product_id' => $Voice->id]);
            $Item->save();
            $Order->status = 'complete';
            $Order->save();
        });
    }

    private function notify(Order $Order, string $State): void
    {
        $Price = number_format($Order->price) . ' تومان';
        SendTelegramMessage("♻️ سفارش گوگل ویس (نامبرلند)\n🔴 وضعیت: $State\n💵 مبلغ: $Price\n🆔 سفارش: {$Order->id}\n#خرید_گوگل_ویس", -1001509696194);
    }

    private function call(array $Params)
    {
        // Locally only the read-only list may go to the real Numberland (DEV_REAL_NUMBERLAND_LIST); buying stays fake.
        if (DevFakeProviders() && !($Params['method'] === 'spnumberslist' && DevRealNumberLandList())) {
            return DevFakeNumberLandSpecial($Params);
        }
        $Key = config('services.numberland.key');
        if (!$Key) {
            throw new \RuntimeException('NUMBERLAND_API_KEY is not set');
        }
        $Ch = curl_init(self::API . http_build_query(['apikey' => $Key] + $Params));
        curl_setopt_array($Ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 10]);
        $Raw = curl_exec($Ch);
        curl_close($Ch);
        if ($Raw === false) {
            throw new \RuntimeException('Numberland: no response');
        }
        return json_decode($Raw, true);
    }
}
