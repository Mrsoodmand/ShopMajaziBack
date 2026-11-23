<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/*
 * Verification products (/verify on the Next.js storefront), set up in React admin › وریفای:
 * products of type "verification" with their plans (plans.type = verification). Two kinds:
 *   ready  — we deliver a ready, already-verified account (PayPal, Binance, Wise…);
 *   verify — we verify the buyer's own account (blue tick, Meta Verified, KYC…).
 *
 * The admin chooses what the buyer must give (products.filds, the shape below), including files. Files are kept
 * on a private disk (config/verification.php) and only admins open them; the buyer and the storefront only ever
 * see their names. verification:prune-files deletes them some days after the order is finished.
 *
 * Buying works like subscriptions (AccountOrders): order type 10 with one item holding the answers and the plan,
 * a payment waiting for wallet or bank, paid through the same wallet / bank code, then delivered by support.
 *
 * products.filds for this type:
 *   [{name, title, title_en, type: text|email|phone|select|textarea|date|file, required, hint, hint_en,
 *     options: [string], accept: [image|pdf], max_mb}]
 */
class VerificationOrders
{
    public const TYPE = 10;
    public const PRODUCT_TYPE = 'verification';
    public const KINDS = ['ready', 'verify'];
    public const FIELD_TYPES = ['text', 'email', 'phone', 'select', 'textarea', 'date', 'file'];
    public const ACCEPT = [
        'image' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/heic' => 'heic', 'image/heif' => 'heic'],
        'pdf' => ['application/pdf' => 'pdf'],
    ];

    /* ---------- Fields ---------- */

    /*
     * The product's fields as the admin saved them, cleaned.
     * @return array<int, array{name: string, title: string, title_en: string, type: string, required: bool, hint: string, hint_en: string, options: string[], accept: string[], max_mb: int}>
     */
    public static function fields(Product $Product): array
    {
        $Raw = json_decode((string)$Product->filds, true);
        $Out = [];
        foreach (is_array($Raw) ? $Raw : [] as $F) {
            $Name = (string)($F['name'] ?? '');
            $Type = (string)($F['type'] ?? 'text');
            if (!preg_match('/^[A-Za-z][A-Za-z0-9_]{0,49}$/', $Name) || !in_array($Type, self::FIELD_TYPES, true) || trim((string)($F['title'] ?? '')) === '') {
                continue;
            }
            $Accept = array_values(array_intersect(array_keys(self::ACCEPT), (array)($F['accept'] ?? [])));
            $Out[] = [
                'name' => $Name,
                'title' => trim((string)$F['title']),
                'title_en' => trim((string)($F['title_en'] ?? '')),
                'type' => $Type,
                'required' => (bool)($F['required'] ?? true),
                'hint' => trim((string)($F['hint'] ?? '')),
                'hint_en' => trim((string)($F['hint_en'] ?? '')),
                'options' => $Type === 'select' ? array_values(array_filter(array_map(fn($o) => trim((string)$o), (array)($F['options'] ?? [])), 'strlen')) : [],
                'accept' => $Type === 'file' ? ($Accept ?: ['image']) : [],
                'max_mb' => $Type === 'file' ? max(1, min((int)config('verification.max_mb', 10), (int)($F['max_mb'] ?? 5))) : 0,
            ];
        }
        return $Out;
    }

    // Mime types (content-sniffed) a file field takes, mapped to the extension it is saved with.
    public static function mimes(array $Field): array
    {
        return array_merge(...array_map(fn($a) => self::ACCEPT[$a], $Field['accept'] ?: ['image']));
    }

    /* ---------- Storefront ---------- */

    // Live plans per product, in the admin's order.
    public function plansFor(Collection $Products): Collection
    {
        return Plan::whereIn('product_id', $Products->pluck('id'))->where('type', self::PRODUCT_TYPE)->where('status', 1)
            ->orderByRaw('sort IS NULL')->orderBy('sort')->orderBy('id')->get()->groupBy('product_id');
    }

    /* One product as the storefront shows it; null without a live plan with a price. `$Full` adds its page text and SEO. */
    public function product(Product $Product, Collection $Plans, bool $Full = false): ?array
    {
        $Info = $this->decode($Product->detail);
        $PlanRows = [];
        foreach ($Plans as $Plan) {
            [$Price, $Was] = AccountOrders::planPrices($Plan, $Info, Pricing::planRate($Plan, $Product));
            if ($Price <= 0) {
                continue;
            }
            $PlanRows[] = [
                'id' => $Plan->id,
                'name' => (string)$Plan->name,
                'en_name' => trim((string)$Plan->en_name) ?: null,
                'popular' => (bool)$Plan->popular,
                'price' => $Price,
                'was' => $Was,
            ];
        }
        if (!$PlanRows) {
            return null;
        }
        $Data = [
            'id' => $Product->id,
            'slug' => (string)$Product->slug,
            'name' => trim((string)($Info['name'] ?? '')) ?: (string)$Product->faName,
            'en_name' => trim((string)($Info['en_name'] ?? '')) ?: null,
            'kind' => in_array($Info['kind'] ?? null, self::KINDS, true) ? $Info['kind'] : 'verify',
            'tagline' => trim((string)($Info['tagline'] ?? '')) ?: null,
            'tagline_en' => trim((string)($Info['tagline_en'] ?? '')) ?: null,
            'color' => preg_match('/^#[0-9a-f]{6}$/i', (string)($Info['color'] ?? '')) ? strtolower($Info['color']) : null,
            'image' => $this->assetPath($Info['image_gallery'] ?? null),
            'delivery' => trim((string)($Info['delivery'] ?? '')) ?: null,
            'delivery_en' => trim((string)($Info['delivery_en'] ?? '')) ?: null,
            'popular' => !empty($Info['popular']),
            'plans' => $PlanRows,
            'fields' => array_map(fn($f) => [
                'name' => $f['name'], 'title' => $f['title'], 'title_en' => $f['title_en'] ?: null, 'type' => $f['type'],
                'required' => $f['required'], 'hint' => $f['hint'] ?: null, 'hint_en' => $f['hint_en'] ?: null,
                'options' => $f['options'], 'accept' => $f['accept'], 'max_mb' => $f['max_mb'] ?: null,
            ], self::fields($Product)),
            'notes' => collect($this->decode($Product->notes))
                ->map(fn($v, $k) => ['label' => trim((string)$k), 'value' => trim((string)$v)])
                ->filter(fn($n) => $n['label'] !== '' && $n['value'] !== '')->values(),
        ];
        if ($Full) {
            $Data += [
                'short_detail' => $Info['short_detail'] ?? null,
                'content' => $Info['long_detail'] ?? null,
                'seo' => ['meta_title' => $Info['meta_title'] ?? null, 'meta_desc' => $Info['meta_desc'] ?? null, 'head' => $Info['seo'] ?? null],
            ];
        }
        return $Data;
    }

    /* ---------- Buying ---------- */

    /*
     * What the buyer asked for, checked against the product: {product_id, plan_id, fields: {name: value | file}}.
     * Sent as multipart when there are files (fields[passport] = the file). Nothing is stored here.
     * @return array{0: ?array, 1: ?array} [spec, error]; error = [message, code, status, data]
     */
    public function spec(array $Input): array
    {
        $Product = Product::where('id', intval($Input['product_id'] ?? 0))->where('type', self::PRODUCT_TYPE)->where('status', 1)->first();
        if (is_null($Product)) {
            return [null, ['این محصول در حال حاضر در دسترس نیست.', 'unavailable', 404, []]];
        }
        $Plan = Plan::where('product_id', $Product->id)->where('type', self::PRODUCT_TYPE)->where('id', intval($Input['plan_id'] ?? 0))->where('status', 1)->first();
        if (is_null($Plan)) {
            return [null, ['این پلن دیگر ارائه نمی شود. لطفا پلن دیگری انتخاب کنید.', 'plan_invalid', 422, []]];
        }
        [$Price] = AccountOrders::planPrices($Plan, $this->decode($Product->detail), Pricing::planRate($Plan, $Product));
        if ($Price <= 0) {
            return [null, ['این محصول در حال حاضر در دسترس نیست.', 'unavailable', 404, []]];
        }

        // Names, titles and rules come from the product, never from the request.
        $Given = is_array($Input['fields'] ?? null) ? $Input['fields'] : [];
        $Answers = [];
        $Files = [];
        foreach (self::fields($Product) as $Field) {
            $Raw = $Given[$Field['name']] ?? null;
            $Bad = fn(string $Why = '') => [null, [$Why ?: 'لطفا «' . $Field['title'] . '» را درست وارد کنید.', 'fields_invalid', 422, ['field' => $Field['name']]]];

            if ($Field['type'] === 'file') {
                $File = $Raw instanceof UploadedFile ? $Raw : null;
                if (!$File) {
                    if ($Field['required']) {
                        return $Bad('لطفا فایل «' . $Field['title'] . '» را بفرستید.');
                    }
                    continue;
                }
                $Mimes = self::mimes($Field);
                $Mime = $File->isValid() ? (string)$File->getMimeType() : '';
                if (!isset($Mimes[$Mime])) {
                    return $Bad('فایل «' . $Field['title'] . '» باید ' . $this->acceptLabel($Field) . ' باشد.');
                }
                if ($File->getSize() > $Field['max_mb'] * 1024 * 1024) {
                    return $Bad('حجم فایل «' . $Field['title'] . '» باید حداکثر ' . $Field['max_mb'] . ' مگابایت باشد.');
                }
                $Files[$Field['name']] = ['file' => $File, 'ext' => $Mimes[$Mime], 'mime' => $Mime];
                $Answers[] = [
                    'filed_name' => $Field['name'], 'filed_type' => $Field['title'], 'kind' => 'file',
                    'filed_value' => $this->fileName($File->getClientOriginalName(), $Mimes[$Mime]),
                    'mime' => $Mime, 'size' => (int)$File->getSize(),
                ];
                continue;
            }

            $Value = is_scalar($Raw) ? trim(strtr((string)$Raw, ["\r\n" => "\n"])) : '';
            if ($Value === '') {
                if ($Field['required']) {
                    return $Bad();
                }
                continue;
            }
            $Plain = $this->westernDigits($Value);
            $Ok = match ($Field['type']) {
                'select' => in_array($Value, $Field['options'], true),
                'email' => mb_strlen($Value) <= 190 && filter_var($Value, FILTER_VALIDATE_EMAIL) !== false,
                'phone' => preg_match('/^\+?[\d\s\-()]{6,24}$/', $Plain) === 1 && strlen(preg_replace('/\D/', '', $Plain)) >= 6,
                'date' => $this->validDate($Plain),
                'textarea' => mb_strlen($Value) <= 3000,
                default => mb_strlen($Value) <= 300 && !str_contains($Value, "\n"),
            };
            if (!$Ok) {
                return $Bad();
            }
            $Answers[] = [
                'filed_name' => $Field['name'], 'filed_type' => $Field['title'], 'kind' => $Field['type'],
                'filed_value' => in_array($Field['type'], ['phone', 'date'], true) ? $Plain : $Value,
            ];
        }

        $Detail = $Answers;
        $Detail['plan'] = ['plan_id' => $Plan->id, 'plan_name' => $Plan->name, 'price' => $Price];
        // An organisation's discount comes off last (Organizations::apply; the storefront shows the same cut).
        return [Organizations::apply(['product' => $Product, 'plan' => $Plan, 'price' => $Price, 'detail' => $Detail, 'files' => $Files], 'verification'), null];
    }

    /*
     * An unpaid order with its item and payment (the shapes AccountOrders::createOrder makes), and the buyer's files
     * saved under verification/{order id}/ on the private disk.
     * @return array{0: Order, 1: Payment}
     */
    public function createOrder($User, array $Spec): array
    {
        $Saved = [];
        try {
            return DB::transaction(function () use ($User, $Spec, &$Saved) {
                $Order = new Order();
                $Order->type = self::TYPE;
                $Order->status = 'waiting to pay';
                $Order->pay_status = 'waiting to pay';
                $Order->price = $Spec['price'];
                $Order->user_id = $User->id;
                $Order->product_id = $Spec['product']->id;
                $Order->expire_date = Carbon::now()->addMinutes(60);
                $Order->save();

                $Detail = $Spec['detail'];
                $Disk = Storage::disk(config('verification.disk', 'local'));
                foreach ($Detail as $Key => $Row) {
                    if (!is_array($Row) || ($Row['kind'] ?? null) !== 'file' || !isset($Spec['files'][$Row['filed_name']])) {
                        continue;
                    }
                    $F = $Spec['files'][$Row['filed_name']];
                    $Path = self::folder($Order->id) . '/' . $Row['filed_name'] . '-' . Str::random(20) . '.' . $F['ext'];
                    if (!$Disk->put($Path, file_get_contents($F['file']->getRealPath()))) {
                        throw new \RuntimeException('verification file not saved');
                    }
                    $Saved[] = $Path;
                    $Detail[$Key]['file'] = $Path;
                }

                $Item = new OrderItem();
                $Item->order_id = $Order->id;
                $Item->product_id = $Spec['product']->id;
                $Item->user_id = null;
                $Item->type = self::TYPE;
                $Item->price = $Spec['price'];
                $Item->detail = json_encode($Detail, JSON_UNESCAPED_UNICODE);
                $Item->save();

                $Payment = new Payment();
                $Payment->order_id = $Order->id;
                $Payment->user_id = $User->id;
                $Payment->type = 1;
                $Payment->status = 'waiting to pay';
                $Payment->gate = 'Bank';
                $Payment->creator = 'System';
                $Payment->price = $Spec['price'];
                $Payment->save();

                $Order->pay_id = $Payment->id;
                $Order->save();

                return [$Order, $Payment];
            });
        } catch (\Throwable $e) {
            // The order rolled back: don't keep its files.
            foreach ($Saved as $Path) {
                Storage::disk(config('verification.disk', 'local'))->delete($Path);
            }
            throw $e;
        }
    }

    public static function folder(int $OrderId): string
    {
        return 'verification/' . $OrderId;
    }

    // An order as the storefront and /account show it. Files only by name; nothing that could open them.
    public function data(Order $Order): array
    {
        $Item = OrderItem::where('order_id', $Order->id)->first();
        $Detail = $Item ? (json_decode((string)$Item->detail, true) ?: []) : [];
        $Product = $Item ? Product::find($Item->product_id) : null;
        $Info = $Product ? $this->decode($Product->detail) : [];
        $PlanId = $Detail['plan']['plan_id'] ?? null;
        $Plan = $PlanId ? Plan::find($PlanId) : null;
        unset($Detail['plan']);

        return [
            'id' => $Order->id,
            'status' => match ($Order->status) {
                'waiting to pay' => 'unpaid',
                'pending' => 'queued',
                'progressing' => 'in_progress',
                'complete' => 'done',
                'back money to wallet' => 'refunded',
                'suspended' => 'paused',
                default => 'cancelled',
            },
            'product' => $Product ? [
                'id' => $Product->id,
                'slug' => (string)$Product->slug,
                'name' => trim((string)($Info['name'] ?? '')) ?: (string)$Product->faName,
                'en_name' => trim((string)($Info['en_name'] ?? '')) ?: null,
                'image' => $this->assetPath($Info['image_gallery'] ?? null),
                'color' => $Info['color'] ?? null,
                'kind' => in_array($Info['kind'] ?? null, self::KINDS, true) ? $Info['kind'] : 'verify',
            ] : null,
            'plan' => [
                'id' => $PlanId ? (int)$PlanId : null,
                'name' => (string)($Detail['plan']['plan_name'] ?? ($Plan->name ?? '')),
                'en_name' => $Plan ? (trim((string)$Plan->en_name) ?: null) : null,
            ],
            'fields' => collect($Detail)->filter(fn($f) => is_array($f) && isset($f['filed_value']))
                ->map(fn($f) => [
                    'title' => (string)($f['filed_type'] ?? ''),
                    'value' => (string)$f['filed_value'],
                    'file' => ($f['kind'] ?? null) === 'file',
                ])->values(),
            'price' => (int)$Order->price,
            'paid_with' => ($Order->pay_id && Payment::where('id', $Order->pay_id)->value('gate') === 'Bank') ? 'card' : 'wallet',
            'created_at' => Carbon::parse($Order->created_at)->toIso8601String(),
            'review' => $Order->status === 'complete' && $Product
                ? app(OrderReviews::class)->state($Order, (int)$Product->id, $Product, app(OrderReviews::class)->reviewed([$Order->id]))
                : null,
        ];
    }

    /* ---------- Helpers ---------- */

    private function acceptLabel(array $Field): string
    {
        $Names = ['image' => 'عکس (JPG، PNG، WEBP یا HEIC)', 'pdf' => 'PDF'];
        return implode(' یا ', array_map(fn($a) => $Names[$a], $Field['accept'] ?: ['image']));
    }

    // The buyer's own file name, kept short and harmless (only shown as text), with the extension we saved.
    private function fileName(string $Name, string $Ext): string
    {
        $Base = pathinfo($Name, PATHINFO_FILENAME);
        $Base = trim((string)preg_replace('/[^\p{L}\p{N} ._()-]+/u', '', $Base)) ?: 'file';
        return mb_substr($Base, 0, 80) . '.' . $Ext;
    }

    // 2024-05-01, 1403/02/12 (a Jalali or Gregorian date as the page sent it).
    private function validDate(string $Value): bool
    {
        if (!preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $Value, $m)) {
            return false;
        }
        [$Y, $M, $D] = [(int)$m[1], (int)$m[2], (int)$m[3]];
        return $Y >= 1300 && $Y <= 2100 && $M >= 1 && $M <= 12 && $D >= 1 && $D <= 31;
    }

    private function westernDigits(string $Value): string
    {
        return strtr($Value, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]);
    }

    private function decode($Value): array
    {
        $Data = is_array($Value) ? $Value : json_decode((string)$Value, true);
        return is_array($Data) ? $Data : [];
    }

    private function assetPath($Path): ?string
    {
        if (empty($Path)) {
            return null;
        }
        return preg_match('#^https?://#', $Path) ? $Path : '/' . ltrim($Path, '/');
    }
}
