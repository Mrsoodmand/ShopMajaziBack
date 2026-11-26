<?php

namespace App\Services;

use App\Models\Commission;
use App\Models\History;
use App\Models\Order;
use App\Models\OrderCharge;
use App\Models\OrderItem;
use App\Models\Payment;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/*
 * Orders support makes for a customer, extra amounts added to an order, paying them, and the customer's own cancel.
 *
 * - make():   an order of type 9 (سفارش اختصاصی) with one item per line — a catalog plan or a free-text service priced
 *             in Toman or dollars (dollars become Toman at today's selling rate and stay fixed). "unpaid" leaves it
 *             «در انتظار پرداخت» with a kind-"order" charge the customer pays; "paid" records an outside payment
 *             (gate Manual) and starts it at «درحال بررسی».
 * - charge(): an extra amount on any order (kind "extra"); the order keeps its status, the customer sees it as due.
 * - pay():    pays every unpaid charge of the order from the wallet, as type-1 payments on the order.
 * - startCard() / paidByCard(): the same by card, as one type-1 bank payment on the order (no wallet top-up).
 * - cancel(): the customer cancels a pending order; every payment on it goes back to the wallet (type-2 payments
 *             whose `error` is the refunded payment's id, the same shape as admin › refund).
 */
class OrderCharges
{
    // Customer methods take either user class: the storefront's signed-in user is App\Models\User, admin and
    // callbacks load App\User (same table).
    public const TYPE = 9;
    public const MAX_LINES = 20;
    public const MAX_PRICE = 500000000; // Toman, per line or charge
    // Orders that have their own cancel (numbers: provider refund; Google Voice: pre-order) or none.
    private const NO_CANCEL = [1, 6];

    /* ---------- Admin ---------- */

    /**
     * An amount support typed, in Toman or dollars, as [toman, currency, amount as typed, error]. Dollars use today's
     * services rate (market dollar + its profit), rounded up to a thousand Toman. Digits must already be western.
     */
    public static function price(string $Currency, $Amount): array
    {
        $Currency = $Currency === 'usd' ? 'usd' : 'toman';
        $Value = (float)str_replace([',', '٬', ' '], '', (string)$Amount);
        if ($Value <= 0) {
            return [0, $Currency, 0, 'مبلغ را وارد کنید.'];
        }
        if ($Currency === 'usd') {
            $Rate = (int)round(Pricing::servicesRate());
            if ($Rate <= 0) {
                return [0, $Currency, $Value, 'نرخ دلار در دسترس نیست.'];
            }
            $Price = (int)(ceil($Value * $Rate / 1000) * 1000);
        } else {
            $Price = (int)round($Value);
        }
        if ($Price < 1000 || $Price > self::MAX_PRICE) {
            return [0, $Currency, $Value, 'مبلغ باید بین ۱٬۰۰۰ تا ۵۰۰٬۰۰۰٬۰۰۰ تومان باشد.'];
        }
        return [$Price, $Currency, round($Value, 2), null];
    }

    /**
     * @param array $Lines each: {product_id?, plan_id?, title, note?, currency: toman|usd, amount, count?}
     * @return Order
     */
    public function make(User $User, array $Lines, bool $Paid, int $AdminId, string $AdminName, string $Note = ''): Order
    {
        return DB::transaction(function () use ($User, $Lines, $Paid, $AdminId, $AdminName, $Note) {
            $Total = array_sum(array_column($Lines, 'price'));
            $Order = new Order();
            $Order->type = self::TYPE;
            $Order->user_id = $User->id;
            $Order->price = $Total;
            $Order->admin_id = $AdminId;
            $Order->service = mb_substr(implode('، ', array_column($Lines, 'title')), 0, 180);
            $Order->status = $Paid ? 'pending' : 'waiting to pay';
            $Order->pay_status = $Paid ? 'Complete' : 'waiting to pay';
            $Order->save();

            foreach ($Lines as $Line) {
                $Item = new OrderItem();
                $Item->order_id = $Order->id;
                $Item->product_id = $Line['product_id'] ?? null;
                $Item->type = self::TYPE;
                $Item->price = $Line['price'];
                $Item->detail = json_encode(array_filter([
                    'title' => $Line['title'],
                    'note' => $Line['note'] ?? null,
                    'plan_id' => $Line['plan_id'] ?? null,
                    'count' => $Line['count'] ?? null,
                    'currency' => $Line['currency'],
                    'amount' => $Line['amount'], // as typed: Toman, or dollars
                    'rate' => $Line['rate'] ?? null, // Toman per dollar used
                ], fn ($v) => $v !== null && $v !== ''), JSON_UNESCAPED_UNICODE);
                $Item->save();
            }

            if ($Paid) {
                $Payment = new Payment();
                $Payment->order_id = $Order->id;
                $Payment->user_id = $User->id;
                $Payment->type = 1;
                $Payment->status = 'Complete';
                $Payment->gate = 'Manual';
                $Payment->creator = 'Admin';
                $Payment->price = $Total;
                $Payment->help = "پرداخت خارج از سایت، ثبت توسط $AdminName";
                $Payment->paid_at = Carbon::now();
                $Payment->save();
                $Order->pay_id = $Payment->id;
                $Order->save();
            } else {
                OrderCharge::create([
                    'order_id' => $Order->id, 'user_id' => $User->id, 'kind' => 'order',
                    'title' => 'مبلغ سفارش', 'price' => $Total, 'admin_id' => $AdminId,
                ]);
                // Official-invoice companies (Organizations::vatOf): their VAT is due with the order. An order already
                // paid outside the site gets none here; support adds it as an extra charge if it is owed.
                $Percent = Organizations::vatOf($User);
                $Vat = Organizations::vat($Total, $Percent);
                if ($Vat > 0) {
                    OrderCharge::create([
                        'order_id' => $Order->id, 'user_id' => $User->id, 'kind' => 'extra',
                        'title' => 'ارزش افزوده (' . Organizations::percentText($Percent) . '٪)', 'price' => $Vat, 'admin_id' => $AdminId,
                    ]);
                }
            }
            $this->history($Order, null, $Order->status, trim('ثبت سفارش توسط پشتیبانی' . ($Note !== '' ? " — $Note" : '')), $AdminId);
            return $Order;
        });
    }

    public function charge(Order $Order, string $Title, int $Price, int $AdminId): OrderCharge
    {
        $Charge = OrderCharge::create([
            'order_id' => $Order->id, 'user_id' => $Order->user_id, 'kind' => 'extra',
            'title' => $Title, 'price' => $Price, 'admin_id' => $AdminId,
        ]);
        $this->history($Order, $Order->status, $Order->status, 'پرداخت اضافه: ' . $Title . ' — ' . number_format($Price) . ' تومان', $AdminId);
        return $Charge;
    }

    /* Drops a charge nobody paid yet. Dropping an admin order's own price cancels the order. */
    public function drop(OrderCharge $Charge, int $AdminId): void
    {
        DB::transaction(function () use ($Charge, $AdminId) {
            $Charge->update(['status' => 'cancelled']);
            $Order = Order::find($Charge->order_id);
            if ($Charge->kind === 'order' && $Order && $Order->pay_status !== 'Complete') {
                $Old = $Order->status;
                $Order->status = $Order->pay_status = 'failed';
                $Order->save();
                OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->update(['status' => 'cancelled']);
                $this->history($Order, $Old, 'failed', 'لغو سفارش پرداخت‌نشده', $AdminId);
            } elseif ($Order) {
                $this->history($Order, $Order->status, $Order->status, 'حذف پرداخت اضافه: ' . $Charge->title, $AdminId);
            }
        });
    }

    /* ---------- Customer ---------- */

    public function due(Order $Order): int
    {
        return (int) OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->sum('price');
    }

    /**
     * Pay every unpaid charge from the wallet. [ok, code, message]; codes: paid, nothing, balance, wallet_blocked, busy.
     */
    public function pay(User|\App\Models\User $User, Order $Order): array
    {
        if ($User->wallet_blocked != 0) {
            return [false, 'wallet_blocked', 'شما مجاز به پرداخت با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید'];
        }
        try {
            return WalletPurchaseLock($User->id)->block(10, fn () => $this->locked($Order, fn () => DB::transaction(function () use ($User, $Order) {
                $Order = Order::lockForUpdate()->find($Order->id);
                if (in_array($Order->status, ['failed', 'back money to wallet'], true)) {
                    return [false, 'cancelled', 'این سفارش لغو شده است.'];
                }
                $Charges = OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->lockForUpdate()->get();
                if ($Charges->isEmpty()) {
                    return [true, 'nothing', 'پرداختی برای این سفارش باقی نمانده است.'];
                }
                $Fresh = User::lockForUpdate()->find($User->id);
                $Sum = (int) $Charges->sum('price');
                if ($Fresh->wallet + $Fresh->blocked_wallet < $Sum) {
                    return [false, 'balance', 'موجودی کیف پول شما کافی نیست.'];
                }
                foreach ($Charges as $Charge) {
                    $Payment = new Payment();
                    $Payment->order_id = $Order->id;
                    $Payment->user_id = $User->id;
                    $Payment->type = 1;
                    $Payment->status = 'Complete';
                    $Payment->gate = 'Wallet';
                    $Payment->creator = 'OrderCharge';
                    $Payment->price = $Charge->price;
                    $Payment->help = $Charge->title;
                    $Payment->paid_at = Carbon::now();
                    $Payment->save();
                    $Charge->update(['status' => 'paid', 'payment_id' => $Payment->id, 'paid_at' => Carbon::now()]);
                    if ($Charge->kind === 'order') {
                        $Order->pay_id = $Payment->id;
                        $Order->pay_status = 'Complete';
                        if ($Order->status === 'waiting to pay') {
                            $Order->status = 'pending';
                        }
                        $Order->save();
                    }
                }
                DecrementWallet($Sum, $Fresh);
                $this->notifyPaid($Order, $Sum);
                return [true, 'paid', 'پرداخت با موفقیت انجام شد.'];
            })));
        } catch (LockTimeoutException $e) {
            return [false, 'busy', 'پرداخت قبلی شما در حال انجام است، لطفا چند لحظه صبر کنید.'];
        }
    }

    public function cancellable(Order $Order): bool
    {
        return $Order->status === 'pending' && $Order->pay_status === 'Complete'
            && !in_array((int) $Order->type, self::NO_CANCEL, true);
    }

    /**
     * The customer cancels a pending order: everything paid on it goes back to the wallet. [ok, code, message, refunded].
     */
    public function cancel(User|\App\Models\User $User, Order $Order): array
    {
        try {
            return $this->locked($Order, fn () => DB::transaction(function () use ($User, $Order) {
                $Order = Order::lockForUpdate()->find($Order->id);
                if ((int) $Order->user_id !== (int) $User->id || !$this->cancellable($Order)) {
                    return [false, 'not_cancellable', 'این سفارش دیگر قابل لغو نیست. اگر کمک لازم دارید، تیکت بزنید.', 0];
                }
                $Payments = Payment::where('order_id', $Order->id)->whereIn('type', ['1', '6', '8'])->where('status', 'Complete')->get();
                $Done = Payment::where('order_id', $Order->id)->where('type', 2)->pluck('error')->map(fn ($v) => (string) $v)->all();
                $Refunded = 0;
                foreach ($Payments as $P) {
                    if (in_array((string) $P->id, $Done, true) || (float) $P->price <= 0) {
                        continue;
                    }
                    $Refund = new Payment();
                    $Refund->user_id = $Order->user_id;
                    $Refund->order_id = $Order->id;
                    $Refund->type = 2;
                    $Refund->status = 'Complete';
                    $Refund->creator = 'system';
                    $Refund->price = $P->price;
                    $Refund->error = $P->id;
                    $Refund->help = "لغو سفارش توسط کاربر، عودت وجه تراکنش $P->id به کیف پول";
                    $Refund->save();
                    $Refunded += (int) round((float) $P->price);
                }
                if ($Refunded > 0) {
                    $Owner = User::lockForUpdate()->find($Order->user_id);
                    $Owner->wallet = $Owner->wallet + $Refunded;
                    $Owner->save();
                }
                $Order->status = 'back money to wallet';
                $Order->wallet_status = 1;
                $Order->save();
                OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->update(['status' => 'cancelled']);
                Commission::where('order_id', $Order->id)->update(['status' => 'failed']);
                $this->history($Order, 'pending', 'back money to wallet', 'لغو توسط مشتری — ' . number_format($Refunded) . ' تومان به کیف پول برگشت', null);
                $this->notifyCancelled($Order, $Refunded);
                return [true, 'cancelled', 'سفارش لغو شد و مبلغ آن به کیف پول شما برگشت.', $Refunded];
            }));
        } catch (LockTimeoutException $e) {
            return [false, 'busy', 'لطفا چند لحظه بعد دوباره تلاش کنید.', 0];
        }
    }

    /* ---------- Card payments (a bank payment on the order, like any order paid by card) ---------- */

    /*
     * One type-1 bank payment on the order for everything still due, linked to those charges (order_charges.topup_id
     * holds the bank payment in progress). The wallet is not touched: the order shows «درگاه بانک», not «کیف پول».
     */
    public function startCard(User|\App\Models\User $User, Order $Order): ?Payment
    {
        $Due = $this->due($Order);
        if ($Due <= 0) {
            return null;
        }
        $Payment = new Payment();
        $Payment->order_id = $Order->id;
        $Payment->user_id = $User->id;
        $Payment->price = $Due;
        $Payment->type = 1;
        $Payment->status = 'waiting to pay';
        $Payment->gate = 'Bank';
        $Payment->creator = 'OrderCharge';
        $Payment->help = "پرداخت سفارش $Order->id";
        $Payment->save();
        OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->update(['topup_id' => $Payment->id]);
        return $Payment;
    }

    /*
     * The bank confirmed $Payment (startCard): its charges are paid by it. A charge that was paid from the wallet or
     * dropped meanwhile (or a cancelled order) leaves money over; that part goes to the wallet as a refund of the
     * payment (type 2, `error` = the payment's id). Safe to call again: nothing is paid or credited twice.
     */
    public function paidByCard(Payment $Payment, Order $Order): void
    {
        $this->locked($Order, fn () => DB::transaction(function () use ($Payment, $Order) {
            $Order = Order::lockForUpdate()->find($Order->id);
            if (Payment::where('type', 2)->where('error', (string) $Payment->id)->exists()) {
                return; // settled before, leftover included
            }
            $Cancelled = in_array($Order->status, ['failed', 'back money to wallet'], true);
            // The order's unpaid charges, as far as this payment reaches (its own first: a later card attempt may
            // have taken over topup_id).
            $Room = (int) round((float) $Payment->price) - (int) OrderCharge::where('payment_id', $Payment->id)->sum('price');
            $Charges = collect();
            if (!$Cancelled) {
                foreach (OrderCharge::where('order_id', $Order->id)->where('status', 'unpaid')->lockForUpdate()->get()
                             ->sortBy(fn ($c) => [(int) $c->topup_id !== (int) $Payment->id, $c->id]) as $C) {
                    if ($C->price <= $Room) {
                        $Charges->push($C);
                        $Room -= $C->price;
                    }
                }
            }
            $Now = Carbon::now();
            foreach ($Charges as $Charge) {
                $Charge->update(['status' => 'paid', 'payment_id' => $Payment->id, 'paid_at' => $Now]);
                if ($Charge->kind === 'order') {
                    $Order->pay_id = $Payment->id;
                    $Order->pay_status = 'Complete';
                    if ($Order->status === 'waiting to pay') {
                        $Order->status = 'pending';
                    }
                    $Order->save();
                }
            }
            $Sum = (int) $Charges->sum('price');
            $Left = $Room; // what this payment didn't cover any charge with
            if ($Left > 0) {
                $Refund = new Payment();
                $Refund->user_id = $Payment->user_id;
                $Refund->order_id = $Order->id;
                $Refund->type = 2;
                $Refund->status = 'Complete';
                $Refund->creator = 'system';
                $Refund->price = $Left;
                $Refund->error = $Payment->id;
                $Refund->help = "مانده پرداخت بانکی تراکنش $Payment->id به کیف پول";
                $Refund->save();
                $Owner = User::lockForUpdate()->find($Payment->user_id);
                $Owner->wallet = $Owner->wallet + $Left;
                $Owner->save();
            }
            if ($Sum > 0) {
                $this->notifyPaid($Order, $Sum, 'درگاه بانک');
            }
        }));
    }

    /* ---------- Helpers ---------- */

    private function locked(Order $Order, callable $Run)
    {
        return Cache::lock('order-money:' . $Order->id, 30)->block(10, $Run);
    }

    private function history(Order $Order, ?string $From, ?string $To, string $Reason, ?int $AdminId): void
    {
        $H = new History();
        $H->type = 'order';
        $H->post_id = $Order->id;
        $H->detail = json_encode(['old_status' => $From, 'new_status' => $To, 'detail' => $Reason, 'changed_by' => $AdminId, 'changed_date' => Carbon::now()], JSON_UNESCAPED_UNICODE);
        $H->admin_id = $AdminId;
        $H->save();
    }

    private function notifyPaid(Order $Order, int $Sum, string $Via = 'کیف پول'): void
    {
        $this->telegram("💳 پرداخت سفارش #$Order->id\n💵 مبلغ: " . number_format($Sum) . " تومان ($Via)\n👀 " . route('admin.order single', $Order->id));
    }

    private function notifyCancelled(Order $Order, int $Refunded): void
    {
        $this->telegram("❌ لغو سفارش #$Order->id توسط مشتری\n💵 عودت به کیف پول: " . number_format($Refunded) . " تومان\n👀 " . route('admin.order single', $Order->id));
    }

    private function telegram(string $Text): void
    {
        try {
            SendTelegramMessage($Text, -1001509696194);
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /* SMS «سفارش {NUM}: {STATUS}» (the order-status template admin › change status uses). */
    public static function sms(Order $Order, string $Status): void
    {
        try {
            $User = User::find($Order->user_id);
            if ($User && $User->mobile) {
                sendSmsWithSmsir($User->mobile, ['NUM' => $Order->id, 'STATUS' => $Status], 138554);
            }
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
