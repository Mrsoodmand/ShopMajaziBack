<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Payment\BankController;
use App\Http\Controllers\Payment\WalletController;
use App\Http\Controllers\v2\VirtualNumberController;
use App\Models\Currency;
use App\Models\MobileVerfiy;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Virtual_Products;
use App\Services\AppleIds;
use App\Services\GoogleVoiceNumbers;
use App\Services\VirtualServiceOrders;
use App\User;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/*
 * Next.js storefront (Front/): sign-in, wallet balance and the virtual-number orders of the signed-in user.
 * Runs in the `web` group, so it shares the Laravel session (and the `user` guard) with the old site; the browser
 * reaches it same-origin through the Next.js /api/v1 rewrite and sends the XSRF-TOKEN cookie back as X-XSRF-TOKEN.
 * Buying, the SMS check and cancelling reuse the old site's controllers, so the money logic lives in one place.
 * Responses: {success, message, data}; errors also carry `code` (a stable key the front translates).
 */
class StorefrontAccountController extends Controller
{
    private const CODE_MINUTES = 5;      // a sign-in / sign-up code works this long
    private const RESEND_SECONDS = 120;  // wait between two codes
    private const CODE_TRIES = 5;        // wrong guesses before a code stops working
    private const CODES_PER_HOUR = 5;
    private const SERVERS = ['pva' => 1, 'man' => 2, 'active' => 3];

    /* ---------- Account ---------- */

    public function me(): JsonResponse
    {
        $User = $this->user();
        return $this->ok([
            'authenticated' => !is_null($User),
            'user' => $User ? ['name' => $User->name, 'mobile' => $User->mobile] : null,
            'wallet' => $User ? $this->wallet($User) : 0,
        ]);
    }

    /* Step 1: a mobile number. Existing users go on to password or code; new users get a sign-up code. */
    public function start(Request $request): JsonResponse
    {
        $Mobile = $this->mobile($request->input('mobile'));
        if (!$Mobile) {
            return $this->fail('شماره موبایل نامعتبر است.', 'mobile_invalid', 422);
        }
        if (User::where('mobile', $Mobile)->exists()) {
            return $this->ok(['mobile' => $Mobile, 'exists' => true]);
        }
        return $this->sendCode($Mobile, false);
    }

    /* Sends (or re-sends) a code: a one-time sign-in code for existing users, the sign-up code for new ones. */
    public function code(Request $request): JsonResponse
    {
        $Mobile = $this->mobile($request->input('mobile'));
        if (!$Mobile) {
            return $this->fail('شماره موبایل نامعتبر است.', 'mobile_invalid', 422);
        }
        return $this->sendCode($Mobile, User::where('mobile', $Mobile)->exists());
    }

    /* Sign in with a password or a one-time code. */
    public function login(Request $request): JsonResponse
    {
        $Mobile = $this->mobile($request->input('mobile'));
        $User = $Mobile ? User::where('mobile', $Mobile)->first() : null;
        if (is_null($User)) {
            return $this->fail('کاربری با این شماره وجود ندارد.', 'no_user', 404);
        }
        if ($request->filled('password')) {
            if (!Hash::check((string)$request->input('password'), $User->password)) {
                return $this->fail('کلمه عبور اشتباه است.', 'wrong_password', 422);
            }
        } elseif (!$this->useCode($Mobile, $request->input('code'), true)) {
            return $this->fail('کد وارد شده صحیح نیست یا منقضی شده است.', 'wrong_code', 422);
        }
        Auth::guard('user')->login($User, true);
        $request->session()->regenerate();
        return $this->me();
    }

    /* New account: the sign-up code, a name and a password (email optional). */
    public function register(Request $request): JsonResponse
    {
        $Mobile = $this->mobile($request->input('mobile'));
        if (!$Mobile) {
            return $this->fail('شماره موبایل نامعتبر است.', 'mobile_invalid', 422);
        }
        $Name = trim((string)$request->input('name'));
        $Password = (string)$request->input('password');
        $Email = trim((string)$request->input('email'));
        if (mb_strlen($Name) < 3 || mb_strlen($Name) > 40) {
            return $this->fail('نام باید بین ۳ تا ۴۰ حرف باشد.', 'name_invalid', 422);
        }
        if (strlen($Password) < 6 || strlen($Password) > 30) {
            return $this->fail('کلمه عبور باید بین ۶ تا ۳۰ کاراکتر باشد.', 'password_invalid', 422);
        }
        if ($Email !== '' && !filter_var($Email, FILTER_VALIDATE_EMAIL)) {
            return $this->fail('ایمیل نامعتبر است.', 'email_invalid', 422);
        }
        if (User::where('mobile', $Mobile)->exists()) {
            return $this->fail('کاربری با این شماره وجود دارد.', 'user_exists', 409);
        }
        if (!$this->useCode($Mobile, $request->input('code'), false)) {
            return $this->fail('کد وارد شده صحیح نیست یا منقضی شده است.', 'wrong_code', 422);
        }

        // Referral: CheckRefCode keeps ?ref=<token> in the session.
        $Parent = 0;
        if ($request->session()->has('RefCode')) {
            $Parent = (int)optional(User::where('token', $request->session()->get('RefCode'))->first())->id;
        }
        $User = new User();
        $User->name = $Name;
        $User->mobile = $Mobile;
        $User->password = bcrypt($Password);
        $User->email = $Email !== '' ? $Email : null;
        $User->parent_id = $Parent;
        $User->token = GenerateApiKey();
        $User->save();

        Auth::guard('user')->login($User, true);
        $request->session()->regenerate();
        return $this->me();
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('user')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->me();
    }

    /* ---------- Virtual-number orders ---------- */

    /* Recent virtual-number orders (newest first). */
    public function orders(): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Orders = Order::where('type', 1)->where('user_id', $User->id)->where('pay_status', 'Complete') // paid ones only: not abandoned or cancelled at the bank
            ->orderby('id', 'DESC')->take(20)->get();
        $Items = OrderItem::whereIn('order_id', $Orders->pluck('id'))->get()->keyBy('order_id');
        $Products = Virtual_Products::whereIn('id', $Items->pluck('product_id'))->get()->keyBy('id');
        return $this->ok([
            'orders' => $Orders->map(fn($o) => $this->orderData($o, $Items->get($o->id), $Products))->values(),
            'wallet' => $this->wallet($User),
        ]);
    }

    /*
     * Buy with the wallet. `price` is what the page showed; if the charge would differ (rates moved since the page
     * loaded), nothing is bought and the new price comes back (code price_changed).
     */
    public function buy(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Product = Virtual_Products::where('id', intval($request->input('product_id')))->where('status', 1)->where('toman_price', '>', 0)->first();
        if (is_null($Product)) {
            return $this->fail('این شماره در حال حاضر در دسترس نیست.', 'unavailable', 404);
        }
        $Price = $this->chargePrice($Product);
        if ($request->filled('price') && intval($request->input('price')) !== $Price) {
            return $this->fail('قیمت این شماره تغییر کرده است.', 'price_changed', 409, ['price' => $Price]);
        }

        $Response = app(WalletController::class)->PayWithWallet(1, $Product->id, $request);
        $Body = $Response->getData(true);
        if ($Response->getStatusCode() === 200 && isset($Body['data']['id'])) {
            $Order = Order::find($Body['data']['id']);
            return $this->ok(['order' => $this->orderData($Order), 'wallet' => $this->wallet($User->fresh())], 'شماره با موفقیت دریافت شد.');
        }

        $Message = $Body['error'][0] ?? 'خرید انجام نشد.';
        $Code = match (true) {
            $Response->getStatusCode() === 429 => 'busy',
            str_contains($Message, 'موجودی') => 'balance',
            str_contains($Message, 'شماره موجود') => 'out_of_stock',
            str_contains($Message, 'مجاز به پرداخت') => 'wallet_blocked',
            default => 'buy_failed',
        };
        return $this->fail($Message, $Code, $Code === 'busy' ? 429 : 400, $Code === 'balance' ? ['wallet' => $this->wallet($User), 'price' => $Price] : []);
    }

    /*
     * Pay by card (ZarinPal). Same checks as buy; then an order waiting for the bank is made and the page gets the
     * gateway URL to send the buyer to. After paying, the bank callback (Payment\CallBackController) issues the
     * number and sends the buyer back to `return` (a path on the storefront) with ?pay=…&order=….
     */
    public function payByCard(Request $request): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        $Product = Virtual_Products::where('id', intval($request->input('product_id')))->where('status', 1)->where('toman_price', '>', 0)->first();
        if (is_null($Product)) {
            return $this->fail('این شماره در حال حاضر در دسترس نیست.', 'unavailable', 404);
        }
        $Price = $this->chargePrice($Product);
        if ($request->filled('price') && intval($request->input('price')) !== $Price) {
            return $this->fail('قیمت این شماره تغییر کرده است.', 'price_changed', 409, ['price' => $Price]);
        }
        $Return = (string)$request->input('return');
        $Return = str_starts_with($Return, '/') && !str_starts_with($Return, '//') ? $Return : '/virtual-number';

        $Bank = app(BankController::class);
        [$Order, $Payment] = $Bank->createVirtualNumberBankOrder($Product);
        $Url = $Bank->zarinPalStartUrl($Payment, $Order, StorefrontUrl(route('user.service-bank-callback', [], false)));
        if (!$Url) {
            $Order->status = $Order->pay_status = 'failed';
            $Order->save();
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        $request->session()->put('storefront_return', $Return);
        return $this->ok(['url' => $Url, 'order_id' => $Order->id]);
    }

    /* Local development only (DEV_FAKE_PROVIDERS): a stand-in for ZarinPal's payment page. */
    public function devBank($authority)
    {
        $Known = DevFakeProviders() ? Cache::get("dev-zarinpal:$authority") : null;
        abort_if(is_null($Known), 404);
        $Back = fn($Status) => e($Known['callback'] . '?' . http_build_query(['Authority' => $authority, 'Status' => $Status]));
        $Toman = number_format($Known['amount'] / 10);
        return response(<<<HTML
            <!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
            <title>Fake bank (local)</title>
            <body style="font-family:system-ui;max-width:420px;margin:12vh auto;padding:0 16px;color:#0f172a">
              <p style="font-size:12px;color:#b45309;background:#fffbeb;padding:8px 12px;border-radius:8px">Local development: this page stands in for ZarinPal. No money moves.</p>
              <h1 style="font-size:22px">Pay {$Toman} Toman</h1>
              <p style="color:#64748b">Authority {$authority}</p>
              <a href="{$Back('OK')}" style="display:block;text-align:center;background:#4f46e5;color:#fff;padding:14px;border-radius:14px;text-decoration:none;font-weight:600">Pay (success)</a>
              <a href="{$Back('NOK')}" style="display:block;text-align:center;margin-top:10px;padding:14px;border-radius:14px;color:#334155;border:1px solid #e2e8f0;text-decoration:none">Cancel payment</a>
            </body></html>
            HTML);
    }

    /* One order, after asking the provider for the SMS (the old site's get-sms: also refunds an expired order). */
    public function order($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = $this->ownOrder($User, $id);
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        if ($Order->status === 'pending') {
            $this->locked($Order, function () use ($Order) {
                if ($Order->fresh()->status === 'pending') {
                    app(VirtualNumberController::class)->CheckNumberSms(new Request(['id' => $Order->id]));
                }
            });
        }
        return $this->ok(['order' => $this->orderData($Order->fresh()), 'wallet' => $this->wallet($User->fresh())]);
    }

    /* Cancel a number that has no code yet; the price goes back to the wallet. */
    public function cancel($id): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = $this->ownOrder($User, $id);
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        $Error = null;
        $Done = $this->locked($Order, function () use ($Order, &$Error) {
            if ($Order->fresh()->status !== 'pending') {
                return; // already finished (code, expiry or an earlier cancel): just report the state
            }
            $Response = app(VirtualNumberController::class)->CancelNumber(new Request(['id' => $Order->id]));
            if ($Response->getStatusCode() !== 200 && $Order->fresh()->status === 'pending') {
                $Error = $Response->getData(true)['error'][0] ?? 'لغو انجام نشد.';
            }
        });
        if (!$Done) {
            return $this->fail('لطفا چند لحظه دیگر دوباره تلاش کنید.', 'busy', 429);
        }
        if ($Error) {
            // Providers refuse to cancel in the first minutes after issuing a number.
            return $this->fail($Error, 'cancel_failed', 400, ['order' => $this->orderData($Order->fresh())]);
        }
        return $this->ok(['order' => $this->orderData($Order->fresh()), 'wallet' => $this->wallet($User->fresh())]);
    }

    /* ---------- Google Voice (Numberland special numbers, see App\Services\GoogleVoiceNumbers) ---------- */

    /* Buy with the wallet: {id, price}. The number is asked fresh from Numberland first, as with virtual numbers. */
    public function googleVoiceBuy(Request $request, GoogleVoiceNumbers $Numbers): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->wallet_blocked != 0) {
            return $this->fail('شما مجاز به پرداخت این سفارش با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید', 'wallet_blocked', 403);
        }
        try {
            return WalletPurchaseLock($User->id)->block(5, function () use ($request, $Numbers, $User) {
                [$Number, $Error] = $this->googleVoiceNumber($request, $Numbers);
                if ($Error) {
                    return $Error;
                }
                $User = $User->fresh();
                if ($this->wallet($User) < $Number['price']) {
                    return $this->fail('موجودی کیف پول شما کافی نیست.', 'balance', 400, ['wallet' => $this->wallet($User), 'price' => $Number['price']]);
                }
                $Order = $Numbers->buyWithWallet($User, $Number);
                return $this->googleVoiceResult($Order, $User);
            });
        } catch (LockTimeoutException $e) {
            return $this->fail('لطفا چند لحظه دیگر دوباره تلاش کنید.', 'busy', 429);
        }
    }

    /* Pay by card: {id, price, return}. The bank callback buys the number and sends the buyer back with ?pay=…&order=…. */
    public function googleVoicePayCard(Request $request, GoogleVoiceNumbers $Numbers): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        [$Number, $Error] = $this->googleVoiceNumber($request, $Numbers);
        if ($Error) {
            return $Error;
        }
        $Return = (string)$request->input('return');
        $Return = str_starts_with($Return, '/') && !str_starts_with($Return, '//') ? $Return : '/google-voice';

        [$Order, $Payment] = $Numbers->bankOrder($User, $Number);
        $Url = app(BankController::class)->zarinPalStartUrl($Payment, $Order, StorefrontUrl(route('user.service-bank-callback', [], false)));
        if (!$Url) {
            $Order->status = $Order->pay_status = 'failed';
            $Order->save();
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        $request->session()->put('storefront_return', $Return);
        return $this->ok(['url' => $Url, 'order_id' => $Order->id]);
    }

    /* One of my Google Voice orders, with the login once delivered. */
    public function googleVoiceOrder($id, GoogleVoiceNumbers $Numbers): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = Order::where('id', intval($id))->where('type', GoogleVoiceNumbers::ORDER_TYPE)->where('user_id', $User->id)->first();
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        return $this->ok(['order' => $this->googleVoiceData($Order, $Numbers), 'wallet' => $this->wallet($User->fresh())]);
    }

    // The number the buyer picked, checked with Numberland and against the price the page showed: [number, error].
    private function googleVoiceNumber(Request $request, GoogleVoiceNumbers $Numbers): array
    {
        if (!$Numbers->enabled()) { // switched off in admin › Google Voice
            return [null, $this->fail('فروش گوگل ویس موقتا متوقف است. لطفا کمی بعد دوباره تلاش کنید.', 'gv_paused', 503)];
        }
        try {
            $Number = $Numbers->find(intval($request->input('id')));
            // Our Numberland balance must cover it, or the buyer would pay for a number we can't get.
            if ($Number && !$Numbers->canAfford($Number['cost'])) {
                return [null, $this->fail('فروش گوگل ویس موقتا متوقف است. لطفا کمی بعد دوباره تلاش کنید.', 'gv_paused', 503)];
            }
        } catch (\Throwable $e) {
            report($e);
            return [null, $this->fail('ارتباط با سرور شماره‌ها برقرار نشد. لطفا دوباره تلاش کنید.', 'provider_unavailable', 502)];
        }
        if (is_null($Number)) {
            return [null, $this->fail('این شماره فروخته شده است. لطفا شماره دیگری انتخاب کنید.', 'sold', 404)];
        }
        if ($request->filled('price') && intval($request->input('price')) !== $Number['price']) {
            return [null, $this->fail('قیمت این شماره تغییر کرده است.', 'price_changed', 409, ['price' => $Number['price']])];
        }
        return [$Number, null];
    }

    private function googleVoiceResult(Order $Order, $User): JsonResponse
    {
        $Data = ['order' => $this->googleVoiceData($Order, app(GoogleVoiceNumbers::class)), 'wallet' => $this->wallet($User->fresh())];
        return match ($Order->status) {
            'complete' => $this->ok($Data, 'شماره گوگل ویس تحویل شد.'),
            'failed' => $this->fail('این شماره همین الان فروخته شد. مبلغ به کیف پول شما برگشت؛ شماره دیگری انتخاب کنید.', 'sold_refunded', 409, $Data),
            default => $this->ok($Data, 'سفارش ثبت شد و پشتیبانی در حال بررسی آن است.'),
        };
    }

    private function googleVoiceData(Order $Order, GoogleVoiceNumbers $Numbers): array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Detail = $Item ? (json_decode($Item->detail, true) ?: []) : [];
        $Status = match ($Order->status) {
            'complete' => 'delivered',
            'failed' => 'refunded',
            'waiting to pay' => 'unpaid',
            default => 'review', // pending (being bought) or review (support checks)
        };
        return [
            'id' => $Order->id,
            'status' => $Status,
            'number' => $Detail['number'] ?? null,
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
            'login' => $Status === 'delivered' ? $Numbers->login($Order) : null,
        ];
    }

    /* ---------- Apple ID (see App\Services\AppleIds) ---------- */

    /*
     * Buy with the wallet: {kind: ready, qty, price} or {kind: custom, first, last, email, birthday, password?, price}.
     * `price` is the total the page showed. Ready Apple IDs are handed over at once; custom ones go to support.
     */
    public function appleIdBuy(Request $request, AppleIds $Apple): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->wallet_blocked != 0) {
            return $this->fail('شما مجاز به پرداخت این سفارش با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید', 'wallet_blocked', 403);
        }
        [$Spec, $Error] = $this->appleIdSpec($request, $Apple);
        if ($Error) {
            return $Error;
        }
        if ($this->wallet($User) < $Spec['price']) {
            return $this->fail('موجودی کیف پول شما کافی نیست.', 'balance', 400, ['wallet' => $this->wallet($User), 'price' => $Spec['price']]);
        }

        [$Order] = $Apple->createOrder($User, $Spec['type'], $Spec['qty'], $Spec['price'], $Spec['details']);
        $Response = app(WalletController::class)->PayWithWallet($Spec['type'], $Order->id, $request);
        if ($Response->getStatusCode() !== 200) {
            $Order->status = $Order->pay_status = 'failed'; // nothing was charged; don't leave it waiting
            $Order->save();
            $Message = $Response->getData(true)['error'][0] ?? 'خرید انجام نشد.';
            $Code = match (true) {
                $Response->getStatusCode() === 429 => 'busy',
                str_contains($Message, 'موجودی') => 'balance',
                default => 'buy_failed',
            };
            return $this->fail($Message, $Code, $Code === 'busy' ? 429 : 400, $Code === 'balance' ? ['wallet' => $this->wallet($User->fresh()), 'price' => $Spec['price']] : []);
        }

        $Order = $Order->fresh();
        $Message = $Order->status === 'complete' ? 'اپل آیدی تحویل شد.' : 'سفارش ثبت شد و به زودی آماده می شود.';
        return $this->ok(['order' => $this->appleIdData($Order, $Apple), 'wallet' => $this->wallet($User->fresh())], $Message);
    }

    /* Pay by card: the same body plus `return`. The bank callback delivers and sends the buyer back with ?pay=…&order=…. */
    public function appleIdPayCard(Request $request, AppleIds $Apple): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        [$Spec, $Error] = $this->appleIdSpec($request, $Apple);
        if ($Error) {
            return $Error;
        }
        $Return = (string)$request->input('return');
        $Return = str_starts_with($Return, '/') && !str_starts_with($Return, '//') ? $Return : '/apple-id';

        [$Order, $Payment] = $Apple->createOrder($User, $Spec['type'], $Spec['qty'], $Spec['price'], $Spec['details']);
        $Url = app(BankController::class)->zarinPalStartUrl($Payment, $Order, StorefrontUrl(route('user.service-bank-callback', [], false)));
        if (!$Url) {
            $Order->status = $Order->pay_status = 'failed';
            $Order->save();
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        $request->session()->put('storefront_return', $Return);
        return $this->ok(['url' => $Url, 'order_id' => $Order->id]);
    }

    /* One of my Apple ID orders, with the logins delivered so far. */
    public function appleIdOrder($id, AppleIds $Apple): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = Order::where('id', intval($id))->whereIn('type', [AppleIds::READY, AppleIds::CUSTOM])->where('user_id', $User->id)->first();
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        return $this->ok(['order' => $this->appleIdData($Order, $Apple), 'wallet' => $this->wallet($User->fresh())]);
    }

    // What the buyer asked for, checked, with the price: [{type, qty, price, details}, error].
    private function appleIdSpec(Request $request, AppleIds $Apple): array
    {
        $Prices = $Apple->prices();
        $Custom = $request->input('kind') === 'custom';
        $Each = $Custom ? $Prices['custom'] : $Prices['ready'];
        if ($Each <= 0) {
            return [null, $this->fail('فروش اپل آیدی موقتا متوقف است. لطفا کمی بعد دوباره تلاش کنید.', 'apple_paused', 503)];
        }

        if ($Custom) {
            $First = trim((string)$request->input('first'));
            $Last = trim((string)$request->input('last'));
            $Email = trim((string)$request->input('email'));
            $Birthday = (string)$request->input('birthday'); // YYYY-MM-DD
            $Password = (string)$request->input('password');
            $Name = '/^\p{L}[\p{L} \'\-]{0,49}$/u';
            $Date = \DateTime::createFromFormat('!Y-m-d', $Birthday);
            $Valid = preg_match($Name, $First) && preg_match($Name, $Last)
                && filter_var($Email, FILTER_VALIDATE_EMAIL) && strlen($Email) <= 100
                && $Date && $Date->format('Y-m-d') === $Birthday && $Date->format('Y') >= 1900 && $Date <= Carbon::now()->subYears(13)
                && ($Password === '' || (strlen($Password) >= 8 && strlen($Password) <= 64 && preg_match('/[A-Z]/', $Password) && preg_match('/[a-z]/', $Password) && preg_match('/\d/', $Password)));
            if (!$Valid) {
                return [null, $this->fail('اطلاعات وارد شده معتبر نیست.', 'details_invalid', 422)];
            }
            // Same keys as the old form (admin › order reads them); the password only if the buyer chose one.
            $Details = ['name' => $First, 'family' => $Last, 'email' => $Email, 'b_day' => $Birthday] + ($Password !== '' ? ['password' => $Password] : []);
            $Spec = ['type' => AppleIds::CUSTOM, 'qty' => 1, 'price' => $Each, 'details' => $Details];
        } else {
            $Qty = intval($request->input('qty'));
            if (!$Apple->validQuantity($Qty)) {
                return [null, $this->fail('تعداد انتخاب شده معتبر نیست.', 'qty_invalid', 422)];
            }
            // No stock check: a ready order bigger than the stock is a pre-order. Paying hands over what is in stock
            // now and leaves the order pending; admin › Apple ID fills pending orders when it adds new ones
            // (Payment\WalletController / CallBackController::NormalAppleId, Admin\AppleIdController).
            $Spec = ['type' => AppleIds::READY, 'qty' => $Qty, 'price' => $Each * $Qty, 'details' => null];
        }

        if ($request->filled('price') && intval($request->input('price')) !== $Spec['price']) {
            return [null, $this->fail('قیمت تغییر کرده است.', 'price_changed', 409, ['price' => $Spec['price']])];
        }
        return [$Spec, null];
    }

    private function appleIdData(Order $Order, AppleIds $Apple): array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Ready = (int)$Order->type === AppleIds::READY;
        $Detail = !$Ready && $Item ? (json_decode($Item->detail, true) ?: []) : [];
        $Status = match ($Order->status) {
            'complete' => 'delivered',
            'waiting to pay' => 'unpaid',
            'failed' => 'failed',
            default => 'preparing', // paid; support is making it (custom) or stock ran short (ready)
        };
        return [
            'id' => $Order->id,
            'kind' => $Ready ? 'ready' : 'custom',
            'status' => $Status,
            'qty' => $Ready ? max(1, intval($Item->detail ?? 1)) : 1,
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
            'accounts' => in_array($Status, ['delivered', 'preparing']) ? $Apple->accounts($Order) : [],
            'details' => $Ready ? null : ['first' => $Detail['name'] ?? '', 'last' => $Detail['family'] ?? '', 'email' => $Detail['email'] ?? ''],
        ];
    }

    /* ---------- Social media services (VirtualServiceOrders) ---------- */

    /*
     * Buy with the wallet: {service_id, plan_id | count, fields: {name: value}, price}. `price` is the total the page
     * showed. Paid orders wait in admin › orders until support delivers them.
     */
    public function servicesBuy(Request $request, VirtualServiceOrders $Services): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->wallet_blocked != 0) {
            return $this->fail('شما مجاز به پرداخت این سفارش با کیف پول نیستید، لطفا با پشتیبانی تماس بگیرید', 'wallet_blocked', 403);
        }
        [$Spec, $Error] = $Services->spec($request->all());
        if ($Error) {
            return $this->fail(...$Error);
        }
        if ($this->wallet($User) < $Spec['price']) {
            return $this->fail('موجودی کیف پول شما کافی نیست.', 'balance', 400, ['wallet' => $this->wallet($User), 'price' => $Spec['price']]);
        }

        [$Order] = $Services->createOrder($User, $Spec);
        $Response = app(WalletController::class)->PayWithWallet(VirtualServiceOrders::TYPE, $Order->id, $request);
        if ($Response->getStatusCode() !== 200) {
            $Order->status = $Order->pay_status = 'failed'; // nothing was charged; don't leave it waiting
            $Order->save();
            $Message = $Response->getData(true)['error'][0] ?? 'خرید انجام نشد.';
            $Code = match (true) {
                $Response->getStatusCode() === 429 => 'busy',
                str_contains($Message, 'موجودی') => 'balance',
                default => 'buy_failed',
            };
            return $this->fail($Message, $Code, $Code === 'busy' ? 429 : 400, $Code === 'balance' ? ['wallet' => $this->wallet($User->fresh()), 'price' => $Spec['price']] : []);
        }
        return $this->ok(['order' => $Services->data($Order->fresh()), 'wallet' => $this->wallet($User->fresh())], 'سفارش ثبت شد و به زودی شروع می شود.');
    }

    /* Pay by card: the same body plus `return`. The bank callback sends the buyer back with ?pay=…&order=…. */
    public function servicesPayCard(Request $request, VirtualServiceOrders $Services): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        if ($User->bank_blocked != 0) {
            return $this->fail('امکان پرداخت با درگاه بانک برای حساب شما وجود ندارد، لطفا با پشتیبانی تماس بگیرید.', 'bank_blocked', 403);
        }
        [$Spec, $Error] = $Services->spec($request->all());
        if ($Error) {
            return $this->fail(...$Error);
        }
        $Return = (string)$request->input('return');
        $Return = str_starts_with($Return, '/') && !str_starts_with($Return, '//') ? $Return : '/virtual-service';

        [$Order, $Payment] = $Services->createOrder($User, $Spec);
        $Url = app(BankController::class)->zarinPalStartUrl($Payment, $Order, StorefrontUrl(route('user.service-bank-callback', [], false)));
        if (!$Url) {
            $Order->status = $Order->pay_status = 'failed';
            $Order->save();
            return $this->fail('اتصال به درگاه پرداخت برقرار نشد. لطفا دوباره تلاش کنید.', 'bank_unavailable', 502);
        }
        $request->session()->put('storefront_return', $Return);
        return $this->ok(['url' => $Url, 'order_id' => $Order->id]);
    }

    /* One of my service orders and where it stands (support moves it along in admin › orders). */
    public function servicesOrder($id, VirtualServiceOrders $Services): JsonResponse
    {
        if (!$User = $this->user()) {
            return $this->signInFirst();
        }
        $Order = Order::where('id', intval($id))->where('type', VirtualServiceOrders::TYPE)->where('user_id', $User->id)->first();
        if (is_null($Order)) {
            return $this->fail('سفارش یافت نشد.', 'not_found', 404);
        }
        return $this->ok(['order' => $Services->data($Order), 'wallet' => $this->wallet($User->fresh())]);
    }

    /* ---------- Helpers ---------- */

    private function user()
    {
        return Auth::guard('user')->user();
    }

    // What the wallet can spend (CheckWallet(): balance plus the blocked part, which purchases use first).
    private function wallet($User): int
    {
        return (int)($User->wallet + $User->blocked_wallet);
    }

    // The price WalletController::payWithWalletLocked charges for a virtual number (same formula).
    private function chargePrice(Virtual_Products $Product): int
    {
        $Currency = Currency::whereIn('id', [1, 34])->select('sell', 'id')->get();
        $Rate = $Product->type == 'pva' ? $Currency->where('id', 1)->first()->sell * 2 : $Currency->where('id', 34)->first()->sell * 2.5;
        return (int)str_replace(',', '', CovertVirtualNumberPrice(number_format($Rate * $Product->dollar_price)));
    }

    private function ownOrder($User, $id): ?Order
    {
        return Order::where('id', intval($id))->where('type', 1)->where('user_id', $User->id)->first();
    }

    // One SMS check / cancel at a time per order, so a poll and a cancel can't both refund it.
    private function locked(Order $Order, callable $Run): bool
    {
        try {
            Cache::lock('vn-order:' . $Order->id, 30)->block(10, $Run);
            return true;
        } catch (LockTimeoutException $e) {
            return false;
        }
    }

    private function orderData(Order $Order, ?OrderItem $Item = null, $Products = null): array
    {
        $Item ??= OrderItem::where('order_id', $Order->id)->first();
        $Detail = $Item ? (json_decode($Item->detail, true) ?: []) : [];
        $Product = $Item ? ($Products ? $Products->get($Item->product_id) : Virtual_Products::find($Item->product_id)) : null;
        $Left = $Order->expire_date ? max(0, (int) Carbon::now()->diffInSeconds(Carbon::parse($Order->expire_date), false)) : 0;
        $Status = match ($Order->status) {
            'pending' => 'waiting',
            'complete' => 'received',
            default => $Order->wallet_status == 1 ? 'expired' : 'cancelled',
        };
        return [
            'id' => $Order->id,
            'product_id' => $Product?->id,
            'service_id' => $Product?->service_id,
            'country_id' => $Product?->country_id,
            'server' => $Product ? (self::SERVERS[$Product->type] ?? 1) : null,
            'number' => isset($Detail['number']) ? '+' . ltrim((string)$Detail['number'], '+') : null,
            'code' => $Detail['sms'] ?? null,
            'status' => $Status,
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
            'seconds_left' => $Status === 'waiting' ? $Left : 0,
        ];
    }

    // "۰۹۱۲ ۳۴۵ ۶۷۸۹", "+98912…", "912…" → "0912…"; null if it isn't an Iranian mobile number.
    private function mobile($Raw): ?string
    {
        $Mobile = preg_replace('/\D/', '', PersianNumToEn(str_replace(['٠', '١', '٢', '٣', '٧', '٨', '٩'], ['0', '1', '2', '3', '7', '8', '9'], (string)$Raw)));
        $Mobile = preg_replace('/^(0098|98)/', '', $Mobile);
        $Mobile = strlen($Mobile) === 10 ? '0' . $Mobile : $Mobile;
        return preg_match('/^09\d{9}$/', $Mobile) ? $Mobile : null;
    }

    // Codes live in mobile_verfiys (as on the old site): type 2 = sign-in, empty type = sign-up.
    private function codes(string $Mobile, bool $Login)
    {
        $Query = MobileVerfiy::where('mobile', $Mobile);
        return $Login ? $Query->where('type', 2) : $Query->whereNull('type');
    }

    private function sendCode(string $Mobile, bool $Login): JsonResponse
    {
        $Last = $this->codes($Mobile, $Login)->orderBy('id', 'DESC')->first();
        $Wait = $Last ? self::RESEND_SECONDS - (int) Carbon::parse($Last->created_at)->diffInSeconds(Carbon::now()) : 0;
        if ($Last && $Last->status == 0 && $Wait > 0) {
            return $this->ok(['mobile' => $Mobile, 'exists' => $Login, 'resend_in' => $Wait] + $this->devCode($Last));
        }
        if ($this->codes($Mobile, $Login)->where('created_at', '>', Carbon::now()->subHour())->count() >= self::CODES_PER_HOUR) {
            return $this->fail('تعداد درخواست‌ها زیاد بود. لطفا یک ساعت دیگر دوباره تلاش کنید.', 'too_many_codes', 429);
        }
        $Code = new MobileVerfiy();
        $Code->mobile = $Mobile;
        $Code->code = random_int(10000, 99999);
        $Code->status = 0;
        $Code->type = $Login ? 2 : null;
        $Code->save();
        Cache::forget("vn-code-tries:$Mobile");
        if ($Login) {
            sendSmsWithSmsir($Mobile, ['CODE' => $Code->code], 927153);
        } else {
            SendVerifySmsSmsIr($Mobile, $Code->code);
        }
        return $this->ok(['mobile' => $Mobile, 'exists' => $Login, 'resend_in' => self::RESEND_SECONDS] + $this->devCode($Code));
    }

    // Checks and uses up the latest code. Too many wrong guesses spend the code.
    private function useCode(string $Mobile, $Input, bool $Login): bool
    {
        $Input = preg_replace('/\D/', '', PersianNumToEn((string)$Input));
        $Code = $this->codes($Mobile, $Login)->where('status', 0)->orderBy('id', 'DESC')->first();
        if (is_null($Code) || $Input === '' || Carbon::parse($Code->created_at)->addMinutes(self::CODE_MINUTES)->isPast()) {
            return false;
        }
        $Tries = Cache::increment("vn-code-tries:$Mobile");
        if ($Tries > self::CODE_TRIES) {
            $Code->status = 1;
            $Code->save();
            return false;
        }
        if ((string)$Code->code !== $Input) {
            return false;
        }
        $Code->status = 1;
        $Code->save();
        Cache::forget("vn-code-tries:$Mobile");
        return true;
    }

    // Local development (fake SMS): hand the code to the page so sign-in can be tried.
    private function devCode(MobileVerfiy $Code): array
    {
        return DevFakeProviders() ? ['dev_code' => (string)$Code->code] : [];
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
