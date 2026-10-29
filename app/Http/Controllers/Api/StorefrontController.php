<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\GoogleVoice;
use App\Services\AppleIds;
use App\Services\GoogleVoiceNumbers;
use App\Services\PageSeo;
use App\Services\VirtualServiceOrders;
use App\Models\Currency;
use App\Models\Pages;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/*
 * Read-only JSON endpoints for the Next.js storefront (frontend/).
 * Prices are computed here with the same helpers the Blade pages use,
 * so the frontend never re-implements pricing.
 */
class StorefrontController extends Controller
{
    use ApiResponse;

    /* صفحه اصلی */
    public function home(): JsonResponse
    {
        $Blogs = Blog::where('status', 2)->where('type', 'blog')->orderby('id', 'DESC')
            ->select('title', 'slug', 'short_detail', 'image_big', 'created_at')->take(12)->get()
            ->map(fn($blog) => [
                'title' => $blog->title,
                'slug' => $blog->slug,
                'excerpt' => strip_tags((string)$blog->short_detail),
                'image' => $this->assetPath($blog->image_big),
                'created_at' => optional($blog->created_at)->toIso8601String(),
            ]);

        $Categories = $this->serviceCategories();

        return $this->success([
            'categories' => $Categories,
            'featured' => $Categories->flatMap(fn($category) => array_slice($category['services'], 0, 2))->take(8)->values(),
            'blogs' => $Blogs,
        ]);
    }

    /* لندینگ خدمات مجازی */
    public function virtualServices(): JsonResponse
    {
        $Page = Pages::find(6);

        return $this->success([
            'page' => is_null($Page) ? null : [
                'title' => $Page->title,
                'h1' => $Page->h1,
                'content' => $Page->long_detail,
                'seo' => $this->decode($Page->seo),
                'faq' => $this->decode($Page->faq),
            ],
            'categories' => $this->serviceCategories(true),
        ]);
    }

    /* لندینگ شماره مجازی: سرویس ها و کشورها به همراه تعداد و کمترین قیمت */
    public function virtualNumbers(): JsonResponse
    {
        $Page = Pages::find(1);
        $Services = Virtual_Service::where('status', 1)->orderby('sort', 'ASC')->get(['id', 'faName', 'image', 'special']);
        $Offers = $this->numberOffers()->whereIn('service_id', $Services->pluck('id'))
            ->get(['service_id', 'country_id', 'countryName', 'countryImage', 'country_sort', 'toman_price']);
        $Countries = Virtual_Country::whereIn('id', $Offers->pluck('country_id')->unique())->get(['id', 'faName'])->keyBy('id');

        $summary = fn($rows, $key) => [
            'count' => $rows->pluck($key)->unique()->count(),
            'min_price' => $this->numberPrice($rows->min('toman_price')),
        ];

        $ServiceList = $Services->map(function ($service) use ($Offers, $summary) {
            $rows = $Offers->where('service_id', $service->id);
            return $rows->isEmpty() ? null : [
                'id' => $service->id,
                'name' => $this->numberName($service->faName),
                'image' => $this->assetPath($service->image),
                'special' => (bool)$service->special,
            ] + $summary($rows, 'country_id');
        })->filter()->values();

        $CountryList = $Offers->groupBy('country_id')->map(function ($rows, $countryId) use ($Countries, $summary) {
            $first = $rows->first();
            return [
                'id' => (int)$countryId,
                'name' => $this->numberName(optional($Countries->get($countryId))->faName ?? $first->countryName),
                'image' => $this->assetPath($first->countryImage),
                'sort' => (int)$first->country_sort,
            ] + $summary($rows, 'service_id');
        })->sortBy('sort')->values()->map(fn($country) => collect($country)->except('sort')->all());

        return $this->success([
            'page' => is_null($Page) ? null : [
                'title' => $Page->title,
                'h1' => $Page->h1,
                'content' => $Page->long_detail,
                'seo' => $this->decode($Page->seo),
                'faq' => $this->decode($Page->faq),
            ],
            'services' => $ServiceList,
            'countries' => $CountryList,
        ]);
    }

    /* شماره های قابل خرید یک سرویس یا یک کشور (یک ردیف برای هر سرور) */
    public function virtualNumberOffers(Request $request): JsonResponse
    {
        if (!$request->filled('service') && !$request->filled('country')) {
            return $this->error('service or country is required', 422, [
                'service' => ['service or country is required'],
            ]);
        }

        $query = $this->numberOffers()->whereIn('service_id', Virtual_Service::where('status', 1)->pluck('id'));
        if ($request->filled('service')) {
            $query->where('service_id', intval($request->input('service')));
        } else {
            $query->where('country_id', intval($request->input('country')));
        }

        $servers = ['pva' => 1, 'man' => 2, 'active' => 3];
        $Offers = $query->orderby('country_sort', 'ASC')->get(['id', 'service_id', 'country_id', 'type', 'toman_price'])
            ->map(fn($product) => [
                'id' => $product->id,
                'service_id' => $product->service_id,
                'country_id' => $product->country_id,
                'server' => $servers[$product->type] ?? 1,
                'price' => $this->numberPrice($product->toman_price),
            ]);

        return $this->success(['offers' => $Offers]);
    }

    /* لیست مقالات وبلاگ یا اخبار: ?type=blog|news&page=&q= */
    public function blog(Request $request): JsonResponse
    {
        $Type = $request->input('type') == 'news' ? 'news' : 'blog';
        $query = Blog::where('status', 2)->where('type', $Type);
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], mb_substr(trim($request->input('q')), 0, 60)) . '%');
        }
        $Posts = $query->orderby('id', 'DESC')
            ->select('id', 'title', 'slug', 'type', 'short_detail', 'image', 'image_big', 'view', 'created_at')
            ->selectRaw('CHAR_LENGTH(detail) as detail_length')
            ->paginate(18);

        return $this->success([
            'type' => $Type,
            'posts' => collect($Posts->items())->map(fn($post) => $this->postSummary($post))->values(),
            'pagination' => [
                'page' => $Posts->currentPage(),
                'pages' => $Posts->lastPage(),
                'total' => $Posts->total(),
            ],
            'popular' => $this->popularPosts($Type, 5),
            'news' => Blog::where('status', 2)->where('type', 'news')->orderby('id', 'DESC')->take(8)->get(['title', 'slug'])
                ->map(fn($post) => ['title' => $post->title, 'slug' => $post->slug]),
        ]);
    }

    /* جزئیات یک مقاله یا خبر */
    public function blogPost($slug): JsonResponse
    {
        $Blog = Blog::where('status', 2)->where('slug', $slug)->first();
        if (is_null($Blog)) {
            return $this->error('مطلب مورد نظر یافت نشد.', 404);
        }

        $Comments = Comment::where('type', 'blog')->where('post_id', $Blog->id)->where('status', 1)
            ->orderby('id', 'DESC')->take(50)->get(['id', 'parent_id', 'name', 'text', 'created_at']);

        return $this->success([
            'post' => $this->postSummary($Blog) + [
                'id' => $Blog->id,
                'content' => (string)$Blog->detail,
                'meta_title' => $Blog->meta_title,
                'meta_desc' => $Blog->meta_desc,
                'updated_at' => optional($Blog->updated_at)->toIso8601String(),
                'faq' => collect($this->decode($Blog->faq) ?? [])->map(fn($item) => [
                    'q' => $item['question'] ?? $item['q'] ?? null,
                    'a' => $item['answer'] ?? $item['a'] ?? null,
                ])->filter(fn($item) => $item['q'] && $item['a'])->values(),
            ],
            'related' => Blog::where('status', 2)->where('type', $Blog->type)->where('id', '!=', $Blog->id)->orderby('id', 'DESC')->take(3)
                ->select('id', 'title', 'slug', 'type', 'short_detail', 'image', 'image_big', 'view', 'created_at')
                ->selectRaw('CHAR_LENGTH(detail) as detail_length')->get()
                ->map(fn($post) => $this->postSummary($post)),
            'popular' => $this->popularPosts($Blog->type, 4, $Blog->id),
            'comments' => $Comments->map(fn($comment) => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id ? (int)$comment->parent_id : null,
                'name' => $comment->name,
                'text' => $comment->text,
                'created_at' => optional($comment->created_at)->toIso8601String(),
            ]),
        ]);
    }

    /* شمارش بازدید (صفحات Next.js کش می شوند، پس بازدید از سمت مرورگر ثبت می شود) */
    public function blogView($slug): JsonResponse
    {
        Blog::where('status', 2)->where('slug', $slug)->increment('view');
        return $this->success(null);
    }

    /* صفحه دسته بندی خدمات مجازی */
    public function serviceCategory($slug): JsonResponse
    {
        $Category = Category::where('type', 'product')->where('slug', $slug)->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 1))->first(); // hidden in admin: no page
        if (is_null($Category)) {
            return $this->error('دسته بندی مورد نظر یافت نشد.', 404);
        }

        $Currency = CalculatorSellPrice(Currency::find(20));
        $Products = Product::where('type', 'service')->where('status', 1)->where('country_id', $Category->id)->orderby('id', 'DESC')->get();
        $Plans = Plan::whereIn('product_id', $Products->pluck('id'))->where('status', 1)->orderby('count', 'ASC')->get()->groupBy('product_id');

        $Services = $Products->map(fn($product) => $this->categoryService($product, $Category, $Plans, $Currency))->values();

        return $this->success([
            'category' => [
                'id' => $Category->id,
                'slug' => $Category->slug,
                'name' => $Category->name,
                'en_name' => $Category->enName,
                'tagline' => $Category->tagline,
                'tagline_en' => $Category->tagline_en,
                'color' => $Category->color,
                'image' => $this->assetPath($Category->image),
                'short_detail' => $Category->short_detail,
                'content' => $Category->long_detail,
                'seo' => ['meta_title' => $Category->meta_title, 'meta_desc' => $Category->meta_desc],
            ],
            'services' => $Services,
        ]);
    }

    /* صفحه یک سرویس مجازی */
    public function serviceDetail($category, $slug): JsonResponse
    {
        $Category = Category::where('type', 'product')->where('slug', $category)->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 1))->first();
        $Product = is_null($Category) ? null : Product::where('type', 'service')->where('status', 1)
            ->where('country_id', $Category->id)->where('slug', $slug)->first();
        if (is_null($Product)) {
            return $this->error('سرویس مورد نظر یافت نشد.', 404);
        }

        $Currency = CalculatorSellPrice(Currency::find(20));
        $Siblings = Product::where('type', 'service')->where('status', 1)->where('country_id', $Category->id)
            ->where('id', '!=', $Product->id)->orderby('id', 'DESC')->take(8)->get();
        $Plans = Plan::whereIn('product_id', $Siblings->pluck('id')->push($Product->id))->where('status', 1)->orderby('count', 'ASC')->get()->groupBy('product_id');
        $Detail = $this->decode($Product->detail) ?? [];
        $Comments = Comment::where('type', 'service')->where('post_id', $Product->id)->where('status', 1)
            ->orderby('id', 'DESC')->take(50)->get(['id', 'parent_id', 'name', 'text', 'created_at']);

        return $this->success([
            'category' => ['id' => $Category->id, 'slug' => $Category->slug, 'name' => $Category->name],
            'service' => $this->categoryService($Product, $Category, $Plans, $Currency) + [
                'short_detail' => $Detail['short_detail'] ?? null,
                'content' => $Detail['detail'] ?? null,
                'seo' => ['meta_title' => $Detail['meta_title'] ?? null, 'meta_desc' => $Detail['meta_desc'] ?? null],
                'faq' => collect($this->decode($Product->faq) ?? [])->map(fn($item) => [
                    'q' => $item['question'] ?? $item['q'] ?? null,
                    'a' => $item['answer'] ?? $item['a'] ?? null,
                ])->filter(fn($item) => $item['q'] && $item['a'])->values(),
            ],
            'related' => $Siblings->map(fn($product) => $this->categoryService($product, $Category, $Plans, $Currency))->values(),
            'comments' => $Comments->map(fn($comment) => [
                'id' => $comment->id,
                'parent_id' => $comment->parent_id ? (int)$comment->parent_id : null,
                'name' => $comment->name,
                'text' => $comment->text,
                'created_at' => optional($comment->created_at)->toIso8601String(),
            ]),
        ]);
    }

    /* گوگل ویس: شماره های موجود و قیمت */
    /*
     * For the Persian site, which shows every price in Toman: the dollar rate (currencies #1, sell) to convert
     * prices that are still in dollars, and the real lowest price (Toman) of each live service, keyed by its page.
     */
    public function prices(AppleIds $Apple): JsonResponse
    {
        $From = [];
        $Prices = $Apple->prices();
        if ($Prices['ready'] > 0) {
            $From['/apple-id/usa'] = $From['/apple-id'] = $Prices['ready'];
        }
        if ($Prices['custom'] > 0) {
            $From['/apple-id/custom'] = $Prices['custom'];
            $From['/apple-id'] = min($From['/apple-id'] ?? PHP_INT_MAX, $Prices['custom']);
        }
        $Min = $this->numberOffers()->min('toman_price');
        if ($Min) {
            $From['/virtual-number'] = $From['/virtual-numbers'] = $this->numberPrice($Min);
        }
        try {
            $Service = app(GoogleVoiceNumbers::class);
            $Gv = $Service->enabled() ? array_column($Service->list(), 'price') : [];
            if ($Gv) {
                $From['/google-voice'] = (int)min($Gv);
            }
        } catch (\Throwable $e) {
            report($e); // Numberland down: the menu falls back to the converted dollar price
        }

        return $this->success([
            'usd' => (int)optional(Currency::find(1))->sell,
            'from' => $From,
        ]);
    }

    /*
     * H1, meta title, meta description and share image the admin set per page (React admin › SEO), keyed like
     * PageSeo::PAGES. Only what was set comes back; the storefront keeps its own text for the rest.
     */
    public function seo(PageSeo $Seo): JsonResponse
    {
        return $this->success([
            'pages' => (object)collect($Seo->all())
                ->map(fn($Page) => array_filter(['image' => $this->assetPath($Page['image'])] + $Page, fn($Value) => $Value !== null))
                ->filter()->all(),
        ]);
    }

    /* Apple ID prices (Toman, admin › Apple ID) and how many ready ones are in stock. */
    public function appleId(AppleIds $Apple): JsonResponse
    {
        $Prices = $Apple->prices();
        return $this->success([
            'ready_price' => $Prices['ready'],
            'custom_price' => $Prices['custom'],
            'stock' => $Apple->stock(),
            'max_qty' => AppleIds::MAX_QTY,
            'packs' => AppleIds::PACKS,
        ]);
    }

    public function googleVoice(): JsonResponse
    {
        $Page = Pages::find(4);
        $Service = app(GoogleVoiceNumbers::class);
        $Unavailable = false;
        $Disabled = !$Service->enabled(); // switched off in admin › Google Voice: show no numbers
        try {
            $Numbers = $Disabled ? [] : array_map(fn($n) => ['id' => $n['id'], 'number' => $n['number'], 'price' => $n['price'], 'round' => $n['round']], $Service->list());
        } catch (\Throwable $e) {
            report($e);
            [$Numbers, $Unavailable] = [[], true];
        }

        return $this->success([
            'page' => is_null($Page) ? null : [
                'title' => $Page->title,
                'h1' => $Page->h1,
                'short_detail' => $Page->short_detail,
                'content' => $Page->long_detail,
                'image' => $this->assetPath($Page->image),
                'seo' => $this->decode($Page->seo),
                'faq' => $this->decode($Page->faq),
            ],
            // Numbers come from Numberland (App\Services\GoogleVoiceNumbers), each with its own price in Toman.
            'numbers' => $Numbers,
            'unavailable' => $Unavailable,
            'disabled' => $Disabled,
        ]);
    }

    /* یک سرویس به همان شکلی که صفحه دسته و صفحه سرویس استفاده می کنند */
    private function categoryService($product, $Category, $Plans, $Currency): array
    {
        $Detail = $this->decode($product->detail) ?? [];
        $Count = VirtualServiceOrders::countPricing($product, (float)$Currency);
        return [
            'id' => $product->id,
            'slug' => $product->slug,
            'name' => $Detail['name'] ?? $product->faName,
            'en_name' => $Detail['enName'] ?? null,
            'url' => '/virtual-service/' . $Category->slug . '/' . $product->slug,
            'image' => $this->assetPath($Detail['image_gallery'] ?? null),
            'price_per_1000' => $Count ? intdiv(1000, $Count['step']) * $Count['price'] : $this->servicePrice($product->price, 1000, $Currency),
            // Any amount: a multiple of `step` between min and max at `price` toman per step; null = packages.
            'count_pricing' => $Count,
            'plans' => $Count ? [] : collect($Plans->get($product->id, []))->map(fn($plan) => [
                'id' => $plan->id,
                'token' => Encode($plan->id), // OrderController::SubmitVirtualService expects the encoded id
                'name' => $plan->name,
                'count' => (int)$plan->count,
                'price' => $this->servicePrice($product->price, $plan->count, $Currency),
            ])->values(),
            'fields' => collect($this->decode($product->filds) ?? [])->map(fn($field) => [
                'name' => $field['filed_name'],
                'title' => $field['title'],
                'type' => $field['type'] == 'select_box' ? 'select' : 'text',
                'options' => $field['type'] == 'select_box' ? array_values(array_filter(array_map('trim', explode(',', (string)$field['select_value'])))) : [],
            ])->values(),
            // Admin › service › notes ("⌛️ مدت تحویل:" → "تحویل آنی"), shown beside the order.
            'notes' => collect(is_array($Notes = $this->decode($product->notes)) ? $Notes : [])
                ->map(fn($value, $key) => ['label' => trim((string)$key), 'value' => trim((string)$value)])
                ->filter(fn($note) => $note['label'] !== '' && $note['value'] !== '')->values(),
        ];
    }

    private function postSummary($post): array
    {
        // ~200 words a minute; list queries only know the HTML length (≈ 9 characters per word incl. markup)
        $words = isset($post->detail_length)
            ? $post->detail_length / 9
            : count(preg_split('/\s+/u', trim(strip_tags((string)$post->detail)), -1, PREG_SPLIT_NO_EMPTY));
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'type' => $post->type,
            'excerpt' => trim(html_entity_decode(strip_tags((string)$post->short_detail))),
            'image' => $this->assetPath($post->image_big ?: $post->image),
            'thumb' => $this->assetPath($post->image ?: $post->image_big),
            'views' => (int)$post->view,
            'reading_minutes' => max(1, (int)round($words / 200)),
            'created_at' => optional($post->created_at)->toIso8601String(),
        ];
    }

    private function popularPosts($Type, $take, $exceptId = null)
    {
        return Blog::where('status', 2)->where('type', $Type)->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->orderby('view', 'DESC')->take($take)->get(['title', 'slug', 'image', 'image_big', 'view'])
            ->map(fn($post) => [
                'title' => $post->title,
                'slug' => $post->slug,
                'thumb' => $this->assetPath($post->image ?: $post->image_big),
                'views' => (int)$post->view,
            ]);
    }

    /* فقط محصولاتی که در صفحه خرید قابل خرید هستند (GetSingleNumberDetail) */
    private function numberOffers()
    {
        return Virtual_Products::where('status', 1)->where('toman_price', '>', 0);
    }

    /* همان گرد کردن قیمت صفحه شماره مجازی (GetNumbers) */
    private function numberPrice($tomanPrice): int
    {
        return (int)str_replace(',', '', CovertVirtualNumberPrice(number_format($tomanPrice)));
    }

    /* «شماره مجازی تلگرام -1» → «تلگرام» */
    private function numberName($name): string
    {
        return trim(preg_replace('/\s*-\d+$/u', '', str_replace('شماره مجازی', '', (string)$name)));
    }

    /* دسته بندی های خدمات مجازی به همراه سرویس ها، به ترتیب و تنظیمات پنل مدیریت (پنهان ها حذف می شوند) */
    private function serviceCategories(bool $withPlans = false)
    {
        $Currency = CalculatorSellPrice(Currency::find(20));
        $Categories = Category::where('type', 'product')->where(fn($q) => $q->whereNull('status')->orWhere('status', '!=', 1))
            ->orderby('sort')->orderby('id')->get();
        $Products = Product::where('type', 'service')->where('status', 1)
            ->whereIn('country_id', $Categories->pluck('id'))->orderby('id', 'DESC')->get();
        $Plans = Plan::whereIn('product_id', $Products->pluck('id'))->where('status', 1)->orderby('count', 'ASC')->get()->groupBy('product_id');

        return $Categories->map(function ($category) use ($Products, $Plans, $Currency, $withPlans) {
            $services = $Products->where('country_id', $category->id)->map(function ($product) use ($category, $Plans, $Currency, $withPlans) {
                $service = $this->categoryService($product, $category, $Plans, $Currency);
                if (!$withPlans) {
                    unset($service['plans'], $service['fields'], $service['notes']);
                }
                return $service;
            })->values()->all();

            return [
                'id' => $category->id,
                'slug' => $category->slug,
                'name' => $category->name,
                'en_name' => $category->enName,
                'tagline' => $category->tagline,
                'tagline_en' => $category->tagline_en,
                'logo' => $this->assetPath($category->image),
                'color' => $category->color,
                'featured' => (bool)$category->featured,
                'sort' => (int)$category->sort,
                'from_per_1000' => collect($services)->pluck('price_per_1000')->filter()->min(),
                'services' => $services,
            ];
        })->filter(fn($category) => count($category['services']) > 0)->values();
    }

    /* همان محاسبه قیمت OrderController::Factor برای سرویس های مجازی */
    private function servicePrice($productPrice, $count, $Currency): int
    {
        return VirtualServiceOrders::dollarPrice($productPrice, (int)$count, (float)$Currency);
    }

    private function decode($value)
    {
        if (is_array($value) || is_null($value)) {
            return $value;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    /* مسیر نسبی فایل ها تا از طریق همان دامنه (پروکسی Next.js) لود شوند */
    private function assetPath($path): ?string
    {
        if (empty($path)) {
            return null;
        }
        return preg_match('#^https?://#', $path) ? $path : '/' . ltrim($path, '/');
    }
}
