<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartCheckout;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AccountOrders;
use App\Services\AppleIds;
use App\Services\GoogleVoiceNumbers;
use App\Services\TelegramStars;
use App\Services\VirtualServiceOrders;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/*
 * The storefront's shopping cart (Next.js: Front/src/lib/cart.tsx, the side drawer). Routes:
 * api/v1/storefront/account/cart* (web group: session + XSRF, like StorefrontAccountController).
 *
 * Each line is one ordinary purchase: `payload` is the very body the product's own buy endpoint takes
 * (services/buy, subscriptions/buy, apple-id/buy, google-voice/buy) and prices are always quoted again here with
 * the same code. Checking out pays each line from the wallet through those endpoints, one after another, so every
 * line becomes an ordinary order (admin, /account, invoices and notices unchanged), tagged with orders.checkout_id.
 * Paying by card first tops the wallet up with the total (a type-3 payment); whatever can't be delivered stays in
 * the wallet. Virtual numbers are not sold through the cart: their SMS window starts when they're bought.
 */
class StorefrontCartController extends Controller
{
    private const KINDS = ['service', 'subscription', 'apple_id', 'google_voice'];
    private const MAX_LINES = 20;

    /* The cart, priced now. */
    public function show(): JsonResponse
    {
        return $this->ok($this->cartData($this->cart(false)));
    }

    /* Add a line: {kind, payload}. The payload is checked and priced as the buy endpoint would. */
    public function add(Request $request): JsonResponse
    {
        $Kind = (string)$request->input('kind');
        $Payload = $request->input('payload');
        if (!in_array($Kind, self::KINDS, true) || !is_array($Payload)) {
            return $this->fail('این مورد را نمی توان به سبد خرید اضافه کرد.', 'kind_invalid', 422);
        }
        $Payload = $this->clean($Payload);
        [$Quote, $Error] = $this->quote($Kind, $Payload, true);
        if ($Error) {
            return $this->fail($Error['message'], $Error['code'], $Error['status'], $Error['data'] ?? []);
        }
        $Cart = $this->cart(true);
        if ($Cart->items()->count() >= self::MAX_LINES) {
            return $this->fail('سبد خرید پر است. لطفا ابتدا موارد فعلی را پرداخت کنید.', 'cart_full', 422);
        }
        // The same Google Voice number twice is one number.
        if ($Kind === 'google_voice' && $Cart->items()->where('kind', $Kind)->get()->contains(fn($i) => (int)($i->payload['id'] ?? 0) === (int)($Payload['id'] ?? -1))) {
            return $this->ok($this->cartData($Cart), 'این شماره در سبد خرید شما هست.');
        }
        $Cart->items()->create(['kind' => $Kind, 'payload' => $Payload, 'label' => $Quote['label'], 'price' => $Quote['price']]);
        $Cart->touch();
        return $this->ok($this->cartData($Cart->fresh()), 'به سبد خرید اضافه شد.');
    }

    /* Change a line's quantity (Apple ID ready: qty) or count (services sold by count): {qty} | {count}. */
    public function update(Request $request, $id): JsonResponse
    {
        $Item = $this->ownItem($id);
        if (is_null($Item)) {
            return $this->fail('این مورد در سبد خرید نیست.', 'not_found', 404);
        }
        $Payload = $Item->payload;
        if ($request->has('qty') && $Item->kind === 'apple_id' && ($Payload['kind'] ?? '') !== 'custom') {
            $Payload['qty'] = intval($request->input('qty'));
        } elseif ($request->has('count') && $Item->kind === 'service' && isset($Payload['count'])) {
            $Payload['count'] = intval($request->input('count'));
        } else {
            return $this->fail('تعداد این مورد را نمی توان تغییر داد.', 'qty_fixed', 422);
        }
        [$Quote, $Error] = $this->quote($Item->kind, $Payload, false);
        if ($Error) {
            return $this->fail($Error['message'], $Error['code'], $Error['status'], $Error['data'] ?? []);
        }
        $Item->update(['payload' => $Payload, 'label' => $Quote['label'], 'price' => $Quote['price']]);
        return $this->ok($this->cartData($this->cart(false)));
    }

    public function remove($id): JsonResponse
    {
        optional($this->ownItem($id))->delete();
        return $this->ok($this->cartData($this->cart(false)));
    }

    public function clear(): JsonResponse
    {
        if ($Cart = $this->cart(false)) {
            $Cart->items()->delete();
        }
        return $this->ok($this->cartData($this->cart(false)));
    }

    /*
     * Pay for the whole cart: {method: wallet|card, total, return}. `total` is what the buyer saw; if the cart now
     * costs something else (or a line can't be bought any more) nothing is charged and the fresh cart comes back.
     * Wallet → {checkout}. Card → {url} of the bank; the bank comes back to bankBack().
     */
    public function checkout(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->fail('برای ادامه، وارد حساب کاربری خود شوید.', 'auth', 401);
        }
        $Method = $request->input('method') === 'card' ? 'card' : 'wallet';
        // The cart is always paid from the wallet in the end (card = top-up first), so a blocked wallet can't use it.
        if ($User->wallet_blocked != 0) {
            return $this->fail('شما مجاز به پرداخت با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید', 'wallet_blocked', 403);
        }
        if ($Method === 'card' && $User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }

        try {
            return Cache::lock('cart-checkout-user:' . $User->id, 60)->block(5, function () use ($request, $User, $Method) {
                $Cart = $this->cart(false);
                $Items = $Cart ? $Cart->items()->get() : collect();
                if ($Items->isEmpty()) {
                    return $this->fail('سبد خرید شما خالی است.', 'cart_empty', 422);
                }
                // Price every line again, with the provider where that matters (Google Voice).
                foreach ($Items as $Item) {
                    [$Quote, $Error] = $this->quote($Item->kind, $Item->payload, true);
                    if ($Error) {
                        return $this->fail($Error['message'], 'cart_changed', 409, ['cart' => $this->cartData($Cart), 'line' => $Item->id]);
                    }
                    if ($Quote['price'] !== $Item->price) {
                        $Item->update(['price' => $Quote['price'], 'label' => $Quote['label']]);
                    }
                }
                $Total = (int)$Items->sum('price');
                if ($Total !== intval($request->input('total'))) {
                    return $this->fail('قیمت سبد خرید تغییر کرده است. لطفا مبلغ جدید را بررسی کنید.', 'price_changed', 409, ['cart' => $this->cartData($Cart->fresh())]);
                }
                $Lines = $Items->map(fn($i) => ['line' => $i->id, 'kind' => $i->kind, 'payload' => $i->payload, 'label' => $i->label, 'price' => $i->price, 'order_id' => null, 'result' => null, 'message' => null])->values()->all();

                if ($Method === 'wallet') {
                    if ($this->wallet($User->fresh()) < $Total) {
                        return $this->fail('موجودی کیف پول شما کافی نیست.', 'balance', 400, ['wallet' => $this->wallet($User->fresh()), 'price' => $Total]);
                    }
                    $Checkout = CartCheckout::create(['user_id' => $User->id, 'method' => 'wallet', 'total' => $Total, 'status' => 'paying', 'items' => $Lines]);
                    $this->run($Checkout);
                    return $this->ok(['checkout' => $this->checkoutData($Checkout->fresh()), 'wallet' => $this->wallet($User->fresh())]);
                }

                // Card: a wallet top-up for the total, then the same checkout once the bank confirms.
                $Payment = new Payment();
                $Payment->user_id = $User->id;
                $Payment->price = $Total;
                $Payment->type = '3';
                $Payment->status = 'waiting to pay';
                $Payment->gate = 'Bank';
                $Payment->creator = 'Cart';
                $Payment->save();
                $Checkout = CartCheckout::create(['user_id' => $User->id, 'method' => 'card', 'total' => $Total, 'payment_id' => $Payment->id, 'status' => 'waiting_to_pay', 'items' => $Lines]);
                $Url = app(\App\Services\Payments\GatewayManager::class)->start(
                    $Payment,
                    $Total * 10, // Rial
                    StorefrontUrl(route('cart-bank-callback', [], false)),
                    "سبد خرید، شماره تراکنش $Payment->id",
                    ['mobile' => (string)$User->mobile, 'email' => (string)$User->email],
                );
                if (!$Url) {
                    $Payment->status = 'failed';
                    $Payment->save();
                    $Checkout->update(['status' => 'failed']);
                    return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
                }
                $Return = (string)parse_url((string)$request->input('return'), PHP_URL_PATH);
                $Return = preg_match('#^/(en/)?[a-z0-9\-/]*$#', $Return) && !str_starts_with($Return, '//') ? $Return : '/';
                $request->session()->put('storefront_cart_return', $Return);
                return $this->ok(['url' => $Url, 'checkout_id' => $Checkout->id]);
            });
        } catch (LockTimeoutException $e) {
            return $this->fail('پرداخت قبلی شما در حال انجام است، لطفا چند لحظه صبر کنید.', 'busy', 429);
        }
    }

    /* One of my checkouts, with where each line stands now. */
    public function showCheckout($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->fail('برای ادامه، وارد حساب کاربری خود شوید.', 'auth', 401);
        }
        $Checkout = CartCheckout::where('id', intval($id))->where('user_id', $User->id)->first();
        if (is_null($Checkout)) {
            return $this->fail('پرداخت یافت نشد.', 'not_found', 404);
        }
        return $this->ok(['checkout' => $this->checkoutData($Checkout), 'wallet' => $this->wallet($User->fresh())]);
    }

    /*
     * Back from the bank (route cart-bank-callback, reached through the Next.js fallback). Verifies the top-up,
     * credits the wallet once, pays the lines and sends the buyer to `{return}?cart=ok|partial|cancelled|failed|held&checkout=`.
     */
    public function bankBack(Request $request)
    {
        $Gateways = app(\App\Services\Payments\GatewayManager::class);
        [$Payment, $Key] = $Gateways->fromCallback($request) ?? [null, null];
        $Checkout = $Payment ? CartCheckout::where('payment_id', $Payment->id)->first() : null;
        if (is_null($Payment) || (int)$Payment->type !== 3 || is_null($Checkout)) {
            return $this->back('failed', null);
        }
        $Label = \App\Services\Payments\GatewayManager::GATEWAYS[$Key]['label'] ?? $Key;
        if (!$Gateways->callbackPaid($request, $Key)) {
            if ($Payment->status !== 'Complete') {
                $Payment->status = 'failed';
                $Payment->error = 'لغو شده توسط کاربر';
                $Payment->save();
                $Checkout->update(['status' => 'cancelled']);
            }
            return $this->back('cancelled', $Checkout->id);
        }

        try {
            return Cache::lock('wallet-topup:' . $Payment->id, 60)->block(20, function () use ($Gateways, $Payment, $Checkout, $Label) {
                $Payment = Payment::find($Payment->id);
                $Checkout = $Checkout->fresh();
                if ($Payment->status !== 'Complete') {
                    $Verify = $Gateways->verify($Payment, (int)round($Payment->price * 10));
                    if (!$Verify->ok && !$Verify->alreadyVerified) {
                        $Payment->status = '-4';
                        $Payment->help = $Verify->code;
                        $Payment->error = $Verify->message;
                        $Payment->details = $Label;
                        $Payment->save();
                        $Checkout->update(['status' => 'failed']);
                        return $this->back('failed', $Checkout->id);
                    }
                    $Payment->refId = $Verify->refId;
                    $Payment->details = $Label;
                    $Payment->fee = $Verify->fee;
                    $Payment->card = $Verify->card;
                    $Payment->status = 'Complete';
                    $Payment->paid_at = $Payment->paid_at ?? Carbon::now();
                    $Payment->save();
                    if ($Verify->ok) { // verified now, not by an earlier callback: credit once
                        $Owner = User::find($Payment->user_id);
                        $Owner->wallet = $Payment->price + $Owner->wallet;
                        $Owner->save();
                    }
                }
                if ($Checkout->status === 'waiting_to_pay') {
                    // The lines are bought as the payment's owner, even if the session ran out at the bank.
                    $Owner = User::find($Payment->user_id);
                    if (Auth::guard('user')->id() !== $Owner->id) {
                        Auth::guard('user')->setUser($Owner);
                    }
                    $Checkout->update(['status' => 'paying']);
                    $this->run($Checkout);
                }
                $Checkout = $Checkout->fresh();
                return $this->back($Checkout->status === 'done' ? 'ok' : ($Checkout->status === 'partial' ? 'partial' : 'failed'), $Checkout->id);
            });
        } catch (LockTimeoutException $e) {
            return $this->back('held', $Checkout->id);
        }
    }

    /* ---------- Paying the lines ---------- */

    /*
     * Pay each line from the wallet through the product's own buy endpoint, in cart order. Saved after every line,
     * so a page reload never pays one twice. Lines that fail are not charged (or refunded by their own code).
     */
    private function run(CartCheckout $Checkout): void
    {
        $Account = app(StorefrontAccountController::class);
        $Lines = $Checkout->items;
        $Paid = [];
        foreach ($Lines as $i => $Line) {
            if ($Line['result']) {
                continue;
            }
            $Request = new Request($Line['payload']);
            $Request->setLaravelSession(Session::driver());
            try {
                $Response = match ($Line['kind']) {
                    'service' => $Account->servicesBuy($Request, app(VirtualServiceOrders::class)),
                    'subscription' => $Account->subscriptionsBuy($Request, app(AccountOrders::class)),
                    'apple_id' => $Account->appleIdBuy($Request, app(AppleIds::class)),
                    'google_voice' => $Account->googleVoiceBuy($Request, app(GoogleVoiceNumbers::class)),
                };
                $Body = $Response->getData(true);
            } catch (\Throwable $e) {
                report($e);
                $Body = ['success' => false, 'message' => 'خرید این مورد انجام نشد.', 'code' => 'buy_failed', 'data' => null];
            }
            $OrderId = $Body['data']['order']['id'] ?? null;
            $Status = $Body['data']['order']['status'] ?? null;
            $Lines[$i]['order_id'] = $OrderId;
            $Lines[$i]['message'] = $Body['message'] ?? null;
            $Lines[$i]['result'] = match (true) {
                !empty($Body['success']) => in_array($Status, ['delivered', 'done'], true) ? 'delivered' : 'queued',
                ($Body['code'] ?? '') === 'sold_refunded' => 'refunded',
                default => 'not_charged',
            };
            if ($OrderId) {
                // The checkout's "available for a call 21:00–07:00?" answer (Order::booted only sees the real request).
                $Night = $Line['payload']['night_call'] ?? null;
                Order::where('id', $OrderId)->update(array_filter([
                    'checkout_id' => $Checkout->id,
                    'night_call' => $Night === null || $Night === '' ? null : (int)filter_var($Night, FILTER_VALIDATE_BOOLEAN),
                ], fn($v) => $v !== null));
            }
            if (!empty($Body['success'])) {
                $Paid[] = $Line['line'];
            }
            $Checkout->items = $Lines;
            $Checkout->save();
        }
        $Ok = collect($Lines)->whereIn('result', ['delivered', 'queued'])->count();
        $Checkout->status = $Ok === count($Lines) ? 'done' : ($Ok > 0 ? 'partial' : 'failed');
        $Checkout->save();
        // Bought lines leave the cart; the rest stay, so the buyer can see what didn't go through and try again.
        CartItem::whereIn('id', $Paid)->delete();
    }

    /* ---------- Quoting ---------- */

    /*
     * Check a line as its buy endpoint would and price it: [{price, label}, error]. `$Live` also asks the provider
     * (Google Voice: is the number still for sale?); otherwise the stored price stands for those lines.
     */
    private function quote(string $Kind, array $Payload, bool $Live): array
    {
        $Error = fn($Message, $Code, $Status = 422, $Data = []) => [null, ['message' => $Message, 'code' => $Code, 'status' => $Status, 'data' => $Data]];
        $Account = app(StorefrontAccountController::class);
        switch ($Kind) {
            case 'service':
                [$Spec, $E] = app(VirtualServiceOrders::class)->spec($Payload);
                if ($E) {
                    return $Error($E[0], $E[1], $E[2], $E[3] ?? []);
                }
                $Info = json_decode((string)$Spec['product']->detail, true) ?: [];
                $Name = ['fa' => (string)($Info['name'] ?? $Spec['product']->faName), 'en' => (string)($Info['enName'] ?? $Spec['product']->enName ?? '')];
                $Plan = $Spec['detail']['plan'] ?? [];
                $Count = (int)($Plan['count'] ?? 0);
                $First = $this->firstAnswer($Spec['detail']);
                $Stars = TelegramStars::isStars($Spec['product']);
                return [['price' => (int)$Spec['price'], 'label' => [
                    'fa' => ['title' => $Name['fa'], 'detail' => trim(number_format($Count) . ($Stars ? ' استار' : ' عدد') . ($First ? ' · ' . $First : ''))],
                    'en' => ['title' => $Name['en'] ?: $Name['fa'], 'detail' => trim(number_format($Count) . ($Stars ? ' Stars' : '') . ($First ? ' · ' . $First : ''))],
                ]], null];
            case 'subscription':
                [$Spec, $E] = app(AccountOrders::class)->spec($Payload);
                if ($E) {
                    return $Error($E[0], $E[1], $E[2], $E[3] ?? []);
                }
                $Info = json_decode((string)$Spec['product']->detail, true) ?: [];
                $Fa = AccountOrders::shortName($Spec['product'], $Info);
                $First = $this->firstAnswer($Spec['detail']);
                $Plan = (string)$Spec['plan']->name;
                return [['price' => (int)$Spec['price'], 'label' => [
                    'fa' => ['title' => $Fa, 'detail' => trim($Plan . ($First ? ' · ' . $First : ''))],
                    'en' => ['title' => (string)($Info['enName'] ?? $Spec['product']->enName ?? '') ?: $Fa, 'detail' => trim($Plan . ($First ? ' · ' . $First : ''))],
                ]], null];
            case 'apple_id':
                [$Spec, $Fail] = $Account->appleIdSpec(new Request($Payload), app(AppleIds::class));
                if ($Fail) {
                    $B = $Fail->getData(true);
                    return $Error($B['message'] ?? 'اطلاعات وارد شده معتبر نیست.', $B['code'] ?? 'details_invalid', $Fail->getStatusCode());
                }
                $Custom = $Spec['type'] === AppleIds::CUSTOM;
                return [['price' => (int)$Spec['price'], 'label' => [
                    'fa' => ['title' => $Custom ? 'اپل آیدی اختصاصی' : 'اپل آیدی آماده', 'detail' => $Custom ? (string)($Spec['details']['email'] ?? '') : number_format($Spec['qty']) . ' عدد'],
                    'en' => ['title' => $Custom ? 'Apple ID · made for you' : 'Apple ID · ready', 'detail' => $Custom ? (string)($Spec['details']['email'] ?? '') : $Spec['qty'] . ($Spec['qty'] > 1 ? ' accounts' : ' account')],
                ], 'qty' => $Custom ? null : $Spec['qty']], null];
            case 'google_voice':
                if (!$Live) {
                    return [null, ['message' => 'quote', 'code' => 'stored', 'status' => 200]];
                }
                [$Number, $Fail] = $Account->googleVoiceNumber(new Request($Payload), app(GoogleVoiceNumbers::class));
                if ($Fail) {
                    $B = $Fail->getData(true);
                    return $Error($B['message'] ?? 'این شماره فروخته شده است.', $B['code'] ?? 'sold', $Fail->getStatusCode());
                }
                $Shown = '+' . ltrim((string)$Number['number'], '+');
                return [['price' => (int)$Number['price'], 'label' => [
                    'fa' => ['title' => 'شماره گوگل ویس', 'detail' => $Shown],
                    'en' => ['title' => 'Google Voice number', 'detail' => $Shown],
                ]], null];
        }
        return $Error('این مورد را نمی توان به سبد خرید اضافه کرد.', 'kind_invalid');
    }

    private function firstAnswer(array $Detail): string
    {
        foreach ($Detail as $Key => $Field) {
            if ($Key !== 'plan' && is_array($Field) && isset($Field['filed_value'])) {
                return mb_substr((string)$Field['filed_value'], 0, 40);
            }
        }
        return '';
    }

    // Only plain values (strings, numbers, a `fields` map) from the page; nothing nested deeper.
    private function clean(array $Payload): array
    {
        $Out = [];
        foreach (array_slice($Payload, 0, 20, true) as $Key => $Value) {
            if (!is_string($Key) || in_array($Key, ['price', 'return'], true)) {
                continue;
            }
            if ($Key === 'fields' && is_array($Value)) {
                $Out['fields'] = collect($Value)->filter(fn($v, $k) => is_string($k) && is_scalar($v))->map(fn($v) => mb_substr((string)$v, 0, 200))->take(20)->all();
            } elseif (is_scalar($Value)) {
                $Out[$Key] = is_string($Value) ? mb_substr($Value, 0, 200) : $Value;
            }
        }
        return $Out;
    }

    /* ---------- Cart ---------- */

    // The visitor's cart: the user's, or the guest's (session key). A guest cart joins the user's at sign-in.
    private function cart(bool $Create): ?Cart
    {
        $User = $this->user();
        $Key = Session::get('cart_key');
        $Guest = $Key ? Cart::where('session_key', $Key)->first() : null;
        if (!$User) {
            if (!$Guest && $Create) {
                $Key = Str::random(40);
                Session::put('cart_key', $Key);
                $Guest = Cart::create(['session_key' => $Key]);
            }
            return $Guest;
        }
        $Cart = Cart::where('user_id', $User->id)->first();
        if ($Guest) {
            $Cart ??= Cart::create(['user_id' => $User->id]);
            CartItem::where('cart_id', $Guest->id)->update(['cart_id' => $Cart->id]);
            $Guest->delete();
            Session::forget('cart_key');
            // Over the limit after joining: keep the newest lines.
            $Extra = $Cart->items()->count() - self::MAX_LINES;
            if ($Extra > 0) {
                CartItem::where('cart_id', $Cart->id)->orderBy('id')->take($Extra)->delete();
            }
        }
        if (!$Cart && $Create) {
            $Cart = Cart::create(['user_id' => $User->id]);
        }
        return $Cart;
    }

    private function ownItem($id): ?CartItem
    {
        $Cart = $this->cart(false);
        return $Cart ? CartItem::where('cart_id', $Cart->id)->where('id', intval($id))->first() : null;
    }

    private function cartData(?Cart $Cart): array
    {
        $Items = $Cart ? $Cart->items()->get() : collect();
        $Lines = $Items->map(function (CartItem $Item) {
            [$Quote, $Error] = $this->quote($Item->kind, $Item->payload, false);
            $Problem = null;
            if ($Error && $Error['code'] !== 'stored') {
                $Problem = $Error['message'];
            } elseif ($Quote && $Quote['price'] !== $Item->price) {
                $Item->update(['price' => $Quote['price'], 'label' => $Quote['label']]); // rates moved: show the new price
            }
            return [
                'id' => $Item->id,
                'kind' => $Item->kind,
                'label' => $Quote['label'] ?? $Item->label,
                'price' => $Item->price,
                'qty' => $Item->kind === 'apple_id' && ($Item->payload['kind'] ?? '') !== 'custom' ? (int)($Item->payload['qty'] ?? 1) : null,
                'problem' => $Problem,
            ];
        })->values();
        $User = $this->user();
        return [
            'items' => $Lines,
            'count' => $Lines->sum(fn($l) => $l['qty'] ?? 1),
            'total' => (int)$Lines->whereNull('problem')->sum('price'),
            'wallet' => $User ? $this->wallet($User->fresh()) : null,
        ];
    }

    private function checkoutData(CartCheckout $Checkout): array
    {
        $Orders = Order::whereIn('id', collect($Checkout->items)->pluck('order_id')->filter())->get()->keyBy('id');
        return [
            'id' => $Checkout->id,
            'method' => $Checkout->method,
            'status' => $Checkout->status,
            'total' => $Checkout->total,
            'items' => collect($Checkout->items)->map(fn($l) => [
                'kind' => $l['kind'],
                'label' => $l['label'],
                'price' => $l['price'],
                'order_id' => $l['order_id'],
                // A queued order may have been delivered or refunded since.
                'result' => match (true) {
                    $l['order_id'] && optional($Orders->get($l['order_id']))->status === 'complete' => 'delivered',
                    $l['order_id'] && in_array(optional($Orders->get($l['order_id']))->status, ['back money to wallet', 'failed'], true) => 'refunded',
                    default => $l['result'],
                },
                'message' => in_array($l['result'], ['not_charged', 'refunded'], true) ? $l['message'] : null,
            ])->values(),
            'refunded' => (int)collect($Checkout->items)->whereIn('result', ['not_charged', 'refunded'])->sum('price'),
            'created_at' => $Checkout->created_at?->toIso8601String(),
        ];
    }

    private function back(string $Result, $CheckoutId)
    {
        $Path = Session::pull('storefront_cart_return') ?: '/';
        return redirect()->away(StorefrontUrl($Path) . (str_contains($Path, '?') ? '&' : '?') . http_build_query(['cart' => $Result, 'checkout' => $CheckoutId]));
    }

    private function user()
    {
        return Auth::guard('user')->user();
    }

    private function wallet($User): int
    {
        return (int)($User->wallet + $User->blocked_wallet);
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
