<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/*
 * SMS through SMS.ir (REST v1, https://api.sms.ir/v1, key SMSIR_API_KEY). Managed in admin › پیامک
 * (Admin\SmsApiController, resources/admin/pages/sms/SmsPage.tsx).
 *
 * Patterns: every automatic SMS is an SMS.ir "verify" template. PATTERNS lists them with the template id the code
 * used before this page existed (`legacy`). The callers still pass those old ids to sendSmsWithSmsir(); the helper
 * looks the id up here and sends with the id and on/off switch saved in the `settings` row "sms" instead. So
 * changing an id in the panel needs no code change, and nothing changes until an admin saves something.
 * Patterns with no id (ticket answered, new order, organisation result) send nothing until one is entered.
 *
 * Bulk SMS: App\Models\SmsCampaign rows, sent 100 numbers per request by App\Jobs\SendSmsCampaign.
 *
 * DEV_FAKE_PROVIDERS: nothing is sent; sends are logged and credit/lines are made up, so the page can be tried.
 */
class Sms
{
    private const API = 'https://api.sms.ir/v1';
    public const BATCH = 100; // SMS.ir's limit per bulk request

    /*
     * key => name, where it is sent, template variables, old hardcoded id, whether it can be switched off.
     * Sign-in codes can't be switched off: without them nobody can sign in or sign up.
     */
    public const PATTERNS = [
        'login_code' => ['label' => 'کد ورود', 'where' => 'ورود با کد در سایت و صفحه شماره مجازی', 'vars' => ['CODE'], 'legacy' => 927153, 'required' => true],
        'verify_code' => ['label' => 'تأیید شماره (ثبت‌نام)', 'where' => 'ثبت‌نام و تأیید شماره موبایل', 'vars' => ['CODE', 'CODE2'], 'legacy' => 880532, 'required' => true],
        'new_password' => ['label' => 'رمز تازه (فراموشی رمز)', 'where' => 'بازیابی رمز عبور در سایت قدیم', 'vars' => ['CODE'], 'legacy' => 166193, 'required' => true],
        'order_status' => ['label' => 'تغییر وضعیت سفارش', 'where' => 'همه سفارش‌ها، اپل آیدی، آی‌پی ثابت و هزینه‌های اضافه', 'vars' => ['NUM', 'STATUS'], 'legacy' => 138554, 'required' => false],
        'ip_ready' => ['label' => 'آی‌پی ثابت آماده شد', 'where' => 'تحویل آی‌پی ثابت', 'vars' => ['NUM'], 'legacy' => 507031, 'required' => false],
        'organization' => ['label' => 'نتیجه حساب سازمانی', 'where' => 'تأیید یا رد درخواست حساب سازمانی', 'vars' => ['STATUS'], 'legacy' => null, 'required' => false],
        'ticket_answer' => ['label' => 'پاسخ تیکت', 'where' => 'وقتی پشتیبانی به تیکت کاربر جواب می‌دهد', 'vars' => ['NUM'], 'legacy' => null, 'required' => false, 'new' => true],
        'new_order' => ['label' => 'ثبت سفارش تازه', 'where' => 'بعد از پرداخت موفق سفارش (درگاه یا کیف پول)', 'vars' => ['NUM'], 'legacy' => null, 'required' => false, 'new' => true],
    ];

    // Sample values for "send test" (SMS.ir rejects values over 25 characters).
    private const SAMPLES = ['CODE' => '12345', 'CODE2' => '#12345', 'NUM' => '1234', 'STATUS' => 'تکمیل شده'];

    // SMS.ir envelope status codes worth explaining to an admin.
    private const ERRORS = [
        0 => 'خطای داخلی SMS.ir؛ کمی بعد دوباره تلاش کنید.',
        10 => 'کلید API نامعتبر است (SMSIR_API_KEY).',
        11 => 'کلید API غیرفعال است.',
        12 => 'کلید API به IP دیگری محدود شده است؛ IP سرور را در پنل SMS.ir مجاز کنید.',
        13 => 'حساب SMS.ir غیرفعال است.',
        14 => 'حساب SMS.ir معلق شده است.',
        20 => 'تعداد درخواست‌ها بیش از حد مجاز است.',
        101 => 'شماره خط نامعتبر است.',
        102 => 'اعتبار SMS.ir کافی نیست.',
        103 => 'متن پیامک خالی است.',
        104 => 'شماره موبایل نامعتبر است.',
        113 => 'الگویی با این شناسه در SMS.ir پیدا نشد.',
        114 => 'مقدار یکی از متغیرها بیش از ۲۵ نویسه است.',
        115 => 'شماره در لیست سیاه است.',
        117 => 'متن پیامک هنوز تأیید نشده است.',
        123 => 'خط ارسال باید فعال شود.',
    ];

    public function settings(): array
    {
        $Saved = json_decode((string)optional(Setting::where('name', 'sms')->first())->key, true) ?: [];
        $Patterns = [];
        foreach (self::PATTERNS as $Key => $P) {
            $S = $Saved['patterns'][$Key] ?? [];
            $Default = $Key === 'organization' ? ((int)config('organization.sms_template') ?: null) : $P['legacy'];
            $Patterns[$Key] = [
                'template' => array_key_exists('template', $S) ? ($S['template'] ? (int)$S['template'] : null) : $Default,
                'enabled' => $P['required'] ? true : (bool)($S['enabled'] ?? ($Default !== null)),
                'note' => (string)($S['note'] ?? ''),
            ];
        }
        return [
            'patterns' => $Patterns,
            'line' => isset($Saved['line']) ? (string)$Saved['line'] : null,
            'low_credit' => (int)($Saved['low_credit'] ?? 500),
        ];
    }

    public function save(array $Data): void
    {
        $Row = Setting::where('name', 'sms')->first() ?? (new Setting())->forceFill(['name' => 'sms']);
        $Row->key = json_encode($Data, JSON_UNESCAPED_UNICODE);
        $Row->save();
        Cache::forget('sms:settings');
    }

    /* Settings for the send path, cached briefly so every login code doesn't read the database. */
    private function cachedSettings(): array
    {
        return Cache::remember('sms:settings', 300, fn () => $this->settings());
    }

    /* The pattern a caller's old hardcoded id stands for (see the class comment). */
    public function keyForLegacy($Template): ?string
    {
        $Template = (int)$Template;
        foreach (self::PATTERNS as $Key => $P) {
            if ($P['legacy'] === $Template) return $Key;
        }
        if ($Template && $Template === (int)config('organization.sms_template')) return 'organization';
        return null;
    }

    /*
     * Sends one pattern SMS. $Template is a pattern key or a caller's old id; an id this class doesn't know is sent
     * as it is. Returns SMS.ir's raw answer (as before), or null when nothing was sent.
     */
    public function sendPattern(string $Mobile, array $Data, $Template): ?string
    {
        $Key = isset(self::PATTERNS[$Template]) ? $Template : $this->keyForLegacy($Template);
        if ($Key) {
            $P = $this->cachedSettings()['patterns'][$Key];
            if (!$P['enabled'] || !$P['template']) return null;
            $Template = $P['template'];
        }
        if (!(int)$Template) return null;
        if (DevFakeProviders()) { // local development: log instead of texting
            Log::info("[dev] SMS template $Template for $Mobile: " . json_encode($Data, JSON_UNESCAPED_UNICODE));
            return null;
        }
        $Params = [];
        foreach ($Data as $Name => $Value) {
            $Params[] = ['name' => (string)$Name, 'value' => (string)$Value];
        }
        try {
            $Res = $this->http()->post(self::API . '/send/verify', [
                'mobile' => (string)$Mobile,
                'templateId' => (int)$Template,
                'parameters' => $Params,
            ]);
            if (($Res->json('status') ?? 0) != 1) {
                Log::warning("SMS.ir verify failed (template $Template): " . $Res->body());
            }
            return $Res->body();
        } catch (\Throwable $e) {
            Log::warning("SMS.ir verify error (template $Template): " . $e->getMessage());
            return null;
        }
    }

    /* The user's SMS for a pattern, by user id. Never throws: a failed SMS must not break what triggered it. */
    public function notifyUser($UserId, string $Key, array $Data): void
    {
        try {
            $Mobile = optional(User::find($UserId))->mobile;
            if ($Mobile) $this->sendPattern((string)$Mobile, $Data, $Key);
        } catch (\Throwable $e) {
            Log::warning("SMS $Key for user $UserId failed: " . $e->getMessage());
        }
    }

    /* "Send test" from the panel: the pattern with sample values, ignoring its on/off switch. */
    public function test(string $Key, int $Template, string $Mobile): array
    {
        $Data = [];
        foreach (self::PATTERNS[$Key]['vars'] as $Var) $Data[$Var] = self::SAMPLES[$Var] ?? '1';
        if (DevFakeProviders()) {
            Log::info("[dev] SMS test template $Template for $Mobile: " . json_encode($Data, JSON_UNESCAPED_UNICODE));
            return ['ok' => true, 'message' => 'حالت توسعه: پیامک فرستاده نشد و فقط در لاگ ثبت شد.'];
        }
        $Res = $this->call('post', '/send/verify', [
            'mobile' => $Mobile,
            'templateId' => $Template,
            'parameters' => array_map(fn ($n, $v) => ['name' => $n, 'value' => $v], array_keys($Data), $Data),
        ]);
        return $Res['ok']
            ? ['ok' => true, 'message' => 'پیامک آزمایشی به ' . $Mobile . ' فرستاده شد.']
            : ['ok' => false, 'message' => $Res['error']];
    }

    /* Credit and lines for the page header. */
    public function account(): array
    {
        if (DevFakeProviders()) {
            return ['ok' => true, 'credit' => 4218.0, 'lines' => ['30007732000000'], 'error' => null, 'dev' => true];
        }
        if (!config('services.smsir.key')) {
            return ['ok' => false, 'credit' => null, 'lines' => [], 'error' => 'کلید SMSIR_API_KEY در .env تنظیم نشده است.', 'dev' => false];
        }
        $Credit = $this->call('get', '/credit');
        $Lines = $this->call('get', '/line');
        return [
            'ok' => $Credit['ok'],
            'credit' => $Credit['ok'] ? (float)$Credit['data'] : null,
            'lines' => $Lines['ok'] ? array_map('strval', (array)$Lines['data']) : [],
            'error' => $Credit['ok'] ? null : $Credit['error'],
            'dev' => false,
        ];
    }

    /*
     * One bulk request (≤100 numbers). Returns ['ok', 'accepted', 'rejected', 'cost', 'packId', 'error'].
     * SMS.ir answers one messageId per number: a positive id = accepted, 0 = blacklisted, null = invalid.
     */
    public function sendBulk(string $Line, string $Text, array $Mobiles): array
    {
        if (DevFakeProviders()) {
            Log::info('[dev] bulk SMS to ' . count($Mobiles) . " numbers from $Line: $Text");
            return ['ok' => true, 'accepted' => count($Mobiles), 'rejected' => 0, 'cost' => (float)(count($Mobiles) * self::parts($Text)), 'packId' => null, 'error' => null];
        }
        $Res = $this->call('post', '/send/bulk', ['lineNumber' => (int)$Line, 'messageText' => $Text, 'mobiles' => array_values($Mobiles)]);
        if (!$Res['ok']) {
            return ['ok' => false, 'accepted' => 0, 'rejected' => 0, 'cost' => 0.0, 'packId' => null, 'error' => $Res['error'], 'fatal' => $Res['fatal']];
        }
        $Ids = (array)($Res['data']['messageIds'] ?? []);
        $Accepted = count(array_filter($Ids, fn ($id) => (int)$id > 0));
        return [
            'ok' => true,
            'accepted' => $Accepted,
            'rejected' => count($Mobiles) - $Accepted,
            'cost' => (float)($Res['data']['cost'] ?? 0),
            'packId' => $Res['data']['packId'] ?? null,
            'error' => null,
        ];
    }

    /* GET/POST to SMS.ir with the envelope checked: ['ok', 'data', 'error', 'fatal']. */
    private function call(string $Method, string $Path, array $Body = []): array
    {
        try {
            $Res = $Method === 'get' ? $this->http()->get(self::API . $Path) : $this->http()->post(self::API . $Path, $Body);
        } catch (\Throwable $e) {
            return ['ok' => false, 'data' => null, 'error' => 'اتصال به SMS.ir برقرار نشد.', 'fatal' => false];
        }
        $Status = $Res->json('status');
        if ($Res->successful() && $Status == 1) {
            return ['ok' => true, 'data' => $Res->json('data'), 'error' => null, 'fatal' => false];
        }
        $Code = is_numeric($Status) ? (int)$Status : null;
        $Error = self::ERRORS[$Code] ?? ($Res->json('message') ?: 'خطای SMS.ir (' . $Res->status() . ')');
        if ($Res->status() === 401) $Error = self::ERRORS[10];
        // Errors a retry won't fix: bad key/account/line, no credit, text not approved.
        $Fatal = $Res->status() === 401 || in_array($Code, [10, 11, 12, 13, 14, 101, 102, 117, 123], true);
        return ['ok' => false, 'data' => null, 'error' => $Error, 'fatal' => $Fatal];
    }

    private function http()
    {
        return Http::withHeaders(['x-api-key' => (string)config('services.smsir.key'), 'Accept' => 'application/json'])
            ->asJson()->timeout(20)->connectTimeout(8);
    }

    /* Iranian mobile as SMS.ir takes it (9xxxxxxxxx), or null. Accepts 09…, +989…, 989… and Persian digits. */
    public static function normalizeMobile(?string $Raw): ?string
    {
        $Digits = preg_replace('/\D/', '', strtr((string)$Raw, ['۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9']));
        if (str_starts_with($Digits, '0098')) $Digits = substr($Digits, 4);
        elseif (str_starts_with($Digits, '98') && strlen($Digits) === 12) $Digits = substr($Digits, 2);
        elseif (str_starts_with($Digits, '0')) $Digits = substr($Digits, 1);
        return preg_match('/^9\d{9}$/', $Digits) ? $Digits : null;
    }

    /* How many SMS parts a text takes: Persian (UCS-2) 70 / 67 per part, Latin (GSM) 160 / 153. */
    public static function parts(string $Text): int
    {
        $Len = mb_strlen($Text);
        if ($Len === 0) return 0;
        $Unicode = (bool)preg_match('/[^\x00-\x7F]/', $Text);
        [$One, $Many] = $Unicode ? [70, 67] : [160, 153];
        return $Len <= $One ? 1 : (int)ceil($Len / $Many);
    }

    public const AUDIENCES = ['all', 'buyers', 'recent', 'custom'];
    private const PAID = ['Complete', 'paid with wallet'];

    /* Numbers for a bulk send, normalised and without repeats. Blocked accounts are left out. */
    public function audience(string $Type, int $Days = 30, string $Custom = ''): array
    {
        if ($Type === 'custom') {
            $List = preg_split('/[\s,،;]+/u', $Custom, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            $Q = User::query()->where('account_blocked', 0);
            if ($Type !== 'all') {
                $Q->whereIn('id', function ($Sub) use ($Type, $Days) {
                    $Sub->select('user_id')->from((new Order())->getTable())->whereIn('pay_status', self::PAID);
                    if ($Type === 'recent') $Sub->where('created_at', '>=', now()->subDays(max(1, $Days)));
                });
            }
            $List = $Q->pluck('mobile')->all();
        }
        $Out = [];
        foreach ($List as $M) {
            if ($N = self::normalizeMobile($M)) $Out[$N] = true;
        }
        return array_keys($Out);
    }

    /* Recipient counts for the bulk form. */
    public function audienceCounts(int $Days = 30): array
    {
        return [
            'all' => count($this->audience('all')),
            'buyers' => count($this->audience('buyers')),
            'recent' => count($this->audience('recent', $Days)),
        ];
    }

    public static function paidStatuses(): array
    {
        return self::PAID;
    }
}
