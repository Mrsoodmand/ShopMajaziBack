<?php

namespace App\Services;

use App\Models\AccountRequest;
use App\Models\AccountRequestField;
use App\Models\Order;
use App\Models\Setting;
use App\User as OrderUser; // OrderCharges::make() takes this user class
use Illuminate\Support\Facades\DB;

/*
 * «درخواست اکانت»: a signed-in customer asks for a subscription the site doesn't sell (e.g. qq.com).
 *
 *   1. The customer fills in the form the admin set up (account_request_fields: site_name + site_url built in, then
 *      the admin's own fields; App\Services\FormFields checks them) → an account_requests row, status pending. Files
 *      go to a private disk. Telegram gets a notice.
 *   2. Support (React admin › اکانت‌های خارجی › درخواست‌ها) quotes it — an ordinary custom order of type 9
 *      (App\Services\OrderCharges::make, «در انتظار پرداخت») that the customer pays in /account like any other —
 *      or rejects it with a note. The customer may withdraw a request that is still pending.
 *   3. Once quoted, the request follows its order: shown «پرداخت شد» when the order is paid, «لغو شد» when it was
 *      cancelled and refunded. Delivery is then the order's own flow (status, messages, invoice).
 */
class AccountRequests
{
    public const STATUSES = ['pending', 'quoted', 'rejected', 'cancelled'];
    // What the customer and admin see: quoted splits by its order.
    public const SHOWN = ['pending', 'quoted', 'paid', 'rejected', 'cancelled'];
    public const BUILTIN = ['site_name', 'site_url'];
    private const CLOSED_ORDER = ['back money to wallet', 'failed', 'cancel'];

    private static ?array $settings = null;

    /* ---------- Settings and form ---------- */

    /** @return array{open: bool} */
    public static function settings(): array
    {
        if (self::$settings === null) {
            $Raw = json_decode((string)optional(Setting::where('name', 'account_request')->first())->key, true) ?: [];
            self::$settings = ['open' => (bool)($Raw['open'] ?? true)];
        }
        return self::$settings;
    }

    public static function saveSettings(array $Input): array
    {
        $Row = Setting::where('name', 'account_request')->first() ?? new Setting();
        $Row->name = 'account_request';
        $Row->key = json_encode(['open' => (bool)($Input['open'] ?? true)]);
        $Row->save();
        self::$settings = null;
        return self::settings();
    }

    public static function fields(bool $OnlyEnabled = true)
    {
        return AccountRequestField::query()->when($OnlyEnabled, fn($q) => $q->where('enabled', true))->orderBy('sort')->orderBy('id')->get();
    }

    /** site_name is the request's title, so it stays on and required. */
    public static function saveFields(array $Rows): array
    {
        return FormFields::save(AccountRequestField::class, $Rows, self::BUILTIN, (int)config('account_requests.max_mb', 10), ['site_name']);
    }

    public static function folder(int $RequestId): string
    {
        return 'account-requests/' . $RequestId;
    }

    /* ---------- Customer ---------- */

    /** The form (for everyone) and, when signed in, the customer's own requests, newest first. */
    public static function state($User): array
    {
        $Open = self::settings()['open'];
        return [
            'open' => $Open,
            'fields' => $Open ? self::fields()->map(fn($f) => FormFields::data($f))->values() : [],
            'requests' => $User
                ? AccountRequest::with('order')->where('user_id', $User->id)->orderByDesc('id')->limit(50)->get()->map(fn($r) => self::data($r, false))->values()
                : [],
        ];
    }

    /*
     * The customer sends the form: {fields: {key: value | file}} (multipart when there are files).
     * @return array{0: ?AccountRequest, 1: ?array} [request, error = [message, code, status, data]]
     */
    public function submit($User, array $Input): array
    {
        if (!self::settings()['open']) {
            return [null, ['ثبت درخواست اکانت فعلا بسته است.', 'closed', 403, []]];
        }
        $Max = (int)config('account_requests.max_pending', 3);
        if (AccountRequest::where('user_id', $User->id)->where('status', 'pending')->count() >= $Max) {
            return [null, ['شما ' . $Max . ' درخواست در حال بررسی دارید؛ لطفا صبر کنید تا جواب بدهیم.', 'too_many', 429, []]];
        }
        [$Answers, $Files, $Error] = FormFields::answers(self::fields(), is_array($Input['fields'] ?? null) ? $Input['fields'] : []);
        if ($Error) {
            return [null, [$Error[0], 'fields_invalid', 422, ['field' => $Error[1]]]];
        }
        $By = collect($Answers)->keyBy('key');
        $Request = AccountRequest::create([
            'user_id' => $User->id,
            'status' => 'pending',
            'site_name' => mb_substr((string)($By['site_name']['value'] ?? ''), 0, 190),
            'site_url' => mb_substr((string)($By['site_url']['value'] ?? ''), 0, 190) ?: null,
            'answers' => $Answers,
        ]);
        if ($Files) {
            $Request->answers = FormFields::storeFiles($Answers, $Files, (string)config('account_requests.disk', 'local'), self::folder($Request->id));
            $Request->save();
        }
        $this->telegram("🔎 درخواست اکانت #$Request->id\n👤 " . ($User->name ?: $User->mobile) . "\n🌐 " . $Request->site_name . ($Request->site_url ? " ($Request->site_url)" : ''));
        return [$Request, null];
    }

    /** The customer withdraws a request nobody has answered yet. */
    public function withdraw($User, int $Id): bool
    {
        return AccountRequest::where('user_id', $User->id)->where('id', $Id)->where('status', 'pending')
            ->update(['status' => 'cancelled', 'updated_at' => now()]) > 0;
    }

    /* ---------- Admin ---------- */

    /**
     * The quote: a custom order (type 9) waiting for the customer's payment.
     * $Line: {title, note?, currency: toman|usd, amount, price, rate?} — priced by OrderCharges::price().
     */
    public function quote(AccountRequest $Request, array $Line, string $Note, int $AdminId, string $AdminName): Order
    {
        $User = OrderUser::findOrFail($Request->user_id);
        $Order = DB::transaction(function () use ($Request, $Line, $Note, $AdminId, $AdminName, $User) {
            $Order = app(OrderCharges::class)->make($User, [$Line], false, $AdminId, $AdminName, 'درخواست اکانت #' . $Request->id);
            $Request->update(['status' => 'quoted', 'order_id' => $Order->id, 'admin_note' => $Note ?: null, 'reviewed_by' => $AdminId, 'reviewed_at' => now()]);
            return $Order;
        });
        OrderCharges::sms($Order, 'در انتظار پرداخت');
        return $Order;
    }

    public function reject(AccountRequest $Request, string $Note, int $AdminId): void
    {
        $Request->update(['status' => 'rejected', 'admin_note' => $Note ?: null, 'reviewed_by' => $AdminId, 'reviewed_at' => now()]);
        app(Sms::class)->notifyUser($Request->user_id, 'account_request', ['STATUS' => 'رد شد']);
    }

    /** Requests waiting for support's answer (admin sidebar). */
    public static function pendingCount(): int
    {
        return AccountRequest::where('status', 'pending')->count();
    }

    /* ---------- Shown ---------- */

    /** pending | quoted | paid | rejected | cancelled: a quote follows its order. */
    public static function shownStatus(AccountRequest $R): string
    {
        if ($R->status !== 'quoted' || !$R->order) {
            return $R->status;
        }
        if (in_array((string)$R->order->status, self::CLOSED_ORDER, true)) {
            return 'cancelled';
        }
        return $R->order->pay_status === 'Complete' ? 'paid' : 'quoted';
    }

    /** A request as the admin or its own customer sees it. The customer never gets file paths. */
    public static function data(AccountRequest $R, bool $Admin): array
    {
        $Order = $R->order;
        return [
            'id' => $R->id,
            'status' => self::shownStatus($R),
            'site_name' => $R->site_name,
            'site_url' => $R->site_url,
            'note' => $R->admin_note,
            'answers' => FormFields::shown((array)$R->answers, $Admin ? fn($Key) => route('admin.api.account-request-file', [$R->id, $Key]) : null),
            'order' => $Order ? ['id' => $Order->id, 'price' => (int)$Order->price, 'title' => (string)$Order->service] : null,
            'created_at' => optional($R->created_at)->toIso8601String(),
            'reviewed_at' => optional($R->reviewed_at)->toIso8601String(),
        ];
    }

    private function telegram(string $Text): void
    {
        try {
            SendTelegramMessage($Text, -1001509696194);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
