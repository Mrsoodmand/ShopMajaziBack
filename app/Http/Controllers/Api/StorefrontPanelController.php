<?php

namespace App\Http\Controllers\Api;

use App\Admin;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\Ticket_Detail;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use App\Services\AccountOrders;
use App\Services\AppleIds;
use App\Services\GoogleVoiceNumbers;
use App\Services\OrderCharges;
use App\Services\VirtualServiceOrders;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/*
 * The customer dashboard of the Next.js storefront (Front/src/app/account, design "Passbook"): the wallet
 * statement, every paid order, support tickets, profile, password and wallet top-ups.
 * Same session and response shape as StorefrontAccountController: {success, message, data}; errors carry `code`.
 *
 * Money rows come from `payments`: type 1/6/8 = an order paid (gate Wallet takes it from the wallet, gate Bank is
 * a card payment that never touches it), 2 or "in" = refund to the wallet, 3 = top-up by card, 5 = referral
 * commission. Order types: 1 number, 2 social service, 3/4 Apple ID, 5 static IP, 6 Google Voice, 7 subscription.
 */
class StorefrontPanelController extends Controller
{
    private const LINES = 150;   // statement lines sent
    private const ORDERS = 100;  // orders sent
    private const TOP_UP_MIN = 10000;
    private const TOP_UP_MAX = 50000000;
    private const PURCHASE = ['1', '6', '8'];
    private const REFUND = ['2', 'in'];
    private const HELD = ['-3', '-2']; // top-up paid with another card / under review: not in the wallet yet
    private const DEPTS = [1 => 'orders', 2 => 'sales', 3 => 'technical', 4 => 'accounting', 5 => 'other'];

    /* Everything the dashboard shows at once: who, wallet, cards, statement, orders and tickets. */
    public function index(): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Wallet = $this->wallet($User);
        $Orders = $this->visible(Order::where('user_id', $User->id))->orderByDesc('id')->take(self::ORDERS)->get();
        $Refunds = Payment::where('user_id', $User->id)->whereIn('type', self::REFUND)->where('status', 'Complete')
            ->whereIn('order_id', $Orders->pluck('id'))->get()->keyBy('order_id');
        $Pays = Payment::whereIn('id', $Orders->pluck('pay_id')->filter())->get()->keyBy('id');
        $Lookup = $this->lookup($Orders);

        return $this->ok([
            'user' => ['name' => (string)$User->name, 'mobile' => (string)$User->mobile, 'email' => $User->email ?: null, 'since' => $this->iso($User->created_at)],
            'wallet' => $Wallet,
            'cards' => $this->cards($User),
            'statement' => $this->statement($User, $Wallet),
            'orders' => $Orders->map(fn($o) => $this->summary($o, $Lookup, $Refunds->get($o->id), $Pays->get($o->pay_id)))->values(),
            'tickets' => $this->tickets($User),
            'top_up' => ['min' => self::TOP_UP_MIN, 'max' => self::TOP_UP_MAX],
        ]);
    }

    /* One order with what it hands over (logins are decrypted here only, never in the list). */
    public function order($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = $this->visible(Order::where('id', intval($id))->where('user_id', $User->id))->first();
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        $Refund = Payment::where('order_id', $Order->id)->whereIn('type', self::REFUND)->where('status', 'Complete')->first();
        $Pay = $Order->pay_id ? Payment::find($Order->pay_id) : null;
        $Data = $this->summary($Order, $this->lookup(collect([$Order])), $Refund, $Pay);
        $Data['fields'] = $this->fields($Order);
        return $this->ok(['order' => $Data, 'wallet' => $this->wallet($User)]);
    }

    /*
     * Pay what the order still asks for: {method: wallet|card, return?}. Card tops the wallet up with what it is short
     * of (route order-charge-callback), then pays from the wallet and returns to `{return}?pay=ok|failed|cancelled|held`.
     */
    public function payOrder(Request $request, $id, OrderCharges $Charges): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = Order::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Order) || $Charges->due($Order) <= 0) {
            return $this->fail('پرداختی برای این سفارش باقی نمانده است.', 'nothing_due', 404);
        }
        if ($request->input('method') !== 'card') {
            [$Ok, $Code, $Message] = $Charges->pay($User, $Order);
            if (!$Ok) {
                return $this->fail($Message, $Code, $Code === 'busy' ? 429 : 400, ['wallet' => $this->wallet($User->fresh()), 'due' => $Charges->due($Order)]);
            }
            Auth::guard('user')->setUser($User->fresh()); // the wallet changed: answer with the new balance
            return $this->ok($this->order($Order->id)->getData(true)['data'], $Message);
        }
        if ($User->bank_blocked != 0 || $User->wallet_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        $Payment = $Charges->startTopUp($User, $Order, self::TOP_UP_MIN);
        $Url = $Payment ? app(\App\Services\Payments\GatewayManager::class)->start(
            $Payment,
            (int)$Payment->price * 10, // Rial
            StorefrontUrl(route('order-charge-callback', [], false)),
            "پرداخت سفارش $Order->id، شماره تراکنش $Payment->id",
            ['mobile' => (string)$User->mobile, 'email' => (string)$User->email],
        ) : null;
        if (!$Url) {
            if ($Payment) {
                $Payment->status = 'failed';
                $Payment->save();
            }
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        $Return = (string)parse_url((string)$request->input('return'), PHP_URL_PATH);
        $Return = preg_match('#^/(en/)?account/orders/\d+$#', $Return) ? $Return : '/account/orders/' . $Order->id;
        $request->session()->put('storefront_charge_return', $Return);
        return $this->ok(['url' => $Url, 'payment_id' => $Payment->id]);
    }

    /* Back from the bank for payOrder(): verify the top-up, credit the wallet once, pay the order's charges. */
    public function payBack(Request $request, OrderCharges $Charges)
    {
        $Gateways = app(\App\Services\Payments\GatewayManager::class);
        [$Payment, $Key] = $Gateways->fromCallback($request) ?? [null, null];
        $Charge = $Payment ? \App\Models\OrderCharge::where('topup_id', $Payment->id)->first() : null;
        if (is_null($Payment) || (int)$Payment->type !== 3 || is_null($Charge)) {
            return $this->chargeBack('failed', null);
        }
        $Label = \App\Services\Payments\GatewayManager::GATEWAYS[$Key]['label'] ?? $Key;
        if (!$Gateways->callbackPaid($request, $Key)) {
            if ($Payment->status !== 'Complete') {
                $Payment->status = 'failed';
                $Payment->error = 'لغو شده توسط کاربر';
                $Payment->save();
            }
            return $this->chargeBack('cancelled', $Charge->order_id);
        }
        try {
            return \Illuminate\Support\Facades\Cache::lock('wallet-topup:' . $Payment->id, 60)->block(20, function () use ($Gateways, $Payment, $Charge, $Label, $Charges) {
                $Payment = Payment::find($Payment->id);
                if ($Payment->status !== 'Complete') {
                    $Verify = $Gateways->verify($Payment, (int)round($Payment->price * 10));
                    if (!$Verify->ok && !$Verify->alreadyVerified) {
                        $Payment->status = '-4';
                        $Payment->help = $Verify->code;
                        $Payment->error = $Verify->message;
                        $Payment->details = $Label;
                        $Payment->save();
                        return $this->chargeBack('failed', $Charge->order_id);
                    }
                    $Payment->refId = $Verify->refId;
                    $Payment->details = $Label;
                    $Payment->fee = $Verify->fee;
                    $Payment->card = $Verify->card;
                    $Payment->status = 'Complete';
                    $Payment->paid_at = $Payment->paid_at ?? Carbon::now();
                    $Payment->save();
                    if ($Verify->ok) { // verified now, not by an earlier callback: credit once
                        $Owner = \App\User::find($Payment->user_id);
                        $Owner->wallet = $Payment->price + $Owner->wallet;
                        $Owner->save();
                    }
                }
                // The money is in the wallet now; pay the order as its owner (the session may have run out at the bank).
                $Order = Order::find($Charge->order_id);
                [$Ok] = $Charges->pay(\App\User::find($Payment->user_id), $Order);
                return $this->chargeBack($Ok ? 'ok' : 'wallet', $Order->id);
            });
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            return $this->chargeBack('held', $Charge->order_id);
        }
    }

    /* The buyer cancels a pending order; what they paid goes back to the wallet. */
    public function cancelOrder($id, OrderCharges $Charges): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = Order::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        [$Ok, $Code, $Message] = $Charges->cancel($User, $Order);
        if (!$Ok) {
            return $this->fail($Message, $Code, $Code === 'busy' ? 429 : 409);
        }
        Auth::guard('user')->setUser($User->fresh()); // the wallet changed: answer with the new balance
        return $this->ok($this->order($Order->id)->getData(true)['data'], $Message);
    }

    private function chargeBack(string $Result, $OrderId)
    {
        $Path = \Illuminate\Support\Facades\Session::pull('storefront_charge_return') ?: ($OrderId ? '/account/orders/' . $OrderId : '/account/orders');
        return redirect()->away(StorefrontUrl($Path) . '?pay=' . $Result);
    }

    // Orders the dashboard lists: paid ones, and ones that wait for the buyer to pay what support asked for.
    private function visible($Query)
    {
        return $Query->where(fn($q) => $q->where('pay_status', 'Complete')
            ->orWhereIn('id', \App\Models\OrderCharge::where('status', 'unpaid')->where('kind', 'order')->select('order_id')));
    }

    /* ---------- Support ---------- */

    public function ticket($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Ticket = Ticket::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Ticket)) {
            return $this->fail('تیکت یافت نشد.', 'not_found', 404);
        }
        if ($Ticket->new_for_user) {
            $Ticket->new_for_user = false;
            $Ticket->save();
        }
        return $this->ok(['ticket' => $this->ticketData($Ticket, true)]);
    }

    /* New ticket: dept (orders|sales|technical|accounting|other), order_id for "orders", subject, text, file? */
    public function newTicket(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Dept = array_search((string)$request->input('dept'), self::DEPTS, true);
        $Subject = trim(strip_tags((string)$request->input('subject')));
        $Text = trim(strip_tags((string)$request->input('text')));
        if ($Dept === false) {
            return $this->fail('دپارتمان را انتخاب کنید.', 'dept_invalid', 422);
        }
        if ($Subject === '' || mb_strlen($Subject) > 150) {
            return $this->fail('عنوان باید بین ۱ تا ۱۵۰ حرف باشد.', 'subject_invalid', 422);
        }
        if ($Text === '' || mb_strlen($Text) > 5000) {
            return $this->fail('متن پیام را بنویسید.', 'text_invalid', 422);
        }
        $OrderId = null;
        if ($Dept === 1) {
            $OrderId = intval($request->input('order_id'));
            if (!Order::where('id', $OrderId)->where('user_id', $User->id)->exists()) {
                return $this->fail('شماره سفارش نامعتبر است.', 'order_invalid', 422);
            }
        }
        if ($Error = $this->badFile($request)) {
            return $Error;
        }
        $Ticket = new Ticket();
        $Ticket->user_id = $User->id;
        $Ticket->subject = $Subject;
        $Ticket->service = $Dept;
        $Ticket->order_id = $OrderId;
        $Ticket->status = 'User-Ticket';
        $Ticket->new_for_admin = true;
        $Ticket->save();
        $this->addMessage($Ticket, $User, $Text, $request);
        $this->notify('💬 تیکت جدیدی ثبت شد!', $Ticket, $User);
        return $this->ok(['ticket' => $this->ticketData($Ticket->fresh(), true)], 'تیکت شما ثبت شد.');
    }

    public function reply(Request $request, $id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Ticket = Ticket::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Ticket)) {
            return $this->fail('تیکت یافت نشد.', 'not_found', 404);
        }
        $Text = trim(strip_tags((string)$request->input('text')));
        if ($Text === '' || mb_strlen($Text) > 5000) {
            return $this->fail('متن پاسخ را بنویسید.', 'text_invalid', 422);
        }
        if ($Error = $this->badFile($request)) {
            return $Error;
        }
        $Ticket->status = 'User-answer'; // a reply re-opens a closed ticket, as in admin › tickets
        $Ticket->new_for_admin = true;
        $Ticket->save();
        $this->addMessage($Ticket, $User, $Text, $request);
        $this->notify('💬 پاسخ جدیدی ثبت شد!', $Ticket, $User);
        return $this->ok(['ticket' => $this->ticketData($Ticket->fresh(), true)], 'پاسخ شما ثبت شد.');
    }

    public function closeTicket($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Ticket = Ticket::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Ticket)) {
            return $this->fail('تیکت یافت نشد.', 'not_found', 404);
        }
        $Ticket->status = 'close';
        $Ticket->save();
        return $this->ok(['ticket' => $this->ticketData($Ticket, true)], 'تیکت بسته شد.');
    }

    /* ---------- Profile, password, top-up ---------- */

    public function profile(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Name = trim(strip_tags((string)$request->input('name')));
        if (mb_strlen($Name) < 3 || mb_strlen($Name) > 40) {
            return $this->fail('نام باید بین ۳ تا ۴۰ حرف باشد.', 'name_invalid', 422);
        }
        $User->name = $Name;
        $User->save();
        return $this->ok(['name' => $User->name], 'ذخیره شد.');
    }

    public function password(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Current = (string)$request->input('current');
        $New = (string)$request->input('password');
        if (!Hash::check($Current, $User->password)) {
            return $this->fail('رمز عبور فعلی اشتباه است.', 'wrong_password', 422);
        }
        if (strlen($New) < 8 || strlen($New) > 30 || $New === $Current || preg_replace('/\D/', '', $New) === $User->mobile) {
            return $this->fail('رمز عبور جدید قابل قبول نیست.', 'password_invalid', 422);
        }
        $User->password = Hash::make($New);
        $User->save();
        // Other devices signed in with the old password stay signed in until their session ends, as on the old site.
        return $this->ok([], 'رمز عبور تغییر کرد.');
    }

    /*
     * Top-up by card: {amount, card_id} or {amount, card_number} (a new card is saved to the account). Returns the
     * bank URL; the old site's callback (v2\ShopController) credits the wallet and sends the buyer back to /account/funds.
     */
    public function topUp(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        $Amount = intval(preg_replace('/\D/', '', PersianNumToEn((string)$request->input('amount'))));
        if ($Amount < self::TOP_UP_MIN || $Amount > self::TOP_UP_MAX) {
            return $this->fail('مبلغ خارج از محدوده مجاز است.', 'amount_invalid', 422);
        }
        if ($request->filled('card_number')) {
            $Number = preg_replace('/\D/', '', PersianNumToEn((string)$request->input('card_number')));
            if (strlen($Number) !== 16) {
                return $this->fail('شماره کارت نامعتبر است.', 'card_invalid', 422);
            }
            $Card = Card::where('user_id', $User->id)->where('card', $Number)->first();
            if (is_null($Card)) {
                $Card = new Card();
                $Card->user_id = $User->id;
                $Card->card = $Number;
                $Card->type = 1;
                $Card->status = 1;
                $Card->save();
            }
        } else {
            $Card = Card::where('user_id', $User->id)->where('id', intval($request->input('card_id')))->first();
            if (is_null($Card)) {
                return $this->fail('کارت را انتخاب کنید.', 'card_invalid', 422);
            }
        }
        $Payment = new Payment();
        $Payment->user_id = $User->id;
        $Payment->price = $Amount;
        $Payment->card_id = $Card->id;
        $Payment->type = '3';
        $Payment->status = 'waiting to pay';
        $Payment->gate = 'Bank';
        $Payment->creator = 'System';
        $Payment->save();

        $Url = app(\App\Services\Payments\GatewayManager::class)->start(
            $Payment,
            $Amount * 10, // Rial
            StorefrontUrl(route('user.ChargeWalletCallBack', [], false)),
            "شارژ کیف پول، شماره تراکنش $Payment->id",
            ['mobile' => (string)$User->mobile, 'email' => (string)$User->email, 'allowedCards' => [$Card->card]],
        );
        if (!$Url) {
            $Payment->status = 'failed';
            $Payment->save();
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        // Back to the page the top-up started on (/account/funds or /en/account/funds), never another site.
        $Return = (string)parse_url((string)$request->input('return'), PHP_URL_PATH);
        $Return = preg_match('#^/(en/)?account/funds$#', $Return) ? $Return : '/account/funds';
        $request->session()->put('storefront_topup_return', $Return);
        return $this->ok(['url' => $Url, 'payment_id' => $Payment->id]);
    }

    /* ---------- Statement ---------- */

    /* Newest first. `balance` is the wallet after the line, counted back from today's balance; null when the line
       didn't move the wallet (card payment, top-up on hold) or when older history no longer adds up (admin edits). */
    private function statement($User, int $Wallet): array
    {
        $Rows = Payment::where('user_id', $User->id)
            ->where(fn($q) => $q
                ->where(fn($w) => $w->whereIn('type', self::PURCHASE)->where('status', 'Complete'))
                ->orWhere(fn($w) => $w->whereIn('type', self::REFUND)->where('status', 'Complete'))
                ->orWhere(fn($w) => $w->where('type', '5')->where('status', 'Complete'))
                ->orWhere(fn($w) => $w->where('type', '3')->whereIn('status', array_merge(['Complete'], self::HELD))))
            ->orderByDesc('id')->take(self::LINES)->get();

        $Balance = $Wallet;
        $Known = true;
        $Lines = [];
        foreach ($Rows as $P) {
            $Price = (int)round((float)$P->price);
            [$Kind, $Amount, $Moves] = match (true) {
                in_array((string)$P->type, self::PURCHASE, true) => $P->gate === 'Wallet' ? ['purchase', -$Price, true] : ['card', -$Price, false],
                in_array((string)$P->type, self::REFUND, true) => ['refund', $Price, true],
                (string)$P->type === '5' => ['commission', $Price, true],
                default => ['top-up', $Price, $P->status === 'Complete'],
            };
            if ($Kind === 'refund' && !$Price && $P->order_id) { // some old refunds were saved without the amount
                $Amount = (int)Order::where('id', $P->order_id)->value('price');
            }
            $Lines[] = [
                'id' => $P->id,
                'kind' => $Kind,
                'amount' => $Amount,
                'balance' => $Moves && $Known ? $Balance : null,
                'held' => $Kind === 'top-up' && $P->status !== 'Complete',
                'order_id' => $P->order_id ? (int)$P->order_id : null,
                'card' => $this->maskedCard($P->card, $P->card_id),
                'at' => $this->iso($P->paid_at ?? $P->created_at),
            ];
            if ($Moves) {
                $Balance -= $Amount;
                $Known = $Known && $Balance >= 0;
            }
        }
        return $Lines;
    }

    /* ---------- Orders ---------- */

    // Names the order list needs, loaded in a few queries instead of one per order.
    private function lookup(Collection $Orders): array
    {
        $Items = OrderItem::whereIn('order_id', $Orders->pluck('id'))->orderBy('id')->get()->groupBy('order_id')->map->first();
        // Orders bought together from the cart (Api\StorefrontCartController): their checkout, and its bank payment.
        $Checkouts = \App\Models\CartCheckout::whereIn('id', $Orders->pluck('checkout_id')->filter()->unique())->get()->keyBy('id');
        $CartPays = Payment::whereIn('id', $Checkouts->pluck('payment_id')->filter())->get()->keyBy('id');
        $NumberIds = $Items->filter(fn($i) => (int)optional($Orders->firstWhere('id', $i->order_id))->type === 1)->pluck('product_id');
        $Numbers = Virtual_Products::whereIn('id', $NumberIds)->get()->keyBy('id');
        $ProductIds = $Items->filter(fn($i) => in_array((int)optional($Orders->firstWhere('id', $i->order_id))->type, [2, 7]))->pluck('product_id');
        $Products = Product::whereIn('id', $ProductIds)->get()->keyBy('id');
        return [
            'charges' => \App\Models\OrderCharge::whereIn('order_id', $Orders->pluck('id'))->where('status', '!=', 'cancelled')->orderBy('id')->get()->groupBy('order_id'),
            'checkouts' => $Checkouts,
            'cart_pays' => $CartPays,
            'items' => $Items,
            'numbers' => $Numbers,
            'services' => Virtual_Service::whereIn('id', $Numbers->pluck('service_id'))->get()->keyBy('id'),
            'countries' => Virtual_Country::whereIn('id', $Numbers->pluck('country_id'))->get()->keyBy('id'),
            'products' => $Products,
            'platforms' => Category::whereIn('id', $Products->pluck('country_id'))->get()->keyBy('id'),
        ];
    }

    private function summary(Order $Order, array $L, ?Payment $Refund, ?Payment $Pay): array
    {
        $Type = (int)$Order->type;
        $Item = $L['items']->get($Order->id);
        $Detail = $Item ? (json_decode((string)$Item->detail, true) ?: []) : [];
        $Refunded = !is_null($Refund) || $Order->status === 'back money to wallet';
        $State = match (true) {
            $Order->status === 'complete' => 'delivered',
            $Refunded => 'refunded',
            $Order->status === 'failed' => 'cancelled',
            $Order->status === 'suspended' => 'needs-you',
            $Order->status === 'waiting to pay' => 'awaiting-payment',
            $Type === 1 && $Order->status === 'pending' => 'waiting',
            default => 'delivering',
        };
        $Data = [
            'id' => $Order->id,
            'type' => $Type,
            'kind' => match ($Type) { 1 => 'number', 2 => 'social', 3, 4 => 'apple-id', 5 => 'static-ip', 6 => 'google-voice', OrderCharges::TYPE => 'custom', default => 'subscription' },
            'state' => $State,
            'price' => (int)$Order->price,
            'paid_with' => $Pay && $Pay->gate === 'Bank' ? 'card' : 'wallet',
            'card' => $Pay && $Pay->gate === 'Bank' ? $this->maskedCard($Pay->card, $Pay->card_id) : null,
            'at' => $this->iso($Order->created_at),
            'updated_at' => $this->iso($Order->updated_at),
            'refund' => $Refunded ? ['amount' => (int)round((float)($Refund->price ?? 0)) ?: (int)$Order->price, 'at' => $this->iso($Refund->created_at ?? $Order->updated_at)] : null,
            'name' => null, 'sub' => null, 'number' => null, 'code' => null, 'seconds_left' => 0, 'count' => null,
        ];
        // What the order still asks for (an order support made, tax, VAT…) and whether the buyer may cancel it.
        $Charges = $L['charges']->get($Order->id) ?? collect();
        $Data['charges'] = $Charges->map(fn($c) => [
            'id' => (int)$c->id, 'kind' => $c->kind, 'title' => (string)$c->title, 'price' => (int)$c->price,
            'paid' => $c->status === 'paid', 'at' => $this->iso($c->paid_at ?? $c->created_at),
        ])->values()->all();
        $Data['due'] = (int)$Charges->where('status', 'unpaid')->sum('price');
        $Data['cancellable'] = app(OrderCharges::class)->cancellable($Order);
        $Data['night_call'] = $Order->night_call === null ? null : (bool)$Order->night_call;
        if ($Type === OrderCharges::TYPE) {
            $Data['name'] = ['fa' => (string)$Order->service, 'en' => (string)$Order->service];
            $Data['paid_with'] = $Pay && $Pay->gate === 'Manual' ? 'outside' : $Data['paid_with'];
        }
        // One cart order (several services paid together): its number (C-…), how it was paid, the card for a bank payment.
        $Checkout = $Order->checkout_id ? $L['checkouts']->get($Order->checkout_id) : null;
        $CartPay = $Checkout && $Checkout->payment_id ? $L['cart_pays']->get($Checkout->payment_id) : null;
        $Data['cart'] = $Checkout ? [
            'id' => (int)$Checkout->id,
            'paid_with' => $Checkout->method === 'card' ? 'card' : 'wallet',
            'card' => $CartPay ? $this->maskedCard($CartPay->card, $CartPay->card_id) : null,
            'total' => (int)$Checkout->total,
            'at' => $this->iso($Checkout->created_at),
        ] : null;

        if ($Type === 1) {
            $Product = $Item ? $L['numbers']->get($Item->product_id) : null;
            $Service = $Product ? $L['services']->get($Product->service_id) : null;
            $Country = $Product ? $L['countries']->get($Product->country_id) : null;
            $Data['name'] = $Service ? ['fa' => $this->bare($Service->faName), 'en' => $Service->enName ?: $this->bare($Service->faName)] : null;
            $Data['sub'] = $Country ? ['fa' => $this->bare($Country->faName), 'en' => $Country->enName ?: $this->bare($Country->faName)] : null;
            $Data['number'] = isset($Detail['number']) ? '+' . ltrim((string)$Detail['number'], '+') : null;
            $Data['code'] = $Detail['sms'] ?? null;
            $Data['seconds_left'] = $State === 'waiting' && $Order->expire_date ? max(0, (int)Carbon::now()->diffInSeconds(Carbon::parse($Order->expire_date), false)) : 0;
        } elseif ($Type === 2 || $Type === 7) {
            $Product = $Item ? $L['products']->get($Item->product_id) : null;
            $Info = $Product ? (json_decode((string)$Product->detail, true) ?: []) : [];
            $Platform = $Product && $Type === 2 ? $L['platforms']->get($Product->country_id) : null;
            $Fa = $Info['name'] ?? $Product?->faName;
            $En = $Info['enName'] ?? $Info['en_name'] ?? null;
            $Data['name'] = $Product ? ['fa' => (string)$Fa, 'en' => (string)($En ?: $Fa)] : null;
            $Data['sub'] = $Platform ? ['fa' => (string)$Platform->name, 'en' => (string)($Platform->enName ?: $Platform->name), 'slug' => $Platform->slug] : null;
            $Data['plan'] = $Detail['plan']['plan_name'] ?? null;
            $Data['count'] = isset($Detail['plan']['count']) ? (int)$Detail['plan']['count'] : null;
        } elseif ($Type === 3 || $Type === 4) {
            $Data['count'] = $Type === AppleIds::READY ? max(1, intval($Item->detail ?? 1)) : 1;
            $Data['custom'] = $Type === AppleIds::CUSTOM;
        } elseif ($Type === 6) {
            $Data['number'] = $Detail['number'] ?? null;
        }
        return $Data;
    }

    // What the order hands over, as label/value rows (secret: hidden until the buyer shows it).
    private function fields(Order $Order): array
    {
        $Type = (int)$Order->type;
        if ($Type === 6 && $Order->status === 'complete') {
            $Login = app(GoogleVoiceNumbers::class)->login($Order);
            return $Login ? array_values(array_filter([
                ['label' => 'Number', 'value' => $Login['number']],
                ['label' => 'Google account', 'value' => $Login['email']],
                ['label' => 'Password', 'value' => $Login['password'], 'secret' => true],
                ['label' => 'Recovery email', 'value' => $Login['recovery']],
                ['label' => '2FA code', 'value' => $Login['twofa'], 'secret' => true],
            ], fn($f) => $f['value'] !== '')) : [];
        }
        if ($Type === 3 || $Type === 4) {
            $Rows = [];
            $Accounts = app(AppleIds::class)->accounts($Order);
            foreach ($Accounts as $i => $A) {
                $N = count($Accounts) > 1 ? ' ' . ($i + 1) : '';
                $Rows[] = ['label' => 'Apple ID' . $N, 'value' => $A['apple_id']];
                $Rows[] = ['label' => 'Password' . $N, 'value' => $A['password'], 'secret' => true];
                if ($A['answers']) {
                    $Rows[] = ['label' => 'Security answers' . $N, 'value' => implode(' · ', $A['answers']), 'secret' => true];
                }
                if ($A['birthday'] !== '') {
                    $Rows[] = ['label' => 'Date of birth' . $N, 'value' => $A['birthday']];
                }
            }
            return $Rows;
        }
        if ($Type === OrderCharges::TYPE) { // made by support: one row per line, with its note
            return OrderItem::where('order_id', $Order->id)->orderBy('id')->get()->map(function ($i) {
                $D = json_decode((string)$i->detail, true) ?: [];
                return ['label' => (string)($D['title'] ?? 'خدمت'), 'value' => trim((string)($D['note'] ?? '')) ?: number_format((int)$i->price) . ' تومان', 'raw' => true];
            })->values()->all();
        }
        if ($Type === 2 || $Type === 7) {
            $Data = $Type === 2 ? app(VirtualServiceOrders::class)->data($Order) : app(AccountOrders::class)->data($Order);
            return collect($Data['fields'] ?? [])->filter(fn($f) => $f['value'] !== null && $f['value'] !== '')
                ->map(fn($f) => ['label' => $f['title'], 'value' => $f['value'], 'raw' => true])->values()->all();
        }
        return [];
    }

    /* ---------- Tickets ---------- */

    private function tickets($User): array
    {
        return Ticket::where('user_id', $User->id)->orderByDesc('updated_at')->orderByDesc('id')->take(50)->get()
            ->map(fn($t) => $this->ticketData($t, false))->values()->all();
    }

    private function ticketData(Ticket $Ticket, bool $Messages): array
    {
        $Rows = Ticket_Detail::where('ticket_id', $Ticket->id)->orderBy('id')->get();
        $Names = Admin::whereIn('id', $Rows->pluck('operator_id')->filter()->unique())->get()
            ->mapWithKeys(fn($a) => [$a->id => $a->full_name ?: $a->name ?: null]);
        $Map = fn($m) => [
            'from' => $m->type === 'admin' ? 'support' : 'you',
            'name' => $m->type === 'admin' ? ($Names[$m->operator_id] ?? null) : null,
            'at' => $this->iso($m->created_at),
            'text' => strip_tags((string)$m->detail),
            'file' => $m->file ? '/' . ltrim($m->file, '/') : null, // stored as "tickets/x.png" (public/tickets)
        ];
        return [
            'id' => $Ticket->id,
            'subject' => strip_tags((string)$Ticket->subject),
            'dept' => self::DEPTS[(int)$Ticket->service] ?? 'other',
            'order_id' => $Ticket->order_id ? (int)$Ticket->order_id : null,
            'state' => match ($Ticket->status) { 'Admin-answer' => 'replied', 'close' => 'closed', default => 'waiting' },
            'unread' => (bool)$Ticket->new_for_user,
            'updated_at' => $this->iso($Ticket->updated_at),
            'last' => ($Last = $Rows->last()) ? $Map($Last) : null,
            'messages' => $Messages ? $Rows->map($Map)->values()->all() : null,
        ];
    }

    private function addMessage(Ticket $Ticket, $User, string $Text, Request $request): void
    {
        $Row = new Ticket_Detail();
        $Row->user_id = $User->id;
        $Row->ticket_id = $Ticket->id;
        $Row->type = 'user';
        $Row->detail = $Text;
        if ($request->hasFile('file')) {
            $Row->file = $this->UploadUserTicketImage($request->file('file'));
        }
        $Row->save();
    }

    // PNG, JPG or BMP up to 2 MB, as on the old ticket form (UploadUserTicketImage checks the content too).
    private function badFile(Request $request): ?JsonResponse
    {
        if (!$request->hasFile('file')) {
            return null;
        }
        $File = $request->file('file');
        if (!$File->isValid() || $File->getSize() > 2 * 1024 * 1024 || !in_array(strtolower((string)$File->guessExtension()), ['png', 'jpg', 'jpeg', 'bmp'], true)) {
            return $this->fail('فقط تصویر PNG، JPG یا BMP تا ۲ مگابایت.', 'file_invalid', 422);
        }
        return null;
    }

    private function notify(string $Head, Ticket $Ticket, $User): void
    {
        try {
            $Dept = __('all.Ticket-Type.' . $Ticket->service);
            $TicketUrl = "<a href='" . route('admin.ticket single', $Ticket->id) . "'>مشاهده</a>";
            $UserUrl = "<a href='" . route('admin.user single', $User->id) . "'>مشاهده</a>";
            SendTelegramMessage("\n$Head\n🔴 عنوان تیکت: {$Ticket->subject}\n⚙ دپارتمان: $Dept\n👤 مشاهده کاربر: $UserUrl\n👀 مشاهده تیکت: $TicketUrl\n", -1001509696194);
        } catch (\Throwable $e) {
            report($e); // the ticket is saved either way
        }
    }

    /* ---------- Helpers ---------- */

    private function cards($User): array
    {
        return Card::where('user_id', $User->id)->orderBy('id')->get()
            ->filter(fn($c) => strlen(preg_replace('/\D/', '', (string)$c->card)) === 16)
            ->map(fn($c) => ['id' => (string)$c->id, 'first6' => substr($c->card, 0, 6), 'last4' => substr($c->card, -4)])->values()->all();
    }

    // "603799******1234" (from the bank) or a saved card → {first6, last4}.
    private function maskedCard($Masked, $CardId): ?array
    {
        $Digits = preg_replace('/[^\d*]/', '', (string)$Masked);
        if (strlen($Digits) >= 10) {
            return ['first6' => substr($Digits, 0, 6), 'last4' => substr($Digits, -4)];
        }
        $Card = $CardId ? Card::find($CardId) : null;
        return $Card && strlen((string)$Card->card) === 16 ? ['first6' => substr($Card->card, 0, 6), 'last4' => substr($Card->card, -4)] : null;
    }

    // "شماره مجازی تلگرام - Telegram" → "تلگرام", "شماره مجازی کشور اکراین" → "اکراین".
    private function bare($Name): string
    {
        $Name = preg_replace('/^\s*شماره\s*(ی\s*)?مجازی\s*(کشور\s*)?/u', '', (string)$Name);
        return trim(preg_replace('/\s+-\s+[A-Za-z].*$/u', '', $Name));
    }

    private function user()
    {
        return Auth::guard('user')->user();
    }

    private function wallet($User): int
    {
        return (int)($User->wallet + $User->blocked_wallet);
    }

    private function iso($Date): ?string
    {
        return $Date ? Carbon::parse($Date)->toIso8601String() : null;
    }

    private function signInFirst(): JsonResponse
    {
        return $this->fail('برای ادامه، وارد حساب کاربری خود شوید.', 'auth', 401);
    }

    private function ok(array $Data, ?string $Message = null): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $Message, 'data' => $Data])->header('Cache-Control', 'no-store');
    }

    private function fail(string $Message, string $Code, int $Status, array $Data = []): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $Message, 'code' => $Code, 'data' => $Data ?: null], $Status)->header('Cache-Control', 'no-store');
    }
}
