<?php

namespace Tests\Feature\Storefront;

use App\Models\Plan;
use App\Services\AccountOrders;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Subscriptions / foreign accounts on the Next.js storefront: the catalog (/api/v1/storefront/accounts) shows what
 * the admin set up in React admin › accounts, and buying (/api/v1/storefront/account/subscriptions/*) makes the old
 * site's order type 7, paid from the wallet or by card, so it lands in admin › orders like before.
 */
class SubscriptionsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Dollar at 200,000 toman; the services rate (#20) adds 10% → 220,000.
        DB::table('currency')->insert(['id' => 1, 'faName' => 'دلار', 'sell' => '200000', 'buy' => 200000, 'type' => 'original']);
        DB::table('currency')->insert(['id' => 20, 'faName' => 'دلار خدمات', 'sell' => '10', 'buy' => 10, 'type' => '1', 'sell_action' => '1', 'sell_cal' => 'percent']);
    }

    private function product(array $detail = [], array $attrs = []): int
    {
        return DB::table('products')->insertGetId(array_merge([
            'type' => 'accounts', 'status' => 1, 'slug' => 'spotify-' . uniqid(), 'price' => '0', 'faName' => 'خرید اشتراک اسپاتیفای',
            'detail' => json_encode(array_merge(['name' => 'خرید اشتراک اسپاتیفای', 'type' => '1', 'price_type' => '1', 'meta_title' => 'اسپاتیفای', 'long_detail' => '<p>متن</p>'], $detail)),
            'filds' => json_encode([
                ['type' => 'text', 'title' => 'آدرس ایمیل', 'col' => null, 'filed_name' => 'email', 'select_value' => 'null'],
                ['type' => 'text', 'title' => 'کلمه عبور', 'col' => null, 'filed_name' => 'password', 'select_value' => 'null'],
            ]),
            'notes' => json_encode(['زمان فعال سازی' => 'کمتر از 4 ساعت', 'a' => 'a']),
        ], $attrs));
    }

    private function plan(int $product, string $name, string $price, array $attrs = []): int
    {
        return DB::table('plans')->insertGetId(array_merge(['product_id' => (string) $product, 'type' => 'accounts', 'name' => $name, 'price' => $price, 'status' => 1], $attrs));
    }

    private function user(int $wallet = 0): User
    {
        $user = new User();
        $user->forceFill([
            'name' => 'خریدار', 'mobile' => '0912' . random_int(1000000, 9999999), 'password' => bcrypt('secret'),
            'wallet' => $wallet, 'blocked_wallet' => 0, 'status' => 0, 'wallet_blocked' => 0, 'bank_blocked' => 0, 'account_blocked' => 0,
        ])->save();
        $this->actingAs($user, 'user');
        return $user;
    }

    private function fields(): array
    {
        return ['email' => 'me@example.com', 'password' => 'hunter22'];
    }

    public function test_catalog_lists_live_products_with_toman_prices(): void
    {
        $id = $this->product(['en_name' => 'Spotify Premium', 'category' => 'music', 'color' => '#1DB954', 'popular' => true]);
        $this->plan($id, 'اشتراک 3 ماهه', '5');
        $this->plan($id, 'اشتراک یک ساله', '20', ['discount' => '12']);
        $this->plan($id, 'غیرفعال', '1', ['status' => 0]);
        $hidden = $this->product([], ['status' => 0]);
        $this->plan($hidden, 'یک ماهه', '3');
        $noPlans = $this->product();

        $list = collect($this->getJson('/api/v1/storefront/accounts')->assertOk()->json('data.accounts'));
        $this->assertNull($list->firstWhere('id', $hidden), 'hidden in admin');
        $this->assertNull($list->firstWhere('id', $noPlans), 'nothing to buy');

        $a = $list->firstWhere('id', $id);
        $this->assertSame('اسپاتیفای', $a['name']);
        $this->assertSame('Spotify Premium', $a['en_name']);
        $this->assertSame('music', $a['category']);
        $this->assertSame('#1db954', $a['color']);
        $this->assertTrue($a['popular']);
        $this->assertSame('login', $a['activation'], 'asks for a password');
        $this->assertSame('کمتر از 4 ساعت', $a['delivery']);
        $this->assertSame([], $a['notes'], 'delivery note moved up; placeholder "a: a" dropped');
        $this->assertSame(['email', 'password'], array_column($a['fields'], 'type'));

        $this->assertCount(2, $a['plans']);
        [$three, $year] = $a['plans'];
        $this->assertSame(['3 ماهه', 3, 1100000, null], [$three['name'], $three['months'], $three['price'], $three['was']]); // 5 $ × 220,000
        $this->assertSame(['یک ساله', 12, 2640000, 4400000], [$year['name'], $year['months'], $year['price'], $year['was']]); // sale price 12 $

        $this->getJson("/api/v1/storefront/accounts/{$a['slug']}")->assertOk()->assertJsonPath('data.account.content', '<p>متن</p>');
        $this->getJson('/api/v1/storefront/accounts/no-such-thing')->assertNotFound();
    }

    public function test_months_are_read_from_plan_names_unless_set(): void
    {
        $cases = ['اکانت سه ماهه Pro' => 3, 'اکانت ۱۸ ماهه premium' => 18, 'اکانت یکساله Premium' => 12, 'اشتراک 12 ماهه' => 12, '2 years' => 24, 'Pro' => null];
        foreach ($cases as $name => $months) {
            $this->assertSame($months, AccountOrders::months((new Plan())->forceFill(['name' => $name])), $name);
        }
        $plan = (new Plan())->forceFill(['name' => 'سه ماهه']);
        $plan->months = 6;
        $this->assertSame(6, AccountOrders::months($plan));
    }

    public function test_guests_are_asked_to_sign_in(): void
    {
        $this->postJson('/api/v1/storefront/account/subscriptions/buy', ['product_id' => 1])->assertStatus(401)->assertJsonPath('code', 'auth');
    }

    public function test_wallet_buy_queues_a_type_7_order_for_admin(): void
    {
        $id = $this->product();
        $plan = $this->plan($id, 'اشتراک 1 ماهه', '3');
        $user = $this->user(1000000);

        $res = $this->postJson('/api/v1/storefront/account/subscriptions/buy', ['product_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 660000])
            ->assertOk()
            ->assertJsonPath('data.order.status', 'queued')
            ->assertJsonPath('data.order.plan.months', 1)
            ->assertJsonPath('data.order.paid_with', 'wallet')
            ->assertJsonPath('data.order.fields.0.value', 'me@example.com')
            ->assertJsonPath('data.order.fields.1.value', null) // the password isn't sent back
            ->assertJsonPath('data.wallet', 340000);

        $order = DB::table('orders')->find($res->json('data.order.id'));
        $this->assertSame('7', (string) $order->type);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Complete', $order->pay_status);
        $this->assertSame($id, (int) $order->product_id);
        $this->assertSame(340000, (int) $user->fresh()->wallet);

        // Same item shape as the old site: the answers, then the plan.
        $detail = json_decode(DB::table('order_items')->where('order_id', $order->id)->value('detail'), true);
        $this->assertSame(['filed_name' => 'password', 'filed_type' => 'کلمه عبور', 'filed_value' => 'hunter22'], $detail[1]);
        $this->assertSame(['plan_id' => $plan, 'plan_name' => 'اشتراک 1 ماهه'], $detail['plan']);

        $this->getJson("/api/v1/storefront/account/subscriptions/orders/{$order->id}")->assertOk()->assertJsonPath('data.order.id', $order->id);
    }

    public function test_bad_answers_and_a_short_wallet_are_refused(): void
    {
        $id = $this->product();
        $plan = $this->plan($id, 'یک ماهه', '3');
        $this->user(100000);
        $url = '/api/v1/storefront/account/subscriptions/buy';

        $this->postJson($url, ['product_id' => $id, 'plan_id' => $plan, 'fields' => ['email' => 'not-an-email', 'password' => 'hunter22']])
            ->assertStatus(422)->assertJsonPath('code', 'fields_invalid')->assertJsonPath('data.field', 'email');
        // A price the page showed before rates moved doesn't stop the purchase; here the wallet is what refuses it.
        $this->postJson($url, ['product_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 500000])
            ->assertStatus(400)->assertJsonPath('code', 'balance');
        $this->postJson($url, ['product_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'price' => 660000])
            ->assertStatus(400)->assertJsonPath('code', 'balance');
        $other = $this->plan($this->product(), 'یک ماهه', '3');
        $this->postJson($url, ['product_id' => $id, 'plan_id' => $other, 'fields' => $this->fields()])
            ->assertStatus(422)->assertJsonPath('code', 'plan_invalid');
        $this->assertSame(0, DB::table('orders')->where('type', 7)->where('product_id', $id)->where('status', '!=', 'failed')->count());
    }

    public function test_card_payment_returns_a_bank_url_and_remembers_the_page(): void
    {
        $id = $this->product();
        $plan = $this->plan($id, 'یک ماهه', '3');
        $this->user();

        $res = $this->postJson('/api/v1/storefront/account/subscriptions/pay-card', ['product_id' => $id, 'plan_id' => $plan, 'fields' => $this->fields(), 'return' => '/accounts/spotify'])
            ->assertOk();
        $this->assertNotEmpty($res->json('data.url'));
        $this->assertSame('/accounts/spotify', session('storefront_return'));
        $order = DB::table('orders')->find($res->json('data.order_id'));
        $this->assertSame(['7', 'waiting to pay'], [(string) $order->type, $order->status]);
    }
}
