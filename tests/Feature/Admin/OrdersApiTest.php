<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;

class OrdersApiTest extends AdminTestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->getJson('/admin/api/orders')->assertRedirect();
    }

    public function test_queue_lists_only_orders_that_need_action_oldest_first(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $newer = $this->order($user, ['status' => 'pending', 'created_at' => now()]);
        $older = $this->order($user, ['status' => 'suspended', 'created_at' => now()->subDays(3)]);
        $done = $this->order($user, ['status' => 'complete']);

        $res = $this->getJson('/admin/api/orders?view=action&q=' . $user->mobile)->assertOk();

        $ids = array_column($res->json('orders'), 'id');
        $this->assertSame([$older, $newer], $ids);
        $this->assertNotContains($done, $ids);
        $this->assertSame(2, $res->json('counts.action'));
        $this->assertSame(1, $res->json('counts.complete'));
        $this->assertSame(3, $res->json('counts.all'));
    }

    public function test_search_by_order_number_and_type_filter(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $vn = $this->order($user, ['type' => '1', 'status' => 'complete']);
        $this->order($user, ['type' => '2', 'status' => 'complete']);

        $this->getJson("/admin/api/orders?view=all&q=$vn")->assertOk()->assertJsonPath('orders.0.id', $vn);
        $res = $this->getJson('/admin/api/orders?view=all&type=1&q=' . $user->mobile)->assertOk();
        $this->assertSame([$vn], array_column($res->json('orders'), 'id'));
    }

    public function test_list_counts_unread_customer_messages(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $id = $this->order($user);
        DB::table('order_chats')->insert([
            ['order_id' => $id, 'type' => 'user', 'text' => 'سلام', 'new' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['order_id' => $id, 'type' => 'admin', 'text' => 'پاسخ', 'new' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->getJson("/admin/api/orders?view=all&q=$id")->assertOk()->assertJsonPath('orders.0.unread', 1);
    }

    public function test_detail_returns_items_payments_and_marks_customer_messages_read(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['wallet' => 1000]);
        $id = $this->order($user, ['type' => '2', 'service' => null, 'price' => 66000]);
        $pay = $this->payment($user, $id, ['price' => 66000, 'refid' => '83407289601']);
        DB::table('orders')->where('id', $id)->update(['pay_id' => $pay]);
        DB::table('order_items')->insert([
            'order_id' => $id, 'user_id' => $user->id, 'type' => '2', 'price' => 66000,
            'detail' => json_encode([['filed_name' => 'link', 'filed_type' => 'لینک کانال', 'filed_value' => 'https://t.me/x'], 'plan' => ['plan_name' => '500 ممبر']]),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('order_chats')->insert(['order_id' => $id, 'type' => 'user', 'text' => 'کی انجام میشه؟', 'new' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $this->getJson("/admin/api/orders/$id")
            ->assertOk()
            ->assertJsonPath('service', '500 ممبر')
            ->assertJsonPath('customer.wallet', 1000)
            ->assertJsonPath('payment.refId', '83407289601')
            ->assertJsonPath('items.0.rows.0.label', 'لینک کانال')
            ->assertJsonPath('items.0.rows.0.value', 'https://t.me/x')
            ->assertJsonPath('chats.0.from', 'customer')
            ->assertJsonPath('chats.0.unread', true);

        $this->assertSame(0, DB::table('order_chats')->where('order_id', $id)->where('new', 1)->where('type', 'user')->count());
    }

    public function test_missing_order_is_a_404_with_a_message(): void
    {
        $this->actingAsAdmin();
        $this->getJson('/admin/api/orders/999999999')->assertNotFound()->assertJsonStructure(['error']);
    }

    public function test_status_change_requires_a_reason_and_writes_history(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->user();
        $id = $this->order($user, ['status' => 'pending']);

        $this->post('/admin/orders/single/change-status', ['order_id' => $id, 'status' => 'complete'], $this->ajax())
            ->assertStatus(400)->assertJsonStructure(['error']);

        $this->post('/admin/orders/single/change-status', ['order_id' => $id, 'status' => 'complete', 'detail' => 'انجام شد'], $this->ajax())
            ->assertOk()->assertJsonStructure(['success']);

        $this->assertSame('complete', DB::table('orders')->where('id', $id)->value('status'));
        $history = DB::table('histories')->where('type', 'order')->where('post_id', $id)->first();
        $this->assertSame($admin->id, (int) $history->admin_id);
        $this->assertSame('انجام شد', json_decode($history->detail, true)['detail']);
    }

    public function test_refund_credits_the_order_price_once(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['wallet' => 1000]);
        $id = $this->order($user, ['price' => 36050]);
        $pay = $this->payment($user, $id, ['price' => 36050]);
        $payload = ['order_id' => $id, 'payment_id' => $pay, 'detail' => 'پیامک نرسید'];

        $this->post('/admin/orders/charge-wallet', $payload, $this->ajax())->assertOk();
        $this->assertSame(37050, (int) $user->fresh()->wallet);
        $this->getJson("/admin/api/orders/$id")->assertJsonPath('payments.0.refunded', true);

        // A second refund of the same payment is refused and the balance does not change.
        $this->post('/admin/orders/charge-wallet', $payload, $this->ajax())->assertStatus(400);
        $this->assertSame(37050, (int) $user->fresh()->wallet);
    }

    public function test_admin_can_message_the_customer(): void
    {
        $this->actingAsAdmin();
        $id = $this->order($this->user());

        $this->post('/admin/orders/order-chat', ['order_id' => $id, 'message' => 'در حال انجام است'], $this->ajax())->assertOk();
        $this->getJson("/admin/api/orders/$id")->assertJsonPath('chats.0.from', 'admin')->assertJsonPath('chats.0.text', 'در حال انجام است');
    }
}
