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
 * review = Numberland gave no clear answer (the number may have been bought: support checks before refunding).
 */
class GoogleVoiceNumbers
{
    public const ORDER_TYPE = 6;
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
        ];
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
    public function buyWithWallet($User, array $Number): Order
    {
        $Order = DB::transaction(function () use ($User, $Number) {
            $Order = $this->newOrder($User, $Number, 'pending', 'Complete');
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
        $this->deliver($Order);
        return $Order->fresh();
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
     * no: the price went back to the wallet) or 'review' (no clear answer: nothing refunded, support checks).
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
                return $Order->status === 'failed' ? 'refunded' : 'review';
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
        $Item->detail = json_encode(['numberland_id' => $Number['id'], 'number' => $Number['number'], 'cost' => $Number['cost']]);
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
        if (DevFakeProviders()) {
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
