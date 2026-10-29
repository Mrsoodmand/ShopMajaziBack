<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Plan;
use App\Models\Product;
use App\Services\VirtualServiceOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Virtual services (social media growth) for the React admin: platforms (categories of type "product"),
 * their services (products of type "service", linked by country_id) and each service's plans.
 * Data is stored in the same columns and JSON shapes the old admin used, so the storefront API
 * (Api\StorefrontController) and order creation (v2\OrderController) read it unchanged.
 */
class ServiceApiController extends Controller
{
    /* ---------- Platforms ---------- */

    public function categories()
    {
        $categories = Category::where('type', 'product')->orderBy('sort')->orderBy('id')->get();
        $counts = Product::where('type', 'service')->whereIn('country_id', $categories->pluck('id'))
            ->select('country_id', DB::raw('count(*) as total'), DB::raw('sum(status = 1) as live'))
            ->groupBy('country_id')->get()->keyBy('country_id');

        return response()->json([
            'categories' => $categories->map(fn (Category $c) => $this->category($c) + [
                'services' => (int) ($counts[$c->id]->total ?? 0),
                'liveServices' => (int) ($counts[$c->id]->live ?? 0),
            ])->values(),
        ]);
    }

    public function saveCategory(Request $request, $id = null)
    {
        $category = $id ? Category::where('type', 'product')->find($id) : new Category();
        if (!$category) {
            return $this->missing('پلتفرم پیدا نشد.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'enName' => 'required|string|max:50',
            'slug' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('categories', 'slug')->where('type', 'product')->ignore($category->id)],
            'short' => 'nullable|string',
            'tagline' => 'nullable|string|max:255',
            'taglineEn' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort' => 'nullable|integer',
            'featured' => 'boolean',
            'visible' => 'boolean',
            'metaTitle' => 'nullable|string|max:255',
            'metaDesc' => 'nullable|string',
        ], $this->messages());

        $category->forceFill([
            'type' => 'product',
            'name' => $data['name'],
            'enName' => $data['enName'],
            'slug' => $data['slug'],
            'short_detail' => $data['short'] ?? null,
            'tagline' => $data['tagline'] ?? null,
            'tagline_en' => $data['taglineEn'] ?? null,
            'long_detail' => $data['content'] ?? null,
            'image' => $data['image'] ?? null,
            'color' => $data['color'] ?? null,
            'sort' => $data['sort'] ?? 0,
            'featured' => (bool) ($data['featured'] ?? false),
            // The storefront has always listed every product category; 1 hides one now.
            'status' => ($data['visible'] ?? true) ? 0 : 1,
            'meta_title' => $data['metaTitle'] ?? null,
            'meta_desc' => $data['metaDesc'] ?? null,
        ])->save();

        return response()->json(['success' => $id ? 'پلتفرم ذخیره شد.' : 'پلتفرم ساخته شد.', 'category' => $this->category($category)]);
    }

    /* ---------- Services ---------- */

    public function services(Request $request)
    {
        $query = Product::where('type', 'service');
        if ($request->filled('category')) {
            $query->where('country_id', $request->integer('category'));
        }
        $rate = $this->rate();
        $plans = Plan::whereIn('product_id', (clone $query)->pluck('id'))->select('product_id', DB::raw('count(*) as c'))->groupBy('product_id')->pluck('c', 'product_id');

        return response()->json([
            'rate' => $rate,
            'services' => $query->orderByDesc('status')->orderBy('sort')->orderByDesc('id')->get()->map(function (Product $p) use ($rate, $plans) {
                $detail = $this->decode($p->detail);
                return [
                    'id' => $p->id,
                    'name' => $detail['name'] ?? $p->faName,
                    'enName' => $detail['enName'] ?? null,
                    'slug' => $p->slug,
                    'categoryId' => (int) $p->country_id,
                    'live' => (int) $p->status === 1,
                    'pricing' => $this->pricing($p),
                    'dollarPer500' => $p->price_per_500 !== null ? (float) $p->price_per_500 : null,
                    'dollarPer1000' => is_numeric($p->price) ? (float) $p->price : null,
                    'tomanPer1000' => $this->tomanPer1000($p, $rate),
                    'plans' => (int) ($plans[$p->id] ?? 0),
                ];
            })->values(),
        ]);
    }

    public function service($id)
    {
        $p = Product::where('type', 'service')->find($id);
        if (!$p) {
            return $this->missing('سرویس پیدا نشد.');
        }
        $detail = $this->decode($p->detail);
        $notes = $this->decode($p->notes);

        return response()->json([
            'rate' => $this->rate(),
            'service' => [
                'id' => $p->id,
                'categoryId' => (int) $p->country_id,
                'name' => $detail['name'] ?? $p->faName,
                'enName' => $detail['enName'] ?? '',
                'slug' => $p->slug,
                'live' => (int) $p->status === 1,
                'short' => $detail['short_detail'] ?? '',
                'content' => $detail['detail'] ?? '',
                'image' => $detail['image_gallery'] ?? '',
                'metaTitle' => $detail['meta_title'] ?? '',
                'metaDesc' => $detail['meta_desc'] ?? '',
                'pricing' => $this->pricing($p),
                'dollarPer1000' => is_numeric($p->price) ? (float) $p->price : null,
                'dollarPer500' => $p->price_per_500 !== null ? (float) $p->price_per_500 : null,
                // Shown as multiples of 500 (older rows hold 100 / 10,000).
                'minCount' => VirtualServiceOrders::countRange($p->min_count, $p->max_count)[0],
                'maxCount' => VirtualServiceOrders::countRange($p->min_count, $p->max_count)[1],
                'commission' => $p->commission !== null ? (int) $p->commission : null,
                'plans' => Plan::where('product_id', $p->id)->orderBy('count')->orderBy('id')->get()->map(fn ($pl) => [
                    'id' => $pl->id,
                    'name' => $pl->name,
                    'count' => (int) $pl->count,
                    'live' => (int) $pl->status === 1,
                ])->values(),
                'fields' => collect($this->decode($p->filds))->map(fn ($f) => [
                    'name' => (string) ($f['filed_name'] ?? ''),
                    'title' => (string) ($f['title'] ?? ''),
                    'type' => ($f['type'] ?? '') === 'select_box' ? 'select' : 'text',
                    'options' => ($f['type'] ?? '') === 'select_box'
                        ? array_values(array_filter(array_map('trim', explode(',', (string) ($f['select_value'] ?? ''))))) : [],
                ])->values(),
                'notes' => collect(is_array($notes) ? $notes : [])->map(fn ($v, $k) => ['label' => (string) $k, 'value' => (string) $v])->values(),
                'faq' => collect($this->decode($p->faq))->map(fn ($f) => ['q' => $f['question'] ?? '', 'a' => $f['answer'] ?? ''])->values(),
            ],
        ]);
    }

    public function saveService(Request $request, $id = null)
    {
        $p = $id ? Product::where('type', 'service')->find($id) : new Product();
        if (!$p) {
            return $this->missing('سرویس پیدا نشد.');
        }
        $data = $request->validate([
            'categoryId' => ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'product')],
            'name' => 'required|string|max:255',
            'enName' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/',
                Rule::unique('products', 'slug')->where('type', 'service')->ignore($p->id)],
            'live' => 'boolean',
            'short' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|string|max:500',
            'metaTitle' => 'nullable|string|max:255',
            'metaDesc' => 'nullable|string',
            'pricing' => 'required|in:count,plans',
            'dollarPer1000' => 'required_if:pricing,plans|nullable|numeric|min:0',
            'dollarPer500' => 'required_if:pricing,count|nullable|numeric|gt:0|max:100000',
            'minCount' => 'required_if:pricing,count|nullable|integer|min:500|multiple_of:500',
            'maxCount' => 'required_if:pricing,count|nullable|integer|gte:minCount|max:10000000|multiple_of:500',
            'commission' => 'nullable|integer|min:0|max:100',
            'plans' => 'array',
            'plans.*.id' => 'nullable|integer',
            'plans.*.name' => 'required|string|max:255',
            'plans.*.count' => 'required|integer|min:1',
            'plans.*.live' => 'boolean',
            'fields' => 'array',
            'fields.*.name' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z_][A-Za-z0-9_]*$/'],
            'fields.*.title' => 'required|string|max:255',
            'fields.*.type' => 'required|in:text,select',
            'fields.*.options' => 'array',
            'fields.*.options.*' => 'string|max:100',
            'notes' => 'array',
            'notes.*.label' => 'required|string|max:100',
            'notes.*.value' => 'required|string|max:500',
            'faq' => 'array',
            'faq.*.q' => 'required|string',
            'faq.*.a' => 'required|string',
        ], $this->messages());

        if (($data['pricing'] === 'plans') && empty($data['plans'])) {
            return response()->json(['message' => 'دست‌کم یک پلن بسازید.', 'errors' => ['plans' => ['دست‌کم یک پلن بسازید؛ یا قیمت‌گذاری با تعداد دلخواه را انتخاب کنید.']]], 422);
        }

        DB::transaction(function () use ($p, $data) {
            $old = $this->decode($p->detail);
            $p->forceFill([
                'type' => 'service',
                'faName' => $data['name'],
                'slug' => $data['slug'],
                'country_id' => $data['categoryId'],
                'status' => ($data['live'] ?? false) ? 1 : 0,
                'commission' => $data['commission'] ?? null,
                'price' => $data['pricing'] === 'plans' ? (string) $data['dollarPer1000'] : ($p->price ?? '0'),
                // Any amount is priced in dollars per 500; the old toman-per-100 price is cleared either way.
                'price_per_500' => $data['pricing'] === 'count' ? (string) $data['dollarPer500'] : null,
                'price_per_100' => null,
                'min_count' => $data['pricing'] === 'count' ? (int) $data['minCount'] : ($p->min_count ?: 100),
                'max_count' => $data['pricing'] === 'count' ? (int) $data['maxCount'] : ($p->max_count ?: 10000),
                // Same keys the old admin wrote; keys this editor does not manage (seo, code) are kept.
                'detail' => json_encode(array_merge($old, [
                    'name' => $data['name'],
                    'enName' => $data['enName'],
                    'short_detail' => $data['short'] ?? '',
                    'detail' => $data['content'] ?? '',
                    'meta_title' => $data['metaTitle'] ?? '',
                    'meta_desc' => $data['metaDesc'] ?? '',
                    'image_gallery' => $data['image'] ?? '',
                ])),
                'filds' => json_encode(collect($data['fields'] ?? [])->map(fn ($f) => [
                    'type' => $f['type'] === 'select' ? 'select_box' : 'text',
                    'title' => $f['title'],
                    'col' => '12',
                    'filed_name' => $f['name'],
                    'select_value' => $f['type'] === 'select' ? implode(',', $f['options'] ?? []) : 'null',
                ])->values()),
                'notes' => json_encode((object) collect($data['notes'] ?? [])->mapWithKeys(fn ($n) => [$n['label'] => $n['value']])->all()),
                'faq' => json_encode(collect($data['faq'] ?? [])->map(fn ($f) => ['question' => $f['q'], 'answer' => $f['a']])->values()),
            ])->save();

            // Plans: update the ones sent, create new ones, delete the rest (as the old editor did).
            $keep = [];
            foreach ($data['plans'] ?? [] as $i => $row) {
                $plan = !empty($row['id']) ? Plan::where('product_id', $p->id)->find($row['id']) : null;
                $plan ??= new Plan();
                $plan->forceFill([
                    'product_id' => (string) $p->id,
                    'name' => $row['name'],
                    'count' => $row['count'],
                    'status' => ($row['live'] ?? true) ? 1 : 0,
                    'sort' => $i,
                    'type' => $plan->type ?? 'social',
                ])->save();
                $keep[] = $plan->id;
            }
            Plan::where('product_id', $p->id)->whereNotIn('id', $keep)->delete();
        });

        return response()->json(['success' => $id ? 'سرویس ذخیره شد.' : 'سرویس ساخته شد.', 'id' => $p->id]);
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
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'image';
        $name .= '-' . now()->format('YmdHis') . '.' . strtolower($file->getClientOriginalExtension());
        $file->move(public_path('upload/services'), $name);

        return response()->json(['path' => '/upload/services/' . $name]);
    }

    /* ---------- Helpers ---------- */

    private function missing(string $message)
    {
        return response()->json(['error' => [$message]], 404);
    }

    private function category(Category $c): array
    {
        return [
            'id' => $c->id,
            'name' => $c->name,
            'enName' => $c->enName,
            'slug' => $c->slug,
            'short' => $c->short_detail,
            'tagline' => $c->tagline,
            'taglineEn' => $c->tagline_en,
            'content' => $c->long_detail,
            'image' => $c->image,
            'color' => $c->color,
            'sort' => (int) $c->sort,
            'featured' => (bool) $c->featured,
            'visible' => (int) $c->status !== 1,
            'metaTitle' => $c->meta_title,
            'metaDesc' => $c->meta_desc,
        ];
    }

    /** Toman per dollar the storefront uses for service prices (currency #20), or null when it is not set up. */
    private function rate(): ?float
    {
        $currency = Currency::find(20);
        return $currency ? (float) CalculatorSellPrice($currency) : null;
    }

    /** 'count' (any amount in steps, VirtualServiceOrders::countPricing) or 'plans' (packages). */
    private function pricing(Product $p): string
    {
        return (float) $p->price_per_500 > 0 || (int) $p->price_per_100 > 0 ? 'count' : 'plans';
    }

    private function tomanPer1000(Product $p, ?float $rate): ?int
    {
        if ($count = VirtualServiceOrders::countPricing($p, (float) $rate)) {
            return $count['price'] ? intdiv(1000, $count['step']) * $count['price'] : null;
        }
        if (!$rate || !is_numeric($p->price)) {
            return null;
        }
        return (int) str_replace(',', '', CovertVirtualNumberPrice(number_format((float) $p->price * $rate)));
    }

    private function decode($value): array
    {
        $decoded = is_string($value) ? json_decode($value, true) : $value;
        return is_array($decoded) ? $decoded : [];
    }

    private function messages(): array
    {
        return [
            'required' => 'این فیلد را پر کنید.',
            'required_if' => 'این فیلد را پر کنید.',
            'slug.regex' => 'فقط حروف کوچک انگلیسی، عدد و خط تیره (مثل buy-telegram-members).',
            'slug.unique' => 'این نامک قبلاً استفاده شده است.',
            'color.regex' => 'رنگ را به شکل #0ea5e9 بنویسید.',
            'integer' => 'یک عدد صحیح بنویسید.',
            'numeric' => 'یک عدد بنویسید.',
            'min' => 'مقدار کمتر از حد مجاز است.',
            'max' => 'مقدار بیشتر از حد مجاز است.',
            'maxCount.gte' => 'حداکثر باید از حداقل بیشتر باشد.',
            'minCount.min' => 'حداقل سفارش دست‌کم ۵۰۰ عدد است.',
            'multiple_of' => 'مضربی از ۵۰۰ بنویسید (مثل ۵۰۰، ۱۰۰۰، ۱۵۰۰).',
            'dollarPer500.gt' => 'قیمت باید بیشتر از صفر باشد.',
            'fields.*.name.regex' => 'نام فیلد فقط حروف انگلیسی، عدد و _ (مثل link).',
            'categoryId.exists' => 'پلتفرم را انتخاب کنید.',
        ];
    }
}
