<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/*
 * Cache management for admin › Cache (Admin\CacheApiController).
 *
 * Two caches matter:
 * - Laravel's own (CACHE_DRIVER, file): the Google Voice list, locks, rate limits… plus the compiled config, routes
 *   and views in bootstrap/cache and storage/framework/views.
 * - The Next.js storefront's data cache: every storefront() fetch (Front/src/lib/backend.ts) is kept for a number of
 *   seconds per section and tagged `storefront` + `storefront:<section>`. Clearing calls Front's /api/revalidate
 *   with STOREFRONT_REVALIDATE_SECRET.
 *
 * Admins may set each section's seconds (null = the time in Front's code); Front reads them from
 * GET api/v1/storefront/cache-times. Settings and the last clear of each kind are kept in the `settings` row named
 * "cache", outside the cache, so clearing doesn't lose them.
 */
class CacheManager
{
    /* Storefront sections: key (first part of the API path) => [label, seconds in Front's code, note]. */
    public const SECTIONS = [
        'google-voice' => ['گوگل ویس', 0, 'موجودی زنده نامبرلند؛ صفحه اصلی گوگل ویس کش نمی‌شود (صفحه فرود: ۶۰ ثانیه).'],
        'virtual-numbers' => ['شماره مجازی', 300, null],
        'virtual-services' => ['خدمات مجازی', 60, null],
        'apple-id' => ['اپل آیدی', 0, null],
        'accounts' => ['اکانت‌ها و تلگرام پریمیوم', 60, null],
        'telegram-stars' => ['استارز تلگرام', 60, null],
        'prices' => ['قیمت‌ها (صفحه اصلی و صفحه‌ها)', 600, null],
        'picks' => ['آیتم‌های منو و صفحه اصلی', 3600, 'با ذخیره اکانت، خدمت، پلتفرم یا محصول وریفای در ادمین فوراً تازه می‌شود.'],
        'blog' => ['بلاگ', 3600, null],
        'comments' => ['نظرات', 120, null],
        'testimonials' => ['نظرات صفحه اصلی', 600, null],
        'seo' => ['سئوی صفحه‌ها', 60, null],
    ];

    public const KINDS = ['app', 'config', 'route', 'view', 'gv', 'storefront'];

    /* Laravel's copy of the menu and home items (Api\StorefrontController::picks). */
    public const PICKS_KEY = 'storefront-picks';

    private const MAX_SECONDS = 86400;

    /* Section => seconds an admin set; sections left to Front's code are absent. */
    public function times(): array
    {
        return array_intersect_key($this->store()['times'] ?? [], self::SECTIONS);
    }

    public function saveTimes(array $Times): void
    {
        $Clean = [];
        foreach (self::SECTIONS as $Key => $_) {
            $V = $Times[$Key] ?? null;
            if ($V !== null && $V !== '') {
                $Clean[$Key] = max(0, min(self::MAX_SECONDS, (int)$V));
            }
        }
        $this->save(['times' => $Clean] + $this->store());
        // The storefront re-reads the times within a minute; drop its copy so they apply now.
        $this->revalidate('cache-times');
    }

    public function status(): array
    {
        $Store = $this->store();
        $DataDir = storage_path('framework/cache/data');
        $ViewsDir = storage_path('framework/views');
        return [
            'driver' => config('cache.default'),
            'appSize' => config('cache.default') === 'file' ? $this->dirSize($DataDir) : null,
            'appFiles' => config('cache.default') === 'file' && File::isDirectory($DataDir) ? count(File::allFiles($DataDir)) : null,
            'configCached' => app()->configurationIsCached(),
            'routesCached' => app()->routesAreCached(),
            'viewsSize' => $this->dirSize($ViewsDir),
            'gvListCached' => Cache::has('gv-numberland-costs'),
            'storefrontReady' => (bool)config('services.storefront.revalidate_secret'),
            'cleared' => $Store['cleared'] ?? [],
        ];
    }

    /*
     * Clears one kind (see KINDS); for `storefront`, $Section limits it to one section. Returns [ok, message].
     * $By: the admin's name, kept with the time as "last cleared".
     */
    public function clear(string $Kind, ?string $Section, string $By): array
    {
        try {
            switch ($Kind) {
                case 'app':
                    Cache::flush();
                    break;
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'gv':
                    Cache::forget('gv-numberland-costs');
                    break;
                case 'storefront':
                    if ($Section !== null && !isset(self::SECTIONS[$Section])) {
                        return [false, 'این بخش وجود ندارد.'];
                    }
                    if ($Section === null || $Section === 'picks') {
                        Cache::forget(self::PICKS_KEY); // the storefront would refetch Laravel's own cached copy
                    }
                    [$Ok, $Message] = $this->revalidate($Section ? "storefront:$Section" : 'storefront');
                    if (!$Ok) {
                        return [false, $Message];
                    }
                    break;
                default:
                    return [false, 'این نوع کش وجود ندارد.'];
            }
        } catch (\Throwable $e) {
            report($e);
            return [false, 'پاک کردن کش انجام نشد: ' . $e->getMessage()];
        }
        $Store = $this->store();
        $Store['cleared'][$Kind . ($Section ? ":$Section" : '')] = ['at' => now()->toIso8601String(), 'by' => $By];
        $this->save($Store);
        Log::info('Cache cleared', ['kind' => $Kind, 'section' => $Section, 'by' => $By]);
        return [true, 'کش پاک شد.'];
    }

    /*
     * Drops one storefront section's cached data after its content changed (a post saved in admin). Never in the
     * way of the save itself: a storefront that doesn't answer is logged, and the section's time still applies.
     */
    public function refreshStorefront(string $Section): void
    {
        [$Ok, $Message] = $this->revalidate("storefront:$Section", 3);
        if (!$Ok) {
            Log::warning('Storefront cache not refreshed', ['section' => $Section, 'reason' => $Message]);
        }
    }

    /*
     * An account, service, social platform or verification product was saved in admin: the header menu and the home
     * page's items may have changed (a name, logo, menu/home switch, or the product going live or off). Rebuilds
     * Laravel's copy and drops the storefront's, so the next visit shows the change. The "prices" section too, so
     * a newly added item gets its price at once.
     */
    public function refreshPicks(): void
    {
        Cache::forget(self::PICKS_KEY);
        $this->refreshStorefront('picks');
        $this->refreshStorefront('prices');
    }

    /* Asks the storefront (Next.js) to drop its cached data with this tag: [ok, message]. */
    private function revalidate(string $Tag, int $Timeout = 10): array
    {
        $Secret = config('services.storefront.revalidate_secret');
        if (!$Secret) {
            return [false, 'STOREFRONT_REVALIDATE_SECRET در .env بک‌اند تنظیم نشده است.'];
        }
        $Url = rtrim(config('services.storefront.internal_url') ?: StorefrontUrl(), '/') . '/api/revalidate';
        try {
            $Res = Http::timeout($Timeout)->withHeaders(['X-Revalidate-Secret' => $Secret])->post($Url, ['tag' => $Tag]);
        } catch (\Throwable $e) {
            return [false, 'سایت (Next.js) پاسخ نداد: ' . $e->getMessage()];
        }
        return $Res->successful() ? [true, 'ok'] : [false, 'سایت (Next.js) کش را پاک نکرد (HTTP ' . $Res->status() . ').'];
    }

    private function store(): array
    {
        return json_decode((string)optional(Setting::where('name', 'cache')->first())->key, true) ?: [];
    }

    private function save(array $Data): void
    {
        $Row = Setting::where('name', 'cache')->first() ?? (new Setting())->forceFill(['name' => 'cache']);
        $Row->key = json_encode($Data, JSON_UNESCAPED_UNICODE);
        $Row->save();
    }

    private function dirSize(string $Dir): int
    {
        if (!File::isDirectory($Dir)) {
            return 0;
        }
        $Size = 0;
        foreach (File::allFiles($Dir) as $F) {
            $Size += $F->getSize();
        }
        return $Size;
    }
}
