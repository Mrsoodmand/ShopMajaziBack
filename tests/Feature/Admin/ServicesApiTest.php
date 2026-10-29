<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ServicesApiTest extends AdminTestCase
{
    private function platform(array $attrs = []): int
    {
        return DB::table('categories')->insertGetId(array_merge([
            'name' => 'تلگرام', 'enName' => 'telegram', 'slug' => 'tg-' . uniqid(), 'type' => 'product', 'status' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    /** Dollar (#1) at 200,000 toman and the services rate (#20) = dollar + 10%. */
    private function rates(): void
    {
        DB::table('currency')->insert(['id' => 1, 'faName' => 'دلار', 'sell' => '200000', 'buy' => 200000, 'type' => 'original']);
        DB::table('currency')->insert([
            ['id' => 20, 'faName' => 'دلار خدمات', 'sell' => '10', 'buy' => 10, 'type' => '1', 'sell_action' => '1', 'sell_cal' => 'percent'],
        ]);
    }

    private function servicePayload(int $categoryId, array $over = []): array
    {
        return array_merge([
            'categoryId' => $categoryId,
            'name' => 'ممبر واقعی تلگرام',
            'enName' => 'Real Telegram members',
            'slug' => 'buy-telegram-members-' . uniqid(),
            'live' => true,
            'short' => 'ممبر واقعی برای کانال',
            'pricing' => 'plans',
            'dollarPer1000' => 1.2,
            'plans' => [['name' => '500 ممبر', 'count' => 500, 'live' => true], ['name' => '1000 ممبر', 'count' => 1000, 'live' => true]],
            'fields' => [
                ['name' => 'link', 'title' => 'لینک کانال', 'type' => 'text', 'options' => []],
                ['name' => 'speed', 'title' => 'سرعت', 'type' => 'select', 'options' => ['عادی', 'سریع']],
            ],
            'notes' => [['label' => 'زمان تحویل', 'value' => 'آنی']],
            'faq' => [['q' => 'ریزش دارد؟', 'a' => 'تا ۳۰ روز جبران می‌شود.']],
        ], $over);
    }

    public function test_requires_the_virtual_services_permission(): void
    {
        $this->actingAsAdmin($this->admin(super: false));
        $this->getJson('/admin/api/service-categories')->assertForbidden();
    }

    public function test_platforms_are_listed_in_admin_order_with_service_counts(): void
    {
        $this->actingAsAdmin();
        $second = $this->platform(['name' => 'اینستاگرام', 'sort' => 2]);
        $first = $this->platform(['name' => 'تلگرام', 'sort' => 1, 'featured' => 1, 'color' => '#0ea5e9']);
        DB::table('products')->insert([
            ['type' => 'service', 'country_id' => $first, 'status' => 1, 'slug' => 'a-' . uniqid()],
            ['type' => 'service', 'country_id' => $first, 'status' => 0, 'slug' => 'b-' . uniqid()],
        ]);

        $rows = collect($this->getJson('/admin/api/service-categories')->assertOk()->json('categories'))->whereIn('id', [$first, $second])->values();
        $this->assertSame([$first, $second], $rows->pluck('id')->all());
        $this->assertSame(2, $rows[0]['services']);
        $this->assertSame(1, $rows[0]['liveServices']);
        $this->assertTrue($rows[0]['featured']);
        $this->assertSame('#0ea5e9', $rows[0]['color']);
    }

    public function test_platform_create_validates_and_saves(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/service-categories', ['name' => '', 'slug' => 'Bad Slug'])
            ->assertStatus(422)->assertJsonValidationErrors(['name', 'enName', 'slug']);

        $slug = 'tiktok-' . uniqid();
        $id = $this->postJson('/admin/api/service-categories', [
            'name' => 'تیک‌تاک', 'enName' => 'TikTok', 'slug' => $slug, 'color' => '#0f172a', 'sort' => 4, 'featured' => true, 'visible' => true,
        ])->assertOk()->json('category.id');

        $row = DB::table('categories')->find($id);
        $this->assertSame('product', $row->type);
        $this->assertSame('#0f172a', $row->color);
        $this->assertSame(1, (int) $row->featured);
        $this->assertSame(0, (int) $row->status);

        // Hiding sets status 1; the slug must stay unique among platforms.
        $this->postJson("/admin/api/service-categories/$id", ['name' => 'تیک‌تاک', 'enName' => 'TikTok', 'slug' => $slug, 'visible' => false])->assertOk();
        $this->assertSame(1, (int) DB::table('categories')->where('id', $id)->value('status'));
        $this->postJson('/admin/api/service-categories', ['name' => 'x', 'enName' => 'x', 'slug' => $slug])->assertStatus(422)->assertJsonValidationErrors('slug');
    }

    public function test_service_is_saved_in_the_legacy_shape(): void
    {
        $this->actingAsAdmin();
        $cat = $this->platform();

        $id = $this->postJson('/admin/api/services', $this->servicePayload($cat))->assertOk()->json('id');
        $p = Product::find($id);

        $this->assertSame('service', $p->type);
        $this->assertSame($cat, (int) $p->country_id);
        $this->assertSame(1, (int) $p->status);
        $this->assertSame('1.2', (string) $p->price);
        $this->assertNull($p->price_per_100);
        $detail = json_decode($p->detail, true);
        $this->assertSame('ممبر واقعی تلگرام', $detail['name']);
        $this->assertSame('ممبر واقعی برای کانال', $detail['short_detail']);
        $fields = json_decode($p->filds, true);
        $this->assertSame(['type' => 'select_box', 'title' => 'سرعت', 'col' => '12', 'filed_name' => 'speed', 'select_value' => 'عادی,سریع'], $fields[1]);
        $this->assertSame('null', $fields[0]['select_value']);
        $this->assertSame(['زمان تحویل' => 'آنی'], json_decode($p->notes, true));
        $this->assertSame([['question' => 'ریزش دارد؟', 'answer' => 'تا ۳۰ روز جبران می‌شود.']], json_decode($p->faq, true));
        $this->assertSame([500, 1000], DB::table('plans')->where('product_id', $id)->orderBy('count')->pluck('count')->map(fn ($c) => (int) $c)->all());
    }

    public function test_editor_round_trip_and_plan_sync(): void
    {
        $this->actingAsAdmin();
        $cat = $this->platform();
        $id = $this->postJson('/admin/api/services', $this->servicePayload($cat))->assertOk()->json('id');

        $service = $this->getJson("/admin/api/services/$id")->assertOk()->json('service');
        $this->assertSame('plans', $service['pricing']);
        $this->assertSame(['عادی', 'سریع'], $service['fields'][1]['options']);
        $this->assertSame('زمان تحویل', $service['notes'][0]['label']);

        // Keep the first plan (renamed), drop the second, add a new one.
        $payload = $this->servicePayload($cat, [
            'slug' => $service['slug'],
            'plans' => [
                ['id' => $service['plans'][0]['id'], 'name' => '500 ممبر ویژه', 'count' => 500, 'live' => true],
                ['name' => '5000 ممبر', 'count' => 5000, 'live' => false],
            ],
        ]);
        $this->postJson("/admin/api/services/$id", $payload)->assertOk();

        $plans = DB::table('plans')->where('product_id', $id)->orderBy('count')->get();
        $this->assertCount(2, $plans);
        $this->assertSame($service['plans'][0]['id'], $plans[0]->id);
        $this->assertSame('500 ممبر ویژه', $plans[0]->name);
        $this->assertSame(0, (int) $plans[1]->status);
    }

    public function test_any_amount_is_priced_in_dollars_per_500_with_limits_in_steps_of_500(): void
    {
        $this->actingAsAdmin();
        $cat = $this->platform();

        $this->postJson('/admin/api/services', $this->servicePayload($cat, ['pricing' => 'count', 'plans' => []]))
            ->assertStatus(422)->assertJsonValidationErrors(['dollarPer500', 'minCount', 'maxCount']);
        $this->postJson('/admin/api/services', $this->servicePayload($cat, [
            'pricing' => 'count', 'dollarPer500' => 0, 'minCount' => 700, 'maxCount' => 20049, 'plans' => [],
        ]))->assertStatus(422)->assertJsonValidationErrors(['dollarPer500', 'minCount', 'maxCount'])
            ->assertJsonPath('errors.minCount.0', 'مضربی از ۵۰۰ بنویسید (مثل ۵۰۰، ۱۰۰۰، ۱۵۰۰).');
        $this->postJson('/admin/api/services', $this->servicePayload($cat, [
            'pricing' => 'count', 'dollarPer500' => 0.4, 'minCount' => 0, 'maxCount' => 500, 'plans' => [],
        ]))->assertStatus(422)->assertJsonValidationErrors(['minCount']);

        $id = $this->postJson('/admin/api/services', $this->servicePayload($cat, [
            'pricing' => 'count', 'dollarPer500' => 0.4, 'minCount' => 1000, 'maxCount' => 20000, 'plans' => [],
        ]))->assertOk()->json('id');

        $p = Product::find($id);
        $this->assertEquals(0.4, (float) $p->price_per_500);
        $this->assertNull($p->price_per_100);
        $this->assertSame(1000, (int) $p->min_count);
        $this->assertSame(20000, (int) $p->max_count);

        $this->getJson("/admin/api/services/$id")->assertOk()
            ->assertJsonPath('service.pricing', 'count')
            ->assertJsonPath('service.dollarPer500', 0.4)
            ->assertJsonPath('service.minCount', 1000);

        // Back to packages: the per-500 price is cleared.
        $this->postJson("/admin/api/services/$id", $this->servicePayload($cat, ['slug' => $p->slug]))->assertOk();
        $this->assertNull(Product::find($id)->price_per_500);
    }

    public function test_missing_service_or_platform_is_a_persian_404(): void
    {
        $this->actingAsAdmin();
        $this->getJson('/admin/api/services/999999999')->assertNotFound()->assertJsonPath('error.0', 'سرویس پیدا نشد.');
        $this->postJson('/admin/api/service-categories/999999999', ['name' => 'x'])->assertNotFound()->assertJsonPath('error.0', 'پلتفرم پیدا نشد.');
    }

    public function test_plan_pricing_needs_at_least_one_plan(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/services', $this->servicePayload($this->platform(), ['plans' => []]))
            ->assertStatus(422)->assertJsonValidationErrors('plans');
    }

    public function test_list_shows_toman_price_per_thousand(): void
    {
        $this->actingAsAdmin();
        $this->rates();
        $cat = $this->platform();
        $this->postJson('/admin/api/services', $this->servicePayload($cat, ['dollarPer1000' => 1]))->assertOk();
        // 0.25 $ per 500 × 220,000 = 55,000 toman per 500 → 110,000 per 1,000
        $this->postJson('/admin/api/services', $this->servicePayload($cat, ['pricing' => 'count', 'dollarPer500' => 0.25, 'minCount' => 500, 'maxCount' => 1000, 'plans' => []]))->assertOk();

        $res = $this->getJson("/admin/api/services?category=$cat")->assertOk();
        $this->assertEquals(220000, $res->json('rate'));
        $prices = collect($res->json('services'))->pluck('tomanPer1000')->sort()->values()->all();
        $this->assertSame([110000, 220000], $prices);
    }

    public function test_image_upload_returns_a_public_path(): void
    {
        $this->actingAsAdmin();
        $path = $this->post('/admin/api/uploads/service-image', ['file' => UploadedFile::fake()->image('Telegram Logo.png', 64, 64)], $this->ajax())
            ->assertOk()->json('path');

        $this->assertMatchesRegularExpression('#^/upload/services/telegram-logo-\d{14}\.png$#', $path);
        $this->assertFileExists(public_path(ltrim($path, '/')));
        @unlink(public_path(ltrim($path, '/')));

        $this->post('/admin/api/uploads/service-image', ['file' => UploadedFile::fake()->create('notes.pdf', 10)], $this->ajax())->assertStatus(422);
    }
}
