<?php

namespace App\Services;

use App\Models\Pages;

/*
 * The H1, meta title, meta description and share (Open Graph) image of the site's main pages, set in
 * React admin › SEO. Stored in the old `pages` table, where admin › pages already kept them: `h1`, and in `seo`
 * (JSON) meta_title, meta_desc and meta_image. Pages that already had a row (`legacy` id, used by the old site and
 * its settings screens) keep using it, so what was set there carries over; the others get a row, found by `slug`,
 * the first time they are saved. Empty values mean "use the page's own text".
 * Read by the Next.js storefront (GET /api/v1/storefront/seo, Persian site) and by the old about / contact / FAQ
 * pages, which Laravel still renders.
 */
class PageSeo
{
    public const PAGES = [
        'virtual-number' => ['label' => 'شماره مجازی', 'path' => '/virtual-number', 'group' => 'شماره‌ها', 'legacy' => 1],
        'google-voice' => ['label' => 'گوگل ویس', 'path' => '/google-voice', 'group' => 'شماره‌ها', 'legacy' => 4],
        'rent-number' => ['label' => 'اجاره شماره', 'path' => '/rent-number', 'group' => 'شماره‌ها'],
        'apple-id' => ['label' => 'اپل آیدی (صفحه اصلی)', 'path' => '/apple-id', 'group' => 'اپل آیدی', 'legacy' => 3],
        'apple-id-usa' => ['label' => 'اپل آیدی آماده', 'path' => '/apple-id/usa', 'group' => 'اپل آیدی'],
        'apple-id-custom' => ['label' => 'اپل آیدی سفارشی', 'path' => '/apple-id/custom', 'group' => 'اپل آیدی'],
        'accounts' => ['label' => 'اشتراک‌ها', 'path' => '/accounts', 'group' => 'اشتراک‌ها', 'legacy' => 7],
        'telegram-stars' => ['label' => 'استارز تلگرام', 'path' => '/telegram/stars', 'group' => 'تلگرام'],
        'telegram-premium' => ['label' => 'پریمیوم تلگرام', 'path' => '/telegram/premium', 'group' => 'تلگرام'],
        'telegram-ads' => ['label' => 'تبلیغات تلگرام', 'path' => '/telegram/ads', 'group' => 'تلگرام'],
        'virtual-service' => ['label' => 'خدمات مجازی', 'path' => '/virtual-service', 'group' => 'خدمات مجازی', 'legacy' => 6],
        'about-us' => ['label' => 'درباره ما', 'path' => '/about-us', 'group' => 'صفحه‌های سایت',
            'default' => ['h1' => 'درباره ما', 'title' => 'درباره ما | شاپ مجازی', 'description' => 'درباره شاپ مجازی بیشتر بدانید']],
        'contact-us' => ['label' => 'تماس با ما', 'path' => '/contact-us', 'group' => 'صفحه‌های سایت',
            'default' => ['h1' => 'تماس با ما', 'title' => 'تماس با ما | شاپ مجازی', 'description' => 'جهت تماس با ما میتوانید از طریق راه ها ارتباطی اقدام نمایید']],
        'faq' => ['label' => 'سؤالات متداول', 'path' => '/faq', 'group' => 'صفحه‌های سایت',
            'default' => ['h1' => 'سوالات متداول', 'title' => 'سوالات متداول | شاپ مجازی', 'description' => 'در این صفحه به سوالاتی که ممکن است برای شما پیش بیاید، پاسخ داده ایم']],
    ];

    public static function exists(string $Key): bool
    {
        return isset(self::PAGES[$Key]);
    }

    /** @return array{h1: ?string, title: ?string, description: ?string, image: ?string} what the admin set (null = page's own) */
    public function get(string $Key): array
    {
        return $this->read($this->rows()[$Key] ?? null);
    }

    /** Every page, keyed like PAGES, with what the admin set. */
    public function all(): array
    {
        $Rows = $this->rows();
        return collect(self::PAGES)->map(fn($Page, $Key) => $this->read($Rows[$Key] ?? null))->all();
    }

    /** The page's `pages` row, if it has one yet (e.g. for screens that edit its long text or FAQ). */
    public function row(string $Key): ?Pages
    {
        return $this->rows()[$Key] ?? null;
    }

    /** What a page shows: the admin's values, else the page's own (old Laravel pages). */
    public function resolved(string $Key): array
    {
        $Default = self::PAGES[$Key]['default'] ?? [];
        return collect($this->get($Key))->map(fn($Value, $Field) => $Value ?? ($Default[$Field] ?? null))->all();
    }

    /* Saves one page. Other keys in `seo` (the old editor's custom head HTML) are kept. */
    public function save(string $Key, array $Data): void
    {
        $Row = $this->rows()[$Key] ?? null;
        if (is_null($Row)) {
            $Row = new Pages();
            $Row->forceFill(['title' => self::PAGES[$Key]['label'], 'slug' => $Key, 'long_detail' => '', 'related_blogs' => '']);
        }
        $Seo = json_decode((string)$Row->seo, true);
        $Seo = is_array($Seo) ? $Seo : [];
        $Clean = fn($Value) => ($Value = trim((string)$Value)) === '' ? null : $Value;
        $Seo['meta_title'] = $Clean($Data['title'] ?? null);
        $Seo['meta_desc'] = $Clean($Data['description'] ?? null);
        $Seo['meta_image'] = $Clean($Data['image'] ?? null);
        $Row->h1 = $Clean($Data['h1'] ?? null);
        $Row->seo = json_encode($Seo, JSON_UNESCAPED_UNICODE);
        $Row->save();
    }

    // The `pages` row of every page that has one: a legacy page's own row (by id), else the one saved under its key
    // (also for a legacy page whose old row doesn't exist, e.g. on a fresh database).
    private function rows(): array
    {
        $Legacy = collect(self::PAGES)->pluck('legacy')->filter()->values()->all();
        $Rows = Pages::whereIn('id', $Legacy)->orWhereIn('slug', array_keys(self::PAGES))->get();
        $Found = [];
        foreach (self::PAGES as $Key => $Page) {
            $Found[$Key] = (isset($Page['legacy']) ? $Rows->firstWhere('id', $Page['legacy']) : null)
                ?? $Rows->first(fn($Row) => $Row->slug === $Key && !in_array($Row->id, $Legacy));
        }
        return array_filter($Found);
    }

    private function read(?Pages $Row): array
    {
        $Seo = $Row ? json_decode((string)$Row->seo, true) : null;
        $Seo = is_array($Seo) ? $Seo : [];
        $Value = fn($V) => is_string($V) && trim($V) !== '' ? trim($V) : null;
        return [
            'h1' => $Value($Row?->h1),
            'title' => $Value($Seo['meta_title'] ?? null),
            'description' => $Value($Seo['meta_desc'] ?? null),
            'image' => $Value($Seo['meta_image'] ?? null),
        ];
    }
}
