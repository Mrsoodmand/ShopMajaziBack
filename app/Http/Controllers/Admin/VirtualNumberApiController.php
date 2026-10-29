<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Category;
use App\Models\Pages;
use App\Models\Setting;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use App\Services\PageSeo;
use App\Services\VirtualNumberOffers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Virtual numbers for the React admin (resources/admin/pages/virtual-numbers): services (apps), countries,
 * their provider codes, the offers built from them, and the section settings.
 * Rows keep the old admin's columns and shapes (a provider code of "empty" = not offered; settings row 3; pages
 * row 1), so the storefront, the price checks and the old admin pages read them unchanged.
 */
class VirtualNumberApiController extends Controller
{
    /** Settings row holding the on/off switch of each provider (JSON in `key`). */
    const SETTING_ID = 3;

    /* ---------- Services (apps) ---------- */

    public function services()
    {
        $offers = $this->offerCounts('service_id');

        return response()->json([
            'services' => Virtual_Service::orderBy('sort')->orderBy('id')->get()->map(fn (Virtual_Service $s) => [
                'id' => $s->id,
                'name' => $s->faName,
                'enName' => $s->enName,
                'slug' => $s->slug,
                'image' => $this->asset($s->image),
                'live' => (int) $s->status === 1,
                'featured' => (bool) $s->special,
                'sort' => (int) $s->sort,
                'codes' => $this->codes($s),
                'offers' => $offers[$s->id] ?? ['total' => 0, 'live' => 0],
            ])->values(),
        ]);
    }

    public function service($id)
    {
        $s = Virtual_Service::find($id);
        if (!$s) {
            return $this->missing('سرویس پیدا نشد.');
        }

        return response()->json(['service' => [
            'id' => $s->id,
            'name' => (string) $s->faName,
            'enName' => (string) $s->enName,
            'slug' => (string) $s->slug,
            'sort' => (int) $s->sort,
            'live' => (int) $s->status === 1,
            'featured' => (bool) $s->special,
            'image' => $this->editable($s->image),
            'codes' => $this->codes($s),
            'content' => (string) $s->detail,
            'metaTitle' => (string) $s->meta_title,
            'metaDesc' => (string) $s->meta_desc,
            'seo' => (string) $s->seo,
        ]]);
    }

    public function saveService(Request $request, $id = null)
    {
        $s = $id ? Virtual_Service::find($id) : new Virtual_Service();
        if (!$s) {
            return $this->missing('سرویس پیدا نشد.');
        }
        $data = $request->validate($this->rules('virtual__services', $s) + [
            'featured' => 'boolean',
            'content' => 'nullable|string',
        ], $this->messages());

        $s->forceFill($this->common($data) + [
            'special' => ($data['featured'] ?? false) ? 1 : 0,
            'detail' => $data['content'] ?? null,
        ])->save();

        return response()->json(['success' => $id ? 'سرویس ذخیره شد.' : 'سرویس ساخته شد.', 'id' => $s->id]);
    }

    public function serviceCodes(Request $request, $id)
    {
        return $this->saveCodes($request, Virtual_Service::find($id), 'سرویس پیدا نشد.');
    }

    public function rebuildService(VirtualNumberOffers $offers, $id)
    {
        $s = Virtual_Service::find($id);
        if (!$s) {
            return $this->missing('سرویس پیدا نشد.');
        }
        return $this->rebuilt($offers->rebuildService($s), 'service_id', $s->id);
    }

    /* ---------- Countries ---------- */

    public function countries()
    {
        $offers = $this->offerCounts('country_id');

        return response()->json([
            'regions' => $this->regions(),
            'countries' => Virtual_Country::orderBy('sort')->orderBy('id')->get()->map(fn (Virtual_Country $c) => [
                'id' => $c->id,
                'name' => $c->faName,
                'enName' => $c->enName,
                'slug' => $c->slug,
                'flag' => $this->flag($c->code),
                'flagCode' => $c->code,
                'regionId' => $c->category_id ? (int) $c->category_id : null,
                'live' => (int) $c->status === 1,
                'sort' => (int) $c->sort,
                'codes' => $this->codes($c),
                'offers' => $offers[$c->id] ?? ['total' => 0, 'live' => 0],
            ])->values(),
        ]);
    }

    public function country($id)
    {
        $c = Virtual_Country::find($id);
        if (!$c) {
            return $this->missing('کشور پیدا نشد.');
        }

        return response()->json(['regions' => $this->regions(), 'country' => [
            'id' => $c->id,
            'name' => (string) $c->faName,
            'enName' => (string) $c->enName,
            'slug' => (string) $c->slug,
            'sort' => (int) $c->sort,
            'live' => (int) $c->status === 1,
            'flagCode' => (string) $c->code,
            'regionId' => $c->category_id ? (int) $c->category_id : '',
            'image' => $this->editable($c->image),
            'codes' => $this->codes($c),
            'content' => (string) $c->long_detail,
            'metaTitle' => (string) $c->meta_title,
            'metaDesc' => (string) $c->meta_desc,
            'seo' => (string) $c->seo,
        ]]);
    }

    public function saveCountry(Request $request, $id = null)
    {
        $c = $id ? Virtual_Country::find($id) : new Virtual_Country();
        if (!$c) {
            return $this->missing('کشور پیدا نشد.');
        }
        $data = $request->validate($this->rules('virtual__countries', $c) + [
            // Flag file in public/image/svg: an ISO code like "ru", or "gb-eng".
            'flagCode' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z]{2}(-[A-Za-z]+)?$/'],
            'regionId' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('type', 'virtual-number-category')],
            'content' => 'nullable|string|max:255', // long_detail is a varchar(255)
        ], $this->messages());

        $c->forceFill($this->common($data) + [
            // Stored like the existing rows: "RU", but "gb-eng".
            'code' => str_contains($data['flagCode'], '-') ? strtolower($data['flagCode']) : strtoupper($data['flagCode']),
            'image_2' => $this->flag($data['flagCode']),
            'category_id' => $data['regionId'] ?? null,
            'long_detail' => $data['content'] ?? null,
        ])->save();

        return response()->json(['success' => $id ? 'کشور ذخیره شد.' : 'کشور ساخته شد.', 'id' => $c->id]);
    }

    public function countryCodes(Request $request, $id)
    {
        return $this->saveCodes($request, Virtual_Country::find($id), 'کشور پیدا نشد.');
    }

    public function rebuildCountry(VirtualNumberOffers $offers, $id)
    {
        $c = Virtual_Country::find($id);
        if (!$c) {
            return $this->missing('کشور پیدا نشد.');
        }
        return $this->rebuilt($offers->rebuildCountry($c), 'country_id', $c->id);
    }

    /* ---------- Offers (read only; prices come from the price checks) ---------- */

    public function offers(Request $request)
    {
        $by = $request->filled('service') ? 'service_id' : ($request->filled('country') ? 'country_id' : null);
        if (!$by) {
            return response()->json(['message' => 'سرویس یا کشور را مشخص کنید.', 'errors' => ['service' => ['سرویس یا کشور را مشخص کنید.']]], 422);
        }
        $id = $request->integer($by === 'service_id' ? 'service' : 'country');
        $rows = Virtual_Products::where($by, $id)->get();
        // Name each row after the other side: a service's offers by country, a country's by service.
        $other = $by === 'service_id' ? 'country_id' : 'service_id';
        $names = ($by === 'service_id' ? Virtual_Country::query() : Virtual_Service::query())
            ->whereIn('id', $rows->pluck($other)->unique())->get()->keyBy('id');
        $jdf = new Jdf();

        return response()->json(['offers' => $rows->map(function (Virtual_Products $p) use ($other, $names, $by, $jdf) {
            $item = $names[$p->$other] ?? null;
            return [
                'id' => $p->id,
                'provider' => $p->type,
                'server' => VirtualNumberOffers::PROVIDERS[$p->type] ?? null,
                'name' => $this->plainName($item?->faName ?? ($by === 'service_id' ? $p->countryName : $p->faName)),
                'image' => $by === 'service_id' ? $this->asset($p->countryImage) : $this->asset($item?->image ?? $p->serviceImage),
                'sort' => (int) ($by === 'service_id' ? $p->country_sort : ($item?->sort ?? 0)),
                'serviceCode' => $p->service_code,
                'countryCode' => $p->country_code,
                'price' => (float) $p->toman_price > 0 ? $this->price($p->toman_price) : null,
                'providerPrice' => is_numeric($p->dollar_price) ? (float) $p->dollar_price : null,
                'state' => $this->state($p),
                'updatedAt' => $p->updated_at ? $jdf->jdate('Y/m/d H:i', strtotime((string) $p->updated_at)) : null,
            ];
        })->sortBy([['sort', 'asc'], ['name', 'asc'], ['server', 'asc']])->values()]);
    }

    /* ---------- Settings ---------- */

    public function settings(PageSeo $seo)
    {
        $switches = json_decode((string) optional(Setting::find(self::SETTING_ID))->key, true) ?: [];
        $page = $seo->row('virtual-number');
        $pageSeo = json_decode((string) $page?->seo, true) ?: [];
        $faq = json_decode((string) $page?->faq, true);

        return response()->json([
            'providers' => collect(VirtualNumberOffers::LABELS)->map(fn ($label, $key) => (bool) ($switches[$key] ?? false)),
            'stats' => $this->stats(),
            'page' => [
                'image' => (string) $page?->image,
                'content' => (string) $page?->long_detail,
                'seoHtml' => (string) ($pageSeo['meta'] ?? ''),
                'faq' => collect(is_array($faq) ? $faq : [])->map(fn ($f) => ['q' => (string) ($f['question'] ?? ''), 'a' => (string) ($f['answer'] ?? '')])->values(),
            ],
        ]);
    }

    public function saveSettings(Request $request, PageSeo $seo)
    {
        $data = $request->validate([
            'providers' => 'array',
            'providers.*' => 'boolean',
            'page.image' => 'nullable|string|max:255',
            'page.content' => 'nullable|string',
            'page.seoHtml' => 'nullable|string',
            'page.faq' => 'array',
            'page.faq.*.q' => 'required|string',
            'page.faq.*.a' => 'required|string',
        ], $this->messages());

        DB::transaction(function () use ($data, $seo) {
            // Same shape the old settings form posted: "1" when on, null when off.
            $setting = Setting::find(self::SETTING_ID) ?? (new Setting())->forceFill(['id' => self::SETTING_ID, 'name' => 'virtual number']);
            $setting->key = json_encode(collect(['pva', 'man', 'land', 'active'])
                ->mapWithKeys(fn ($k) => [$k => ($data['providers'][$k] ?? false) ? '1' : null])->all());
            $setting->save();

            $page = $seo->row('virtual-number')
                ?? (new Pages())->forceFill(['title' => 'شماره مجازی', 'slug' => 'virtual-number', 'related_blogs' => '']);
            // H1, meta title/description and share image belong to React › SEO (PageSeo); only `meta` is set here.
            $pageSeo = json_decode((string) $page->seo, true);
            $pageSeo = is_array($pageSeo) ? $pageSeo : [];
            $pageSeo['meta'] = trim((string) ($data['page']['seoHtml'] ?? '')) ?: null;
            $page->forceFill([
                'image' => $data['page']['image'] ?? null,
                'long_detail' => $data['page']['content'] ?? '',
                'seo' => json_encode($pageSeo, JSON_UNESCAPED_UNICODE),
                'faq' => json_encode(collect($data['page']['faq'] ?? [])->map(fn ($f) => ['question' => $f['q'], 'answer' => $f['a']])->values(), JSON_UNESCAPED_UNICODE),
            ])->save();
        });

        return response()->json(['success' => 'تنظیمات شماره مجازی ذخیره شد.']);
    }

    /* ---------- Images ---------- */

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|image|mimes:png,jpg,jpeg,webp,svg|max:2048'], [
            'file.required' => 'فایلی انتخاب نشده است.',
            'file.image' => 'فقط تصویر (PNG، JPG، WebP یا SVG) بارگذاری کنید.',
            'file.mimes' => 'فقط تصویر (PNG، JPG، WebP یا SVG) بارگذاری کنید.',
            'file.max' => 'حجم تصویر حداکثر ۲ مگابایت باشد.',
        ]);
        $file = $request->file('file');
        $name = (Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image')
            . '-' . now()->format('YmdHis') . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(public_path('upload/virtual-number'), $name);

        return response()->json(['path' => '/upload/virtual-number/' . $name]);
    }

    /* ---------- Helpers ---------- */

    /** Validation shared by services and countries; `$table` scopes the slug check. */
    private function rules(string $table, Model $row): array
    {
        return [
            'name' => 'required|string|max:255',
            'enName' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:250', 'regex:/^[a-z0-9.-]+$/', Rule::unique($table, 'slug')->ignore($row->id)],
            'sort' => 'required|integer|min:0|max:100000',
            'live' => 'boolean',
            'image' => 'nullable|string|max:255',
            'codes' => 'array',
            'codes.pva' => ['nullable', 'string', 'max:50', 'regex:/^\S+$/'],
            'codes.man' => ['nullable', 'string', 'max:50', 'regex:/^\S+$/'],
            'codes.active' => ['nullable', 'string', 'max:50', 'regex:/^\S+$/'],
            'codes.land' => ['nullable', 'string', 'max:11', 'regex:/^\S+$/'],
            'metaTitle' => 'required|string|max:255',
            'metaDesc' => 'required|string|max:255',
            'seo' => 'nullable|string',
        ];
    }

    /** Columns services and countries share. A blank provider code is stored as the old "empty". */
    private function common(array $data): array
    {
        return [
            'faName' => $data['name'],
            'enName' => $data['enName'],
            'slug' => $data['slug'],
            'sort' => $data['sort'],
            'status' => ($data['live'] ?? false) ? 1 : 0,
            'image' => $data['image'] ?? null,
            'meta_title' => $data['metaTitle'],
            'meta_desc' => $data['metaDesc'],
            'seo' => $data['seo'] ?? null,
        ] + $this->storedCodes($data['codes'] ?? []);
    }

    private function storedCodes(array $codes): array
    {
        return collect(VirtualNumberOffers::LABELS)->map(fn ($label, $key) => VirtualNumberOffers::offered($codes[$key] ?? null) ? trim($codes[$key]) : 'empty')->all();
    }

    /** Provider codes for the admin: "" where the provider doesn't offer it. */
    private function codes(Model $row): array
    {
        return collect(VirtualNumberOffers::LABELS)->map(fn ($label, $key) => VirtualNumberOffers::offered($row->$key) ? trim((string) $row->$key) : '')->all();
    }

    /** Quick save of a list row's provider codes (the old list's per-row «ذخیره»). */
    private function saveCodes(Request $request, ?Model $row, string $missing)
    {
        if (!$row) {
            return $this->missing($missing);
        }
        $rules = collect($this->rules($row->getTable(), $row))->filter(fn ($r, $k) => str_starts_with($k, 'codes'))->all();
        $data = $request->validate($rules, $this->messages());
        $row->forceFill($this->storedCodes($data['codes'] ?? []))->save();

        return response()->json(['success' => 'کدها ذخیره شد.', 'codes' => $this->codes($row)]);
    }

    private function rebuilt(array $result, string $column, int $id)
    {
        $counts = $this->offerCounts($column, $id)[$id] ?? ['total' => 0, 'live' => 0];
        $message = $result['created'] || $result['updated']
            ? sprintf('%s ردیف تازه ساخته و %s ردیف به‌روز شد.', number_format($result['created']), number_format($result['updated']))
            : 'پیشنهادها از قبل به‌روز بودند؛ چیزی تغییر نکرد.';

        return response()->json(['success' => $message, 'offers' => $counts] + $result);
    }

    /** Offers per service or country: all rows, and those on sale (status 1 with a price). */
    private function offerCounts(string $column, ?int $id = null): array
    {
        return Virtual_Products::query()
            ->when($id, fn ($q) => $q->where($column, $id))
            ->select($column, DB::raw('count(*) as total'), DB::raw('sum(status = 1 and toman_price > 0) as live'))
            ->groupBy($column)->get()
            ->mapWithKeys(fn ($r) => [(int) $r->$column => ['total' => (int) $r->total, 'live' => (int) $r->live]])->all();
    }

    /** live: on sale · waiting: no price yet (or the price check hid it) · gone: the provider no longer lists it. */
    private function state(Virtual_Products $p): string
    {
        if ((int) $p->status === -1) {
            return 'gone';
        }
        return (int) $p->status === 1 && (float) $p->toman_price > 0 ? 'live' : 'waiting';
    }

    /** What the old settings screen's «مشخصات» tab counted, plus the offers actually built and on sale. */
    private function stats(): array
    {
        $services = Virtual_Service::get(array_keys(VirtualNumberOffers::LABELS));
        $countries = Virtual_Country::get(array_keys(VirtualNumberOffers::LABELS));
        $offers = Virtual_Products::select('type', DB::raw('count(*) as total'), DB::raw('sum(status = 1 and toman_price > 0) as live'), DB::raw('max(updated_at) as last'))
            ->groupBy('type')->get()->keyBy('type');
        $jdf = new Jdf();

        return collect(VirtualNumberOffers::LABELS)->map(function ($label, $key) use ($services, $countries, $offers, $jdf) {
            $s = $services->filter(fn ($r) => VirtualNumberOffers::offered($r->$key))->count();
            $c = $countries->filter(fn ($r) => VirtualNumberOffers::offered($r->$key))->count();
            $o = $offers[$key] ?? null;
            return [
                'key' => $key,
                'label' => $label,
                'server' => VirtualNumberOffers::PROVIDERS[$key] ?? null,
                'services' => $s,
                'countries' => $c,
                'pairs' => $s * $c,
                'offers' => (int) ($o->total ?? 0),
                'live' => (int) ($o->live ?? 0),
                'lastUpdate' => $o?->last ? $jdf->jdate('Y/m/d H:i', strtotime((string) $o->last)) : null,
            ];
        })->values()->all();
    }

    private function regions(): array
    {
        return Category::where('type', 'virtual-number-category')->orderBy('id')->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name])->all();
    }

    private function flag(?string $code): ?string
    {
        return $code ? '/image/svg/' . strtolower($code) . '.svg' : null;
    }

    /** Stored paths are relative ("image/…"), root-relative or old absolute URLs; the admin needs a usable src. */
    private function asset(?string $path): ?string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return null;
        }
        $path = str_replace(['http://localhost:8000/', 'https://shopmajazi.com/'], '/', $path);
        return preg_match('#^(https?:)?//#', $path) || str_starts_with($path, '/') ? $path : '/' . $path;
    }

    /**
     * An image path for the editor: relative paths ("upload/country/x.jpg") get a leading slash so the preview loads
     * under /admin/app; old "http://localhost:8000/…" URLs (a dead dev host) become root-relative; other full URLs stay.
     * The storefront and old pages accept both forms.
     */
    private function editable(?string $path): string
    {
        $path = str_replace('http://localhost:8000/', '/', trim((string) $path));
        return $path === '' || preg_match('#^(https?:)?//|^/#', $path) ? $path : '/' . $path;
    }

    /** «شماره مجازی کشور روسیه» → «کشور روسیه»; « سایت 1668 -1» → «سایت 1668». */
    private function plainName(?string $name): string
    {
        return trim(preg_replace('/\s*-\d+$/u', '', str_replace('شماره مجازی', '', (string) $name)));
    }

    /** Toman price rounded the way the storefront shows it (last two digits 00 or 50). */
    private function price($toman): int
    {
        return (int) str_replace(',', '', CovertVirtualNumberPrice(number_format((float) $toman)));
    }

    private function missing(string $message)
    {
        return response()->json(['error' => [$message]], 404);
    }

    private function messages(): array
    {
        return [
            'required' => 'این فیلد را پر کنید.',
            'string' => 'متن وارد کنید.',
            'integer' => 'یک عدد صحیح بنویسید.',
            'boolean' => 'مقدار نامعتبر است.',
            'min' => 'مقدار کمتر از حد مجاز است.',
            'max' => 'طولانی‌تر یا بزرگ‌تر از حد مجاز است.',
            'content.max' => 'حداکثر ۲۵۵ حرف (این ستون در پایگاه داده کوتاه است).',
            'metaDesc.max' => 'حداکثر ۲۵۵ حرف.',
            'slug.regex' => 'فقط حروف کوچک انگلیسی، عدد، نقطه و خط تیره (مثل telegram).',
            'slug.unique' => 'این نامک قبلاً استفاده شده است.',
            'codes.*.regex' => 'کد نباید فاصله داشته باشد.',
            'codes.land.max' => 'کد Land حداکثر ۱۱ حرف است.',
            'flagCode.regex' => 'کد دوحرفی کشور بنویسید (مثل RU یا gb-eng).',
            'regionId.exists' => 'منطقه را از فهرست انتخاب کنید.',
            'page.faq.*.q.required' => 'سؤال را بنویسید.',
            'page.faq.*.a.required' => 'پاسخ را بنویسید.',
        ];
    }
}
