<?php

namespace Tests\Feature\Storefront;

use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Buying social media services on the Next.js storefront (/api/v1/storefront/account/services/*): the order is
 * checked against the service as the admin set it up, stored in the old site's shape (order type 2) and paid from
 * the wallet or by card, so it lands in admin › orders like before.
 */
class ServiceOrdersTest extends TestCase
{
    use DatabaseTransactions;

    private int $platform;

    protected function setUp(): void
    {
        parent::setUp();
        // Dollar at 200,000 toman; the services rate (#20) adds 10% → 220,000.
        DB::table('currency')->insert(['id' => 1, 'faName' => 'دلار', 'sell' => '200000', 'buy' => 200000, 'type' => 'original']);
        DB::table('currency')->insert(['id' => 20, 'faName' => 'دلار خدمات', 'sell' => '10', 'buy' => 10, 'type' => '1', 'sell_action' => '1', 'sell_cal' => 'percent']);
        $this->platform = DB::table('categories')->insertGetId(['name' => 'تلگرام', 'enName' => 'Telegram', 'slug' => 'tg-test', 'type' => 'product', 'status' => 0]);
    }

    private function service(array $attrs = []): int
    {
        return DB::table('products')->insertGetId(array_merge([
            'type' => 'service', 'status' => 1, 'country_id' => $this->platform, 'slug' => 'members-' . uniqid(), 'price' => '0.8',
            'detail' => json_encode(['name' => 'ممبر تلگرام', 'enName' => 'Telegram members']),
            'filds' => json_encode([
                ['type' => 'text', 'title' => 'لینک کانال', 'col' => '12', 'filed_name' => 'link', 'select_value' => 'null'],
                ['type' => 'select_box', 'title' => 'سرعت', 'col' => '6', 'filed_name' => 'speed', 'select_value' => 'آرام,سریع'],
            ]),
        ], $attrs));
    }

    private function plan(int $service, int $count, int $status = 1): int
    {
        return DB::table('plans')->insertGetId(['product_id' => (string) $service, 'name' => "$count ممبر", 'count' => $count, 'status' => $status]);
    }

    private function user(int $wallet = 0, array $attrs = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'name' => 'خریدار', 'mobile' => '0912' . random_int(1000000, 9999999), 'password' => bcrypt('secret'),
            'wallet' => $wallet, 'blocked_wallet' => 0, 'status' => 0, 'wallet_blocked' => 0, 'bank_blocked' => 0, 'account_blocked' => 0,
        ], $attrs))->save();
        $this->actingAs($user, 'user');
        return $user;
    }

    private function fields(): array
    {
        return ['link' => 't.me/mychannel', 'speed' => 'سریع'];
    }

    public function test_guests_are_asked_to_sign_in(): void
    {
        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => 1])->assertStatus(401)->assertJsonPath('code', 'auth');
    }

    public function test_platform_page_lists_plans_fields_and_notes(): void
    {
        $id = $this->service(['notes' => json_encode(['⌛️ مدت تحویل:' => 'تحویل آنی', 'خالی' => ''])]);
        $this->plan($id, 1000);

        $service = $this->getJson('/api/v1/storefront/virtual-services/tg-test')->assertOk()->json('data.services.0');

        $this->assertSame(176000, $service['plans'][0]['price']); // 0.8 $ × 1,000 ÷ 1,000 × 220,000
        $this->assertSame(['link', 'speed'], array_column($service['fields'], 'name'));
        $this->assertSame(['آرام', 'سریع'], $service['fields'][1]['options']);
        $this->assertSame([['label' => '⌛️ مدت تحویل:', 'value' => 'تحویل آنی']], $service['notes']);
    }

    public function test_hidden_platforms_have_no_pages(): void
    {
        $id = $this->service(['slug' => 'members']);
        $this->plan($id, 1000);
        DB::table('categories')->where('id', $this->platform)->update(['status' => 1]);

        $this->getJson('/api/v1/storefront/virtual-services/tg-test')->assertNotFound();
        $this->getJson('/api/v1/storefront/virtual-services/tg-test/members')->assertNotFound();
    }

    public function test_wallet_buy_with_a_plan_pays_and_queues_the_order_for_admin(): void
    {
        $id = $this->service();
        $plan = $this->plan($id, 1000);
        $user = $this->user(500000);

        $res = $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 176000])
            ->assertOk()
            ->assertJsonPath('data.order.status', 'queued')
            ->assertJsonPath('data.order.count', 1000)
            ->assertJsonPath('data.order.paid_with', 'wallet')
            ->assertJsonPath('data.order.fields.0.value', 't.me/mychannel')
            ->assertJsonPath('data.wallet', 324000);

        $order = DB::table('orders')->find($res->json('data.order.id'));
        $this->assertSame('2', (string) $order->type);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Complete', $order->pay_status);
        $this->assertSame(324000, (int) $user->fresh()->wallet);
        $this->assertSame('Wallet', DB::table('payments')->where('id', $order->pay_id)->value('gate'));

        // Same item shape as the old site: the answers, then the plan.
        $detail = json_decode(DB::table('order_items')->where('order_id', $order->id)->value('detail'), true);
        $this->assertSame(['filed_name' => 'link', 'filed_type' => 'لینک کانال', 'filed_value' => 't.me/mychannel'], $detail[0]);
        $this->assertSame(['plan_id' => $plan, 'plan_name' => '1000 ممبر', 'count' => 1000], $detail['plan']);
    }

    public function test_any_amount_services_sell_in_steps_of_500_at_the_dollar_price(): void
    {
        // 0.4 $ per 500 × 220,000 = 88,000 toman per 500
        $id = $this->service(['price_per_500' => '0.4', 'min_count' => 1000, 'max_count' => 5000]);
        $this->user(500000);

        $page = $this->getJson('/api/v1/storefront/virtual-services/tg-test')->assertOk()->json('data.services.0');
        $this->assertSame(['step' => 500, 'price' => 88000, 'min' => 1000, 'max' => 5000], $page['count_pricing']);
        $this->assertSame(176000, $page['price_per_1000']);
        $this->assertSame([], $page['plans']);

        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'count' => 1500, 'fields' => $this->fields(), 'price' => 264000])
            ->assertOk()->assertJsonPath('data.order.count', 1500)->assertJsonPath('data.order.price', 264000)
            ->assertJsonPath('data.wallet', 236000);

        foreach ([1200, 500, 5500, 0] as $bad) {
            $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'count' => $bad, 'fields' => $this->fields()])
                ->assertStatus(422)->assertJsonPath('code', 'count_invalid')->assertJsonPath('data.step', 500);
        }
    }

    public function test_services_priced_by_the_old_admin_in_toman_per_100_still_sell(): void
    {
        $id = $this->service(['price_per_100' => 2400, 'min_count' => 100, 'max_count' => 5000]);
        $this->user(100000);

        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'count' => 1500, 'fields' => $this->fields(), 'price' => 36000])
            ->assertOk()->assertJsonPath('data.order.count', 1500)->assertJsonPath('data.order.price', 36000);
        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'count' => 150, 'fields' => $this->fields()])
            ->assertStatus(422)->assertJsonPath('code', 'count_invalid');
    }

    public function test_a_changed_price_buys_nothing_and_returns_the_new_one(): void
    {
        $id = $this->service();
        $plan = $this->plan($id, 1000);
        $this->user(500000);

        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 150000])
            ->assertStatus(409)->assertJsonPath('code', 'price_changed')->assertJsonPath('data.price', 176000);
        $this->assertSame(0, DB::table('order_items')->where('product_id', $id)->count());
    }

    public function test_fields_are_checked_against_the_service(): void
    {
        $id = $this->service();
        $plan = $this->plan($id, 1000);
        $this->user(500000);
        $buy = fn(array $fields) => $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $plan, 'fields' => $fields]);

        $buy(['speed' => 'سریع'])->assertStatus(422)->assertJsonPath('code', 'fields_invalid')->assertJsonPath('data.field', 'link');
        $buy(['link' => 'ab', 'speed' => 'سریع'])->assertStatus(422)->assertJsonPath('data.field', 'link');
        $buy(['link' => 't.me/mychannel', 'speed' => 'خیلی سریع'])->assertStatus(422)->assertJsonPath('data.field', 'speed');
    }

    public function test_switched_off_plans_hidden_platforms_and_short_wallets_buy_nothing(): void
    {
        $id = $this->service();
        $off = $this->plan($id, 1000, 0);
        $on = $this->plan($id, 500);
        $user = $this->user(1000);

        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $off, 'fields' => $this->fields()])
            ->assertStatus(422)->assertJsonPath('code', 'plan_invalid');
        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $on, 'fields' => $this->fields()])
            ->assertStatus(400)->assertJsonPath('code', 'balance')->assertJsonPath('data.price', 88000);

        DB::table('categories')->where('id', $this->platform)->update(['status' => 1]);
        $user->forceFill(['wallet' => 500000])->save();
        $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $on, 'fields' => $this->fields()])
            ->assertStatus(404)->assertJsonPath('code', 'service_unavailable');

        $this->assertSame(0, DB::table('order_items')->where('product_id', $id)->count());
    }

    public function test_card_payment_goes_through_the_bank_and_back_to_the_page(): void
    {
        $id = $this->service();
        $plan = $this->plan($id, 1000);
        $user = $this->user(0);

        $res = $this->postJson('/api/v1/storefront/account/services/pay-card', [
            'service_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 176000, 'return' => '/virtual-service/tg-test',
        ])->assertOk();
        $orderId = $res->json('data.order_id');
        $this->assertStringContainsString('/dev-bank/', $res->json('data.url'));
        $this->assertSame('waiting to pay', DB::table('orders')->where('id', $orderId)->value('status'));

        $authority = DB::table('payments')->where('order_id', $orderId)->value('authority');
        $back = $this->get(route('user.service-bank-callback', ['Authority' => $authority, 'Status' => 'OK'], false));

        $back->assertRedirect();
        $this->assertStringEndsWith('/virtual-service/tg-test?pay=ok&order=' . $orderId, $back->headers->get('Location'));
        $this->assertSame('pending', DB::table('orders')->where('id', $orderId)->value('status'));
        $this->assertSame(0, (int) $user->fresh()->wallet);

        $this->getJson("/api/v1/storefront/account/services/orders/$orderId")->assertOk()
            ->assertJsonPath('data.order.status', 'queued')->assertJsonPath('data.order.paid_with', 'card');
    }

    public function test_order_view_is_only_for_its_buyer(): void
    {
        $id = $this->service();
        $plan = $this->plan($id, 1000);
        $this->user(500000);
        $orderId = $this->postJson('/api/v1/storefront/account/services/buy', ['service_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields()])->json('data.order.id');

        $this->user(0);
        $this->getJson("/api/v1/storefront/account/services/orders/$orderId")->assertStatus(404)->assertJsonPath('code', 'not_found');
    }
}
