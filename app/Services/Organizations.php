<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrganizationField;
use App\Models\OrganizationRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Organisation accounts.
 *
 *   1. The customer fills in the form in /account (organization_fields: the five company fields + the admin's own
 *      questions) → an organization_requests row, status pending. Files go to a private disk.
 *   2. An admin (React admin › حساب‌های سازمانی) approves it, choosing a percent per product family for that
 *      company, or rejects it with a note. Approved: users.type = organization, users.org_discounts =
 *      {family: percent} (e.g. company 1: subscriptions 10, verification 5; company 2: subscriptions 15,
 *      services 20, google_voice 3). A family not listed gets no discount.
 *   3. apply() lowers a price by the company's percent for its family, where each family prices a purchase:
 *      VirtualServiceOrders, AccountOrders, VerificationOrders specs, the Apple ID spec and the Google Voice number
 *      check (StorefrontAccountController). The catalog cache is the same for everyone, so the storefront shows the
 *      lowered price itself with the same formula (cut()). The order keeps discount_code = organization and
 *      discount_price = Toman taken off.
 *
 * Virtual numbers and Google Voice pre-orders are never discounted (live provider prices / pre-orders are filled
 * later with any number up to the price paid).
 *
 * Official invoice (فاکتور رسمی): the form's built-in yes/no question official_invoice. Yes makes the tax code
 * required; on approval the company gets users.org_vat (the admin's percent, default settings › vat = 12) and
 * apply() adds that VAT to every purchase after the discount (orders.vat_percent / vat_price keep it for the
 * invoice). Admin orders (OrderCharges::make) get a VAT charge too. No: tax code optional, no VAT.
 */
class Organizations
{
    public const USER_TYPE = 'organization';
    public const CODE = 'organization';
    public const STATUSES = ['pending', 'approved', 'rejected', 'cancelled'];
    public const FIELD_TYPES = ['text', 'email', 'phone', 'textarea', 'number', 'select', 'yesno', 'date', 'file'];
    public const BUILTIN = ['official_invoice', 'company_name', 'company_email', 'company_phone', 'company_address', 'tax_code'];
    public const DEFAULT_VAT = 12;
    // Product families a company can get a percent on (users.org_discounts keys).
    public const FAMILIES = [
        'subscriptions' => 'اشتراک‌ها و اکانت‌ها',
        'services' => 'خدمات شبکه‌های اجتماعی',
        'verification' => 'وریفای',
        'apple_id' => 'اپل آیدی',
        'google_voice' => 'گوگل ویس',
        'telegram' => 'تلگرام (استارز و پریمیوم)',
    ];
    /** Telegram Premium's product slug (admin › accounts): its plans take the 'telegram' percent, not 'subscriptions'. */
    public const TELEGRAM_PREMIUM = 'telegram-premium';
    public const ACCEPT = [
        'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heic'],
        'pdf' => ['application/pdf' => 'pdf'],
    ];

    private static ?array $settings = null;

    /* ---------- Settings ---------- */

    /**
     * @return array{open: bool, defaults: array<string, float>, vat: float} defaults: the percents an approval starts
     * from; vat: the VAT percent an official-invoice company starts with.
     */
    public static function settings(): array
    {
        if (self::$settings === null) {
            $Raw = json_decode((string)optional(Setting::where('name', 'organization')->first())->key, true) ?: [];
            self::$settings = [
                'open' => (bool)($Raw['open'] ?? true),
                'defaults' => self::discounts($Raw['defaults'] ?? []),
                'vat' => array_key_exists('vat', $Raw) ? self::vatPercent($Raw['vat']) : (float)self::DEFAULT_VAT,
            ];
        }
        return self::$settings;
    }

    public static function saveSettings(array $Input): array
    {
        $Data = [
            'open' => (bool)($Input['open'] ?? true),
            'defaults' => (object)self::discounts($Input['defaults'] ?? []),
            'vat' => array_key_exists('vat', $Input) ? self::vatPercent($Input['vat']) : self::settings()['vat'],
        ];
        $Row = Setting::where('name', 'organization')->first() ?? new Setting();
        $Row->name = 'organization';
        $Row->key = json_encode($Data);
        $Row->save();
        self::$settings = null;
        return self::settings();
    }

    /** A percent from the admin, kept between 0 and config max_discount with two decimals. */
    public static function percent($Value): float
    {
        $N = (float)str_replace(',', '.', convertPersianNumbersToEnglish((string)$Value));
        return round(max(0, min((float)config('organization.max_discount', 50), $N)), 2);
    }

    /** A VAT percent from the admin, 0–100 with two decimals. */
    public static function vatPercent($Value): float
    {
        $N = (float)str_replace(['٫', '،', ','], '.', convertPersianNumbersToEnglish(trim((string)$Value)));
        return round(max(0, min(100, $N)), 2);
    }

    /** 12 → "۱۲", 9.5 → "۹٫۵" (charge titles). */
    public static function percentText(float $Percent): string
    {
        $T = rtrim(rtrim(number_format($Percent, 2, '.', ''), '0'), '.');
        return strtr($T, ['.' => '٫', '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']);
    }

    /** This company's VAT percent; 0 for personal accounts and companies without an official invoice. */
    public static function vatOf($User): float
    {
        return self::isOrganization($User) && $User->org_vat !== null ? self::vatPercent($User->org_vat) : 0.0;
    }

    /* ---------- Discount ---------- */

    public static function isOrganization($User): bool
    {
        return $User && (string)$User->type === self::USER_TYPE;
    }

    /** {family: percent} cleaned: known families only, each 0–max, zeros left out. */
    public static function discounts($Raw): array
    {
        $Raw = is_string($Raw) ? (json_decode($Raw, true) ?: []) : (array)$Raw;
        $Out = [];
        foreach (array_keys(self::FAMILIES) as $Family) {
            $P = self::percent($Raw[$Family] ?? 0);
            if ($P > 0) {
                $Out[$Family] = $P;
            }
        }
        return $Out;
    }

    /** This company's percents per family; [] for personal accounts. */
    public static function discountsOf($User): array
    {
        return self::isOrganization($User) ? self::discounts($User->org_discounts) : [];
    }

    /** The percent this user gets on a product family; 0 for personal accounts and families not given. */
    public static function discountFor($User, string $Family): float
    {
        return self::discountsOf($User)[$Family] ?? 0.0;
    }

    /**
     * What the storefront needs to show an organisation its prices: {discounts: {family: percent}} or null. Sent with
     * /storefront/me, account/me and the panel, so pages can lower the cached prices with cut().
     */
    public static function badge($User): ?array
    {
        return self::isOrganization($User) ? ['discounts' => (object)self::discountsOf($User), 'vat' => self::vatOf($User)] : null;
    }

    /** An approved company's name and tax code, for invoices; null for personal accounts. */
    public static function company($User): ?array
    {
        if (!self::isOrganization($User)) {
            return null;
        }
        $R = OrganizationRequest::where('user_id', $User->id)->where('status', 'approved')->orderByDesc('id')->first();
        return $R ? ['name' => (string)$R->company_name, 'tax_code' => (string)$R->tax_code, 'official' => self::vatOf($User) > 0] : null;
    }

    /** Toman taken off a price. The storefront rounds the same way (Math.round). */
    public static function cut(int $Price, float $Percent): int
    {
        return $Percent > 0 ? (int)round($Price * $Percent / 100) : 0;
    }

    /** Toman of VAT on a price. The storefront rounds the same way (Math.round). */
    public static function vat(int $Price, float $Percent): int
    {
        return $Percent > 0 ? (int)round($Price * $Percent / 100) : 0;
    }

    /*
     * A priced spec (['price' => int, …]) with the signed-in organisation's discount taken off, then its VAT added
     * (official-invoice companies); adds price_before, org_discount, vat, vat_percent. `price` is what is charged.
     * Unchanged for everyone else.
     */
    public static function apply(array $Spec, string $Family, $User = null): array
    {
        $User ??= Auth::guard('user')->user();
        $Cut = self::cut((int)$Spec['price'], self::discountFor($User, $Family));
        if ($Cut > 0) {
            $Spec['price_before'] = (int)$Spec['price'];
            $Spec['org_discount'] = $Cut;
            $Spec['price'] = (int)$Spec['price'] - $Cut;
        }
        $Percent = self::vatOf($User);
        $Vat = self::vat((int)$Spec['price'], $Percent);
        if ($Vat > 0) {
            $Spec['vat_percent'] = $Percent;
            $Spec['vat'] = $Vat;
            $Spec['price'] = (int)$Spec['price'] + $Vat;
        }
        return $Spec;
    }

    /** Marks an order made from a discounted / VAT-carrying spec (admin › order and the invoice show them). */
    public static function stamp(Order $Order, array $Spec): void
    {
        $Dirty = false;
        if (($Spec['org_discount'] ?? 0) > 0) {
            $Order->discount_code = self::CODE;
            $Order->discount_price = (string)$Spec['org_discount'];
            $Dirty = true;
        }
        if (($Spec['vat'] ?? 0) > 0) {
            $Order->vat_percent = $Spec['vat_percent'];
            $Order->vat_price = (int)$Spec['vat'];
            $Dirty = true;
        }
        if ($Dirty) {
            $Order->save();
        }
    }

    /* ---------- Form ---------- */

    /** @return \Illuminate\Support\Collection<OrganizationField> */
    public static function fields(bool $OnlyEnabled = true)
    {
        return OrganizationField::query()->when($OnlyEnabled, fn($q) => $q->where('enabled', true))->orderBy('sort')->orderBy('id')->get();
    }

    /** One field as the storefront form and the admin show it. */
    public static function fieldData(OrganizationField $F, bool $Admin = false): array
    {
        $Data = [
            'key' => $F->key,
            'title' => $F->title,
            'title_en' => $F->title_en ?: null,
            'type' => $F->type,
            'required' => $F->required,
            'hint' => $F->hint ?: null,
            'hint_en' => $F->hint_en ?: null,
            'options' => $F->type === 'select' ? array_values((array)$F->options) : [],
            'accept' => $F->type === 'file' ? (array)($F->accept ?: ['image']) : [],
            'max_mb' => $F->type === 'file' ? (int)($F->max_mb ?: 5) : null,
        ];
        if ($Admin) {
            $Data += ['id' => $F->id, 'enabled' => $F->enabled, 'builtin' => $F->builtin, 'sort' => $F->sort];
        }
        return $Data;
    }

    /*
     * Replaces the form with the admin's list (in order). Built-in fields can be switched off or made optional and
     * retitled, never deleted or retyped. Answers already sent keep their own snapshot, so edits don't change them.
     * @return array{0: bool, 1: ?string} [ok, error]
     */
    public static function saveFields(array $Rows): array
    {
        $Clean = [];
        $Keys = [];
        foreach (array_values($Rows) as $I => $R) {
            $Key = trim((string)($R['key'] ?? ''));
            $Builtin = in_array($Key, self::BUILTIN, true);
            $Type = $Builtin ? null : (string)($R['type'] ?? 'text');
            $Title = trim((string)($R['title'] ?? ''));
            if ($Key === '') {
                $Key = 'q_' . Str::lower(Str::random(8));
            }
            if (!preg_match('/^[a-z][a-z0-9_]{0,49}$/', $Key) || isset($Keys[$Key])) {
                return [false, 'کلید «' . $Key . '» معتبر یا یکتا نیست.'];
            }
            if ($Title === '') {
                return [false, 'عنوان همه سوال‌ها را بنویسید.'];
            }
            if (!$Builtin && !in_array($Type, self::FIELD_TYPES, true)) {
                return [false, 'نوع سوال «' . $Title . '» معتبر نیست.'];
            }
            $Options = array_values(array_unique(array_filter(array_map(fn($o) => mb_substr(trim((string)$o), 0, 100), (array)($R['options'] ?? [])), 'strlen')));
            if ($Type === 'select' && count($Options) < 2) {
                return [false, 'سوال «' . $Title . '» دست کم دو گزینه لازم دارد.'];
            }
            $Keys[$Key] = true;
            $Clean[] = [
                'key' => $Key, 'builtin' => $Builtin, 'type' => $Type,
                'title' => mb_substr($Title, 0, 190),
                'title_en' => mb_substr(trim((string)($R['title_en'] ?? '')), 0, 190) ?: null,
                'required' => (bool)($R['required'] ?? true),
                'enabled' => (bool)($R['enabled'] ?? true),
                'hint' => mb_substr(trim((string)($R['hint'] ?? '')), 0, 190) ?: null,
                'hint_en' => mb_substr(trim((string)($R['hint_en'] ?? '')), 0, 190) ?: null,
                'options' => $Type === 'select' ? $Options : null,
                'accept' => $Type === 'file' ? (array_values(array_intersect(array_keys(self::ACCEPT), (array)($R['accept'] ?? []))) ?: ['image']) : null,
                'max_mb' => $Type === 'file' ? max(1, min((int)config('organization.max_mb', 10), (int)($R['max_mb'] ?? 5))) : null,
                'sort' => $I + 1,
            ];
        }
        foreach (self::BUILTIN as $Key) {
            if (!isset($Keys[$Key])) {
                return [false, 'فیلدهای اصلی شرکت را نمی‌توان حذف کرد؛ می‌توانید خاموششان کنید.'];
            }
        }

        DB::transaction(function () use ($Clean) {
            OrganizationField::whereNotIn('key', array_column($Clean, 'key'))->where('builtin', false)->delete();
            foreach ($Clean as $C) {
                $Row = OrganizationField::firstOrNew(['key' => $C['key']]);
                $Builtin = $C['builtin'];
                unset($C['builtin']);
                if ($Builtin) {
                    unset($C['type']); // keeps its own
                }
                $Row->fill($C);
                $Row->builtin = $Builtin;
                $Row->save();
            }
        });
        return [true, null];
    }

    /* ---------- Customer ---------- */

    public static function latest($User): ?OrganizationRequest
    {
        return OrganizationRequest::where('user_id', $User->id)->orderByDesc('id')->first();
    }

    /** What /account shows: where the account stands, the form when it can be sent, and the last request. */
    public static function state($User): array
    {
        $Latest = self::latest($User);
        $Settings = self::settings();
        $Status = match (true) {
            self::isOrganization($User) => 'approved',
            $Latest && $Latest->status === 'pending' => 'pending',
            $Latest && in_array($Latest->status, ['rejected', 'cancelled'], true) => $Latest->status,
            default => 'personal',
        };
        $CanApply = $Settings['open'] && in_array($Status, ['personal', 'rejected', 'cancelled'], true);
        return [
            'status' => $Status,
            'discounts' => (object)self::discountsOf($User),
            'open' => $Settings['open'],
            // VAT: the company's own percent once approved, else the percent the form's official-invoice note shows.
            'vat' => self::isOrganization($User) ? self::vatOf($User) : $Settings['vat'],
            'can_apply' => $CanApply,
            'fields' => $CanApply ? self::fields()->map(fn($f) => self::fieldData($f))->values() : [],
            'request' => $Latest ? self::requestData($Latest, false) : null,
        ];
    }

    /*
     * The customer sends the form: {fields: {key: value | file}} (multipart when there are files).
     * @return array{0: ?OrganizationRequest, 1: ?array} [request, error = [message, code, status, data]]
     */
    public function submit($User, array $Input): array
    {
        $State = self::state($User);
        if (!$State['can_apply']) {
            return [null, match ($State['status']) {
                'pending' => ['درخواست قبلی شما در حال بررسی است.', 'pending', 409, []],
                'approved' => ['حساب شما همین حالا سازمانی است.', 'already', 409, []],
                default => ['ثبت درخواست حساب سازمانی فعلا بسته است.', 'closed', 403, []],
            }];
        }

        $Given = is_array($Input['fields'] ?? null) ? $Input['fields'] : [];
        $Answers = [];
        $Files = [];
        $Fields = self::fields();
        // Official invoice: yes makes the tax code required, no makes it optional. Off in admin: the tax code keeps
        // its own setting and no VAT is asked for.
        $Asked = $Fields->contains('key', 'official_invoice');
        $Official = $Asked && is_scalar($Given['official_invoice'] ?? null) && (string)$Given['official_invoice'] === 'yes';
        foreach ($Fields as $F) {
            if ($F->key === 'tax_code' && $Asked) {
                $F->required = $Official;
            }
            $Raw = $Given[$F->key] ?? null;
            $Bad = fn(string $Why = '') => [null, [$Why ?: 'لطفا «' . $F->title . '» را درست وارد کنید.', 'fields_invalid', 422, ['field' => $F->key]]];

            if ($F->type === 'file') {
                $File = $Raw instanceof UploadedFile ? $Raw : null;
                if (!$File) {
                    if ($F->required) {
                        return $Bad('لطفا فایل «' . $F->title . '» را بفرستید.');
                    }
                    continue;
                }
                $Mimes = array_merge(...array_map(fn($a) => self::ACCEPT[$a], (array)($F->accept ?: ['image'])));
                $Mime = $File->isValid() ? (string)$File->getMimeType() : '';
                if (!isset($Mimes[$Mime])) {
                    return $Bad('فرمت فایل «' . $F->title . '» پذیرفته نیست.');
                }
                if ($File->getSize() > (int)($F->max_mb ?: 5) * 1024 * 1024) {
                    return $Bad('حجم فایل «' . $F->title . '» باید حداکثر ' . (int)($F->max_mb ?: 5) . ' مگابایت باشد.');
                }
                $Files[$F->key] = ['file' => $File, 'ext' => $Mimes[$Mime]];
                $Base = trim((string)preg_replace('/[^\p{L}\p{N} ._()-]+/u', '', pathinfo($File->getClientOriginalName(), PATHINFO_FILENAME))) ?: 'file';
                $Answers[] = ['key' => $F->key, 'title' => $F->title, 'type' => 'file', 'value' => mb_substr($Base, 0, 80) . '.' . $Mimes[$Mime], 'mime' => $Mime, 'size' => (int)$File->getSize()];
                continue;
            }

            $Value = is_scalar($Raw) ? trim(convertPersianNumbersToEnglish((string)$Raw)) : '';
            if ($Value === '') {
                if ($F->required) {
                    return $Bad('لطفا «' . $F->title . '» را وارد کنید.');
                }
                continue;
            }
            $Ok = match ($F->type) {
                'email' => filter_var($Value, FILTER_VALIDATE_EMAIL) && strlen($Value) <= 150,
                'phone' => (bool)preg_match('/^\+?[0-9][0-9 \-]{4,19}$/', $Value),
                'number' => is_numeric($Value) && strlen($Value) <= 20,
                'select' => in_array($Value, (array)$F->options, true),
                'yesno' => in_array($Value, ['yes', 'no'], true),
                'date' => (bool)preg_match('~^\d{4}[/\-]\d{1,2}[/\-]\d{1,2}$~', $Value),
                'textarea' => mb_strlen($Value) <= 2000,
                default => mb_strlen($Value) <= 255,
            };
            if ($Ok && $F->key === 'tax_code') {
                $Ok = (bool)preg_match('/^\d{10,14}$/', $Value);
            }
            if (!$Ok) {
                return $Bad();
            }
            $Answers[] = ['key' => $F->key, 'title' => $F->title, 'type' => $F->type, 'value' => $Value];
        }

        $Request = DB::transaction(function () use ($User, $Answers, $Asked, $Official) {
            $By = collect($Answers)->keyBy('key');
            return OrganizationRequest::create([
                'user_id' => $User->id,
                'status' => 'pending',
                'company_name' => $By['company_name']['value'] ?? null,
                'company_email' => $By['company_email']['value'] ?? null,
                'company_phone' => $By['company_phone']['value'] ?? null,
                'company_address' => $By['company_address']['value'] ?? null,
                'tax_code' => $By['tax_code']['value'] ?? null,
                'official_invoice' => $Asked ? $Official : null,
                'answers' => $Answers,
            ]);
        });

        if ($Files) {
            $Disk = Storage::disk(config('organization.disk', 'local'));
            $Rows = $Request->answers;
            foreach ($Rows as &$Row) {
                if (isset($Files[$Row['key']])) {
                    $F = $Files[$Row['key']];
                    $Path = self::folder($Request->id) . '/' . $Row['key'] . '-' . Str::random(20) . '.' . $F['ext'];
                    $Disk->put($Path, file_get_contents($F['file']->getRealPath()));
                    $Row['file'] = $Path;
                }
            }
            unset($Row);
            $Request->answers = $Rows;
            $Request->save();
        }

        $this->telegram("🏢 درخواست حساب سازمانی #$Request->id\n👤 " . ($User->name ?: $User->mobile) . "\n🏷 " . ($Request->company_name ?: '—'));
        return [$Request, null];
    }

    public static function folder(int $RequestId): string
    {
        return 'organization/' . $RequestId;
    }

    /* ---------- Admin ---------- */

    /** $Discounts: {family: percent}. $Vat: the company's VAT percent, used only when it asked for an official invoice. */
    public function approve(OrganizationRequest $Request, array $Discounts, int $AdminId, ?float $Vat = null): void
    {
        DB::transaction(function () use ($Request, $Discounts, $AdminId, $Vat) {
            $Request->update(['status' => 'approved', 'admin_note' => null, 'reviewed_by' => $AdminId, 'reviewed_at' => now()]);
            // An older approved request of the same user is superseded.
            OrganizationRequest::where('user_id', $Request->user_id)->where('id', '!=', $Request->id)->where('status', 'approved')->update(['status' => 'cancelled']);
            User::where('id', $Request->user_id)->update([
                'type' => self::USER_TYPE,
                'org_discounts' => json_encode((object)self::discounts($Discounts)),
                'org_vat' => $Request->official_invoice ? self::vatPercent($Vat ?? self::settings()['vat']) : null,
            ]);
        });
        $this->sms($Request->user_id, 'تایید شد');
    }

    public function reject(OrganizationRequest $Request, string $Note, int $AdminId): void
    {
        $Request->update(['status' => 'rejected', 'admin_note' => $Note ?: null, 'reviewed_by' => $AdminId, 'reviewed_at' => now()]);
        $this->sms($Request->user_id, 'رد شد');
    }

    /** Back to a personal account. Orders already bought keep their discount. */
    public function cancel(User $User, string $Note, int $AdminId): void
    {
        DB::transaction(function () use ($User, $Note, $AdminId) {
            OrganizationRequest::where('user_id', $User->id)->where('status', 'approved')
                ->update(['status' => 'cancelled', 'admin_note' => $Note ?: null, 'reviewed_by' => $AdminId, 'reviewed_at' => now()]);
            User::where('id', $User->id)->update(['type' => 'user', 'org_discounts' => null, 'org_vat' => null]);
        });
    }

    /** Replaces the company's percents ({family: percent}); from the next purchase on. */
    public function setDiscounts(User $User, array $Discounts): void
    {
        User::where('id', $User->id)->update(['org_discounts' => json_encode((object)self::discounts($Discounts))]);
    }

    /** The company's VAT percent from the next purchase on; null or 0 = no official invoice, no VAT. */
    public function setVat(User $User, $Percent): void
    {
        $P = $Percent === null || $Percent === '' ? 0 : self::vatPercent($Percent);
        User::where('id', $User->id)->update(['org_vat' => $P > 0 ? $P : null]);
    }

    /** A request as the admin ($Admin) or its own customer sees it. The customer never gets file paths. */
    public static function requestData(OrganizationRequest $R, bool $Admin): array
    {
        $Answers = array_map(function ($A) use ($Admin, $R) {
            $Row = ['key' => $A['key'], 'title' => $A['title'], 'type' => $A['type'], 'value' => $A['value']];
            if ($A['type'] === 'file' && $Admin) {
                $Row['url'] = !empty($A['file']) ? route('admin.api.organization-file', [$R->id, $A['key']]) : null;
                $Row['mime'] = $A['mime'] ?? null;
            }
            return $Row;
        }, (array)$R->answers);

        return [
            'id' => $R->id,
            'status' => $R->status,
            'company_name' => $R->company_name,
            'official_invoice' => $R->official_invoice === null ? null : (bool)$R->official_invoice,
            'note' => $R->admin_note,
            'answers' => $Answers,
            'created_at' => optional($R->created_at)->toIso8601String(),
            'reviewed_at' => optional($R->reviewed_at)->toIso8601String(),
        ];
    }

    /* ---------- Notices ---------- */

    private function sms(int $UserId, string $Status): void
    {
        $Template = config('organization.sms_template');
        if (!$Template) {
            return;
        }
        try {
            $User = User::find($UserId);
            if ($User && $User->mobile) {
                sendSmsWithSmsir($User->mobile, ['STATUS' => $Status], (int)$Template);
            }
        } catch (\Throwable $e) {
            report($e);
        }
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
