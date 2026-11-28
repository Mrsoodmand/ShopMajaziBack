<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Services\Pricing;
use App\Services\VerificationOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Verification products for the React admin (admin › وریفای): products of type "verification" with their plans
 * (plans.type = verification) and the inputs the buyer fills in (products.filds, VerificationOrders::fields).
 * `detail` JSON keeps the same keys as accounts (name, price_type, short_detail, long_detail, meta…) plus `kind`.
 *
 * Also serves the files buyers sent with an order (file()), from the private disk, to signed-in admins only.
 */
class VerificationApiController extends Controller
{
    use AdminApi;

    private const TYPE = VerificationOrders::PRODUCT_TYPE;
    // Products in the header menu's «وریفای» group (Api\StorefrontController::picks › menu.verify).
    public const MENU_LIMIT = 6;
    // Products in the home page shop window's «وریفای» tab (picks.home.verify).
    public const HOME_LIMIT = 8;

    public function index(Request $request)
    {
        $products = Product::where('type', self::TYPE)->orderByDesc('status')->orderByRaw('sort IS NULL')->orderBy('sort')->orderByDesc('id')->get();
        $plans = Plan::where('type', self::TYPE)->whereIn('product_id', $products->pluck('id'))
            ->select('product_id', DB::raw('count(*) as total'), DB::raw('sum(status = 1) as live'))->groupBy('product_id')->get()->keyBy('product_id');
        $waiting = Order::where('type', VerificationOrders::TYPE)->where('status', 'pending')->count();

        return response()->json([
            'waitingOrders' => $waiting,
            'products' => $products->map(function (Product $p) use ($plans) {
                $d = $this->decode($p->detail);
                return [
                    'id' => $p->id,
                    'name' => (string) ($d['name'] ?? $p->faName),
                    'slug' => (string) $p->slug,
                    'image' => $d['image_gallery'] ?? null,
                    'kind' => in_array($d['kind'] ?? null, VerificationOrders::KINDS, true) ? $d['kind'] : 'verify',
                    'live' => (int) $p->status === 1,
                    'currency' => (string) ($d['price_type'] ?? '1') === '0' ? 'toman' : 'dollar',
                    'plans' => (int) ($plans[$p->id]->total ?? 0),
                    'livePlans' => (int) ($plans[$p->id]->live ?? 0),
                    'inputs' => count(VerificationOrders::fields($p)),
                    'inMenu' => (bool) $p->in_menu,
                    'onHome' => (bool) $p->on_home,
                    'updatedAt' => $this->jdate($p->updated_at),
                ];
            })->values(),
        ]);
    }

    public function show($id)
    {
        $p = Product::where('type', self::TYPE)->find($id);
        if (!$p) {
            return $this->missing('این محصول پیدا نشد.');
        }
        $d = $this->decode($p->detail);

        return response()->json(['product' => [
            'id' => $p->id,
            'name' => (string) ($d['name'] ?? $p->faName),
            'slug' => (string) $p->slug,
            'live' => (int) $p->status === 1,
            'kind' => in_array($d['kind'] ?? null, VerificationOrders::KINDS, true) ? $d['kind'] : 'verify',
            'currency' => (string) ($d['price_type'] ?? '1') === '0' ? 'toman' : 'dollar',
            'commission' => $p->commission !== null ? (string) $p->commission : '',
            'profit' => $p->profit_percent !== null ? rtrim(rtrim((string) $p->profit_percent, '0'), '.') : '',
            'pricing' => [
                'marketDollar' => (int) round(Pricing::market(Pricing::DOLLAR)),
                'dollarProfit' => (float) Pricing::currencyProfit(Pricing::DOLLAR),
            ],
            'short' => (string) ($d['short_detail'] ?? ''),
            'content' => (string) ($d['long_detail'] ?? ''),
            'image' => (string) ($d['image_gallery'] ?? ''),
            'metaTitle' => (string) ($d['meta_title'] ?? ''),
            'metaDesc' => (string) ($d['meta_desc'] ?? ''),
            'headHtml' => (string) ($d['seo'] ?? ''),
            'enName' => (string) ($d['en_name'] ?? ''),
            'tagline' => (string) ($d['tagline'] ?? ''),
            'taglineEn' => (string) ($d['tagline_en'] ?? ''),
            'color' => (string) ($d['color'] ?? ''),
            'delivery' => (string) ($d['delivery'] ?? ''),
            'deliveryEn' => (string) ($d['delivery_en'] ?? ''),
            'popular' => !empty($d['popular']),
            'sort' => $p->sort !== null ? (string) $p->sort : '',
            'inMenu' => (bool) $p->in_menu,
            'onHome' => (bool) $p->on_home,
            'fields' => collect(VerificationOrders::fields($p))->map(fn ($f) => [
                'name' => $f['name'], 'title' => $f['title'], 'titleEn' => $f['title_en'], 'type' => $f['type'],
                'required' => $f['required'], 'hint' => $f['hint'], 'hintEn' => $f['hint_en'], 'options' => $f['options'],
                'accept' => $f['accept'], 'maxMb' => $f['max_mb'] ? (string) $f['max_mb'] : '5',
            ])->values(),
            'notes' => collect($this->decode($p->notes))->map(fn ($v, $k) => ['label' => (string) $k, 'value' => (string) $v])->values(),
            'plans' => Plan::where('product_id', $p->id)->where('type', self::TYPE)->orderBy('sort')->orderBy('id')->get()->map(fn (Plan $pl) => [
                'id' => $pl->id,
                'name' => (string) $pl->name,
                'enName' => (string) $pl->en_name,
                'popular' => (bool) $pl->popular,
                'price' => $pl->price !== null ? (string) $pl->price : '',
                'discount' => $pl->discount !== null ? (string) $pl->discount : '',
                'profit' => $pl->profit_percent !== null ? rtrim(rtrim((string) $pl->profit_percent, '0'), '.') : '',
                'live' => (int) $pl->status === 1,
            ])->values(),
        ]]);
    }

    public function save(Request $request, $id = null)
    {
        $p = $id ? Product::where('type', self::TYPE)->find($id) : new Product();
        if (!$p) {
            return $this->missing('این محصول پیدا نشد.');
        }
        $maxMb = (int) config('verification.max_mb', 10);
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->where('type', self::TYPE)->ignore($p->id)],
            'live' => 'boolean',
            'kind' => ['required', Rule::in(VerificationOrders::KINDS)],
            'currency' => ['required', Rule::in(['dollar', 'toman'])],
            'commission' => 'nullable|numeric|min:0|max:100',
            'profit' => 'nullable|numeric|min:-50|max:1000',
            'short' => 'required|string',
            'content' => 'required|string',
            'image' => 'nullable|string|max:500',
            'metaTitle' => 'required|string|max:255',
            'metaDesc' => 'required|string|max:1000',
            'headHtml' => 'nullable|string',
            'enName' => 'nullable|string|max:120',
            'tagline' => 'nullable|string|max:160',
            'taglineEn' => 'nullable|string|max:160',
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'delivery' => 'nullable|string|max:80',
            'deliveryEn' => 'nullable|string|max:80',
            'popular' => 'boolean',
            'sort' => 'nullable|integer|min:0|max:100000',
            'inMenu' => 'boolean',
            'onHome' => 'boolean',
            'fields' => 'array|max:20',
            'fields.*.name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z][A-Za-z0-9_]*$/', 'distinct'],
            'fields.*.title' => 'required|string|max:190',
            'fields.*.titleEn' => 'nullable|string|max:190',
            'fields.*.type' => ['required', Rule::in(VerificationOrders::FIELD_TYPES)],
            'fields.*.required' => 'boolean',
            'fields.*.hint' => 'nullable|string|max:300',
            'fields.*.hintEn' => 'nullable|string|max:300',
            'fields.*.options' => 'array|max:30',
            'fields.*.options.*' => 'string|max:100',
            'fields.*.accept' => 'array',
            'fields.*.accept.*' => [Rule::in(array_keys(VerificationOrders::ACCEPT))],
            'fields.*.maxMb' => "nullable|integer|min:1|max:$maxMb",
            'notes' => 'array',
            'notes.*.label' => 'required|string|max:255|distinct',
            'notes.*.value' => 'required|string|max:255',
            'plans' => 'array|min:1',
            'plans.*.id' => 'nullable|integer',
            'plans.*.name' => 'required|string|max:255',
            'plans.*.enName' => 'nullable|string|max:200',
            'plans.*.price' => 'required|numeric|min:0',
            'plans.*.discount' => 'nullable|numeric|min:0',
            'plans.*.profit' => 'nullable|numeric|min:-50|max:1000',
            'plans.*.live' => 'boolean',
            'plans.*.popular' => 'boolean',
        ], $this->messages() + [
            'fields.*.name.regex' => 'نام فنی فقط حروف انگلیسی، عدد و _ (مثل passport_photo).',
            'fields.*.maxMb.max' => "حداکثر حجم فایل $maxMb مگابایت است.",
            'plans.min' => 'دست‌کم یک پلن با قیمت بسازید.',
            'color.regex' => 'رنگ را به شکل #10a37f بنویسید.',
            'profit.min' => 'سود اختصاصی نمی‌تواند کمتر از -۵۰ باشد.',
            'profit.max' => 'سود اختصاصی نمی‌تواند بیشتر از ۱۰۰۰ باشد.',
            'plans.*.profit.min' => 'سود پلن نمی‌تواند کمتر از ۵۰- درصد باشد.',
            'plans.*.profit.max' => 'سود پلن حداکثر ۱۰۰۰ درصد است.',
        ]);
        foreach ($data['fields'] ?? [] as $i => $f) {
            if ($f['type'] === 'select' && count(array_filter(array_map('trim', $f['options'] ?? []), 'strlen')) < 2) {
                return response()->json(['message' => 'گزینه', 'errors' => ["fields.$i.options" => ['دست‌کم دو گزینه بنویسید.']]], 422);
            }
            if ($f['type'] === 'file' && empty($f['accept'])) {
                return response()->json(['message' => 'نوع فایل', 'errors' => ["fields.$i.accept" => ['دست‌کم یک نوع فایل (عکس یا PDF) را انتخاب کنید.']]], 422);
            }
        }

        // The menu group holds at most MENU_LIMIT products.
        if (!empty($data['inMenu'])) {
            $others = Product::where('type', self::TYPE)->where('in_menu', 1)->when($p->id, fn ($q) => $q->where('id', '!=', $p->id))->pluck('faName');
            if ($others->count() >= self::MENU_LIMIT) {
                return response()->json(['message' => 'منو پر است', 'errors' => ['inMenu' => [
                    'منوی وریفای حداکثر ' . self::MENU_LIMIT . ' محصول دارد؛ اول یکی را از منو بردارید: ' . $others->implode('، '),
                ]]], 422);
            }
        }

        if (!empty($data['onHome'])) {
            $others = Product::where('type', self::TYPE)->where('on_home', 1)->when($p->id, fn ($q) => $q->where('id', '!=', $p->id))->pluck('faName');
            if ($others->count() >= self::HOME_LIMIT) {
                return response()->json(['message' => 'صفحه اول پر است', 'errors' => ['onHome' => [
                    'تب وریفای صفحه اول حداکثر ' . self::HOME_LIMIT . ' محصول دارد؛ اول یکی را بردارید: ' . $others->implode('، '),
                ]]], 422);
            }
        }

        DB::transaction(function () use ($p, $data) {
            $detail = array_merge($this->decode($p->detail), [
                'name' => $data['name'],
                'kind' => $data['kind'],
                'type' => '1', // with plans
                'price_type' => $data['currency'] === 'toman' ? '0' : '1',
                'short_detail' => $data['short'],
                'long_detail' => $data['content'],
                'slug' => $data['slug'],
                'meta_title' => $data['metaTitle'],
                'meta_desc' => $data['metaDesc'],
                'image_gallery' => $data['image'] ?? null,
                'seo' => $data['headHtml'] ?? null,
                'en_name' => $data['enName'] ?? null,
                'tagline' => $data['tagline'] ?? null,
                'tagline_en' => $data['taglineEn'] ?? null,
                'color' => isset($data['color']) && $data['color'] !== '' ? strtolower($data['color']) : null,
                'delivery' => $data['delivery'] ?? null,
                'delivery_en' => $data['deliveryEn'] ?? null,
                'popular' => !empty($data['popular']),
            ]);
            $fields = collect($data['fields'] ?? [])->map(fn ($f) => [
                'name' => $f['name'],
                'title' => trim($f['title']),
                'title_en' => trim((string) ($f['titleEn'] ?? '')),
                'type' => $f['type'],
                'required' => (bool) ($f['required'] ?? true),
                'hint' => trim((string) ($f['hint'] ?? '')),
                'hint_en' => trim((string) ($f['hintEn'] ?? '')),
                'options' => $f['type'] === 'select' ? array_values(array_filter(array_map('trim', $f['options'] ?? []), 'strlen')) : [],
                'accept' => $f['type'] === 'file' ? array_values(array_unique($f['accept'])) : [],
                'max_mb' => $f['type'] === 'file' ? (int) ($f['maxMb'] ?? 5) : null,
            ])->values()->all();
            $notes = collect($data['notes'] ?? [])->mapWithKeys(fn ($n) => [$n['label'] => $n['value']])->all();

            $p->forceFill([
                'type' => self::TYPE,
                'faName' => $data['name'],
                'slug' => $data['slug'],
                'detail' => json_encode($detail, JSON_UNESCAPED_UNICODE),
                'price' => 0,
                'filds' => json_encode($fields, JSON_UNESCAPED_UNICODE),
                'notes' => json_encode((object) $notes, JSON_UNESCAPED_UNICODE),
                'commission' => ($data['commission'] ?? '') === '' ? null : $data['commission'],
                'profit_percent' => isset($data['profit']) && $data['profit'] !== '' ? $data['profit'] : null,
                'status' => ($data['live'] ?? false) ? 1 : 0,
                'sort' => ($data['sort'] ?? '') === '' ? null : (int) $data['sort'],
                'in_menu' => !empty($data['inMenu']),
                'on_home' => !empty($data['onHome']),
            ])->save();

            $keep = [];
            $popularTaken = false;
            foreach (array_values($data['plans'] ?? []) as $i => $row) {
                $popular = !$popularTaken && ($row['popular'] ?? false);
                $popularTaken = $popularTaken || $popular;
                $plan = !empty($row['id']) ? Plan::where('product_id', $p->id)->where('type', self::TYPE)->find($row['id']) : null;
                $plan ??= new Plan();
                $plan->forceFill([
                    'product_id' => $p->id,
                    'type' => self::TYPE,
                    'name' => $row['name'],
                    'en_name' => ($row['enName'] ?? '') === '' ? null : $row['enName'],
                    'popular' => $popular ? 1 : 0,
                    'price' => $row['price'],
                    'discount' => ($row['discount'] ?? '') === '' ? null : $row['discount'],
                    'profit_percent' => ($row['profit'] ?? '') === '' ? null : $row['profit'],
                    'status' => ($row['live'] ?? true) ? 1 : 0,
                    'sort' => $i + 1,
                ])->save();
                $keep[] = $plan->id;
            }
            Plan::where('product_id', $p->id)->where('type', self::TYPE)->whereNotIn('id', $keep)->delete();
        });

        // The header menu and the home page's Verification tab (storefront "picks" section, cached until a save like
        // this one) and their "from" prices: show the change now.
        app(\App\Services\CacheManager::class)->refreshPicks();

        return response()->json(['success' => $id ? 'تغییرات ذخیره شد.' : 'محصول ساخته شد.', 'id' => $p->id]);
    }

    public function upload(Request $request)
    {
        return $this->storeImage($request, 'verification');
    }

    /*
     * One file a buyer sent with a verification order, by its field name. Shown inline (images, PDF); the
     * headers keep the browser from running anything in it. Gone after verification:prune-files: 410.
     */
    public function file($id, $field)
    {
        $order = Order::where('type', VerificationOrders::TYPE)->find($id);
        $item = $order ? OrderItem::where('order_id', $order->id)->first() : null;
        $row = $item ? collect($this->decode($item->detail))->first(fn ($r) => is_array($r) && ($r['filed_name'] ?? null) === $field && ($r['kind'] ?? null) === 'file') : null;
        if (!$row) {
            return $this->missing('این فایل پیدا نشد.');
        }
        $disk = Storage::disk(config('verification.disk', 'local'));
        $path = (string) ($row['file'] ?? '');
        // Only files this order saved, under its own folder.
        if ($path === '' || !str_starts_with($path, VerificationOrders::folder($order->id) . '/') || str_contains($path, '..') || !$disk->exists($path)) {
            return $this->refuse(!empty($row['pruned']) ? 'این فایل طبق زمان‌بندی نگهداری حذف شده است.' : 'این فایل پیدا نشد.', 410);
        }
        $mime = (string) ($row['mime'] ?? 'application/octet-stream');
        $mime = in_array($mime, array_merge(...array_map('array_keys', array_values(VerificationOrders::ACCEPT))), true) ? $mime : 'application/octet-stream';

        $headers = [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
            'Referrer-Policy' => 'no-referrer',
        ];
        // Images only: a sandbox CSP would also stop the browser's own PDF viewer.
        if (str_starts_with($mime, 'image/')) {
            $headers['Content-Security-Policy'] = "default-src 'none'; img-src 'self'; style-src 'unsafe-inline'; sandbox";
        }

        return $disk->response($path, (string) ($row['filed_value'] ?? basename($path)), $headers, 'inline');
    }
}
