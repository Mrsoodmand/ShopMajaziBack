<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The public storefront API the Next.js site reads for social media services (/api/v1/storefront/virtual-services*).
 * The test database has no other service categories, so the response holds only what each test creates.
 */
class VirtualServicesTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Dollar at 200,000 toman; the services rate (#20) adds 10% → 220,000.
        DB::table('currency')->insert(['id' => 1, 'faName' => 'دلار', 'sell' => '200000', 'buy' => 200000, 'type' => 'original']);
        DB::table('currency')->insert(['id' => 20, 'faName' => 'دلار خدمات', 'sell' => '10', 'buy' => 10, 'type' => '1', 'sell_action' => '1', 'sell_cal' => 'percent']);
        DB::table('categories')->where('type', 'product')->delete();
    }

    private function platform(string $slug, array $attrs = []): int
    {
        return DB::table('categories')->insertGetId(array_merge([
            'name' => 'پلتفرم ' . $slug, 'enName' => ucfirst($slug), 'slug' => $slug, 'type' => 'product', 'status' => 0,
        ], $attrs));
    }

    private function service(int $platform, string $slug, array $attrs = []): int
    {
        return DB::table('products')->insertGetId(array_merge([
            'type' => 'service', 'status' => 1, 'country_id' => $platform, 'slug' => $slug, 'price' => '1',
            'detail' => json_encode(['name' => 'سرویس ' . $slug, 'enName' => 'Service ' . $slug]),
            'filds' => json_encode([['type' => 'text', 'title' => 'لینک', 'col' => '12', 'filed_name' => 'link', 'select_value' => 'null']]),
        ], $attrs));
    }

    public function test_list_follows_admin_order_and_hides_hidden_or_empty_platforms(): void
    {
        $tg = $this->platform('telegram', ['sort' => 2, 'color' => '#0ea5e9', 'featured' => 1, 'tagline_en' => 'Members, views', 'image' => 'upload/services/tg.png']);
        $ig = $this->platform('instagram', ['sort' => 1]);
        $hidden = $this->platform('hidden', ['status' => 1]);
        $this->platform('empty');
        $this->service($tg, 'members');
        $this->service($ig, 'followers');
        $this->service($hidden, 'secret');

        $cats = $this->getJson('/api/v1/storefront/virtual-services')->assertOk()->json('data.categories');

        $this->assertSame(['instagram', 'telegram'], array_column($cats, 'slug'));
        $this->assertSame('#0ea5e9', $cats[1]['color']);
        $this->assertTrue($cats[1]['featured']);
        $this->assertSame('Members, views', $cats[1]['tagline_en']);
        $this->assertSame('/upload/services/tg.png', $cats[1]['logo']);
        $this->assertSame('Telegram', $cats[1]['en_name']);
        $this->assertSame('Service members', $cats[1]['services'][0]['en_name']);
    }

    public function test_prices_per_thousand_for_dollar_and_count_priced_services(): void
    {
        $tg = $this->platform('telegram');
        $this->service($tg, 'views', ['price' => '0.8']);                                   // 0.8 $ × 220,000 = 176,000
        $this->service($tg, 'members', ['price_per_100' => 2400, 'min_count' => 100, 'max_count' => 5000]); // 2,400 × 10

        $cat = $this->getJson('/api/v1/storefront/virtual-services')->assertOk()->json('data.categories.0');
        $prices = collect($cat['services'])->pluck('price_per_1000', 'slug');

        $this->assertSame(176000, $prices['views']);
        $this->assertSame(24000, $prices['members']);
        $this->assertSame(24000, $cat['from_per_1000']);
    }

    public function test_switched_off_plans_are_not_offered(): void
    {
        $tg = $this->platform('telegram');
        $id = $this->service($tg, 'members');
        DB::table('plans')->insert([
            ['product_id' => (string) $id, 'name' => '500', 'count' => 500, 'status' => 1],
            ['product_id' => (string) $id, 'name' => '1000', 'count' => 1000, 'status' => 0],
        ]);

        $listed = $this->getJson('/api/v1/storefront/virtual-services')->json('data.categories.0.services.0.plans');
        $this->assertSame([500], array_column($listed, 'count'));

        $page = $this->getJson('/api/v1/storefront/virtual-services/telegram')->assertOk()->json('data.services.0.plans');
        $this->assertSame([500], array_column($page, 'count'));
    }

    public function test_category_page_carries_presentation_fields(): void
    {
        $tg = $this->platform('telegram', ['color' => '#0ea5e9', 'tagline' => 'ممبر و بازدید', 'tagline_en' => 'Members and views']);
        $this->service($tg, 'members');

        $this->getJson('/api/v1/storefront/virtual-services/telegram')->assertOk()
            ->assertJsonPath('data.category.color', '#0ea5e9')
            ->assertJsonPath('data.category.tagline', 'ممبر و بازدید')
            ->assertJsonPath('data.category.tagline_en', 'Members and views')
            ->assertJsonPath('data.services.0.fields.0.name', 'link');
    }
}
