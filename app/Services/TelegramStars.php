<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;

/*
 * Telegram Stars, sold on the storefront's /telegram/stars page. Stored as one social-media service (products, type
 * "service", under the Telegram platform) so orders, refunds, invoices, /account and the cart work as for members and
 * views: an order waits in admin › orders until support sends the Stars and completes it.
 *
 * What makes it different (detail.stars = true): the admin sets a dollar price per Star (kept in `price` as dollars
 * per 1,000, like every service), ready plans (packs of Stars), and the customer may also type any amount between
 * min_count and max_count. Every price is per Star × amount, converted at the services rate and rounded as the site
 * rounds (VirtualServiceOrders::dollarPrice); a plan may carry its own profit (plans.profit_percent, Pricing::planRate)
 * instead of the services one. Admin: Admin\TelegramStarsController (React admin › تلگرام استارز).
 */
class TelegramStars
{
    public const SLUG = 'telegram-stars';
    public const FIELD = 'username';

    public function product(): ?Product
    {
        return Product::where('type', 'service')->where('slug', self::SLUG)->first();
    }

    public static function isStars(Product $Product): bool
    {
        return $Product->slug === self::SLUG;
    }

    public function platform(): ?Category
    {
        return Category::where('type', 'product')->where('slug', 'telegram')->first();
    }

    // Toman for `count` Stars at the current rate.
    public static function price(Product $Product, int $Count, ?float $Rate = null): int
    {
        return VirtualServiceOrders::dollarPrice((float)$Product->price, $Count, $Rate ?? Pricing::servicesRate());
    }

    // What the storefront page needs, or null when Stars are off (or not set up yet).
    public function storefront(): ?array
    {
        $Product = $this->product();
        $Platform = $this->platform();
        if (!$Product || (int)$Product->status !== 1 || (float)$Product->price <= 0 || !$Platform || (int)$Platform->status === 1) {
            return null;
        }
        $Rate = Pricing::servicesRate();
        $Plans = Plan::where('product_id', $Product->id)->where('status', 1)->orderBy('count')->get();
        return [
            'service_id' => $Product->id,
            'field' => self::FIELD,
            'min' => (int)$Product->min_count,
            'max' => (int)$Product->max_count,
            // The page prices any amount the way the server does: (dollars per 1,000 ÷ 1,000) × amount × rate,
            // rounded to the integer, then the last two digits to 00 or 50.
            'dollar_per_1000' => (float)$Product->price,
            'rate' => $Rate,
            'plans' => $Plans->map(fn(Plan $p) => [
                'id' => $p->id,
                'count' => (int)$p->count,
                'name' => (string)$p->name,
                // A plan with its own profit sells at its own rate (VirtualServiceOrders charges it the same way).
                'price' => self::price($Product, (int)$p->count, $p->profit_percent !== null ? Pricing::planRate($p, $Product) : $Rate),
            ])->values(),
        ];
    }
}
