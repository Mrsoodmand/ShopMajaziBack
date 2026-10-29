<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;

class DashboardApiTest extends AdminTestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/admin/api/dashboard')->assertRedirect();
    }

    public function test_today_figures_and_queue_come_from_real_orders(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        DB::table('orders')->whereIn('status', ['pending', 'progressing', 'suspended'])->delete();
        $before = $this->getJson('/admin/api/dashboard')->json();

        $this->order($user, ['status' => 'complete', 'price' => 70000]);
        $this->order($user, ['status' => 'complete', 'price' => 30000, 'created_at' => now()->subDay()]);
        $old = $this->order($user, ['status' => 'suspended', 'created_at' => now()->subDays(5)]);
        $this->order($user, ['status' => 'pending']);
        $this->order($user, ['status' => 'waiting to pay']);

        $res = $this->getJson('/admin/api/dashboard')->assertOk();

        $res->assertJsonCount(14, 'days');
        $this->assertSame($before['days'][13]['sales'] + 70000, $res->json('days.13.sales'));
        $this->assertSame($before['days'][12]['sales'] + 30000, $res->json('days.12.sales'));
        $this->assertSame(2, $res->json('queue.total'));
        $this->assertSame(1, $res->json('queue.counts.suspended'));
        $this->assertSame($old, $res->json('queue.oldest.0.id'), 'the queue starts with the order waiting longest');
        $this->assertSame($before['queue']['waitingToPay'] + 1, $res->json('queue.waitingToPay'));
    }

    public function test_open_tickets_mismatches_and_pending_comments(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $before = $this->getJson('/admin/api/dashboard')->json();

        DB::table('tickets')->insert(['user_id' => $user->id, 'subject' => 'سفارش نرسید', 'status' => 'User-Ticket', 'new_for_admin' => 1, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('tickets')->insert(['user_id' => $user->id, 'subject' => 'پاسخ داده شد', 'status' => 'Admin-answer', 'created_at' => now(), 'updated_at' => now()]);
        $this->payment($user, null, ['status' => -3, 'price' => 90000]);
        DB::table('comments')->insert(['name' => 'علی', 'text' => 'عالی بود', 'status' => 'Pending', 'parent_id' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('comments')->insert(['name' => 'علی', 'text' => 'پاسخ', 'status' => 'Pending', 'parent_id' => 5, 'created_at' => now(), 'updated_at' => now()]);

        $res = $this->getJson('/admin/api/dashboard')->assertOk();

        $this->assertSame($before['tickets']['total'] + 1, $res->json('tickets.total'), 'answered tickets are not open');
        $this->assertSame('سفارش نرسید', $res->json('tickets.items.0.subject'));
        $this->assertSame($before['mismatches']['total'] + 1, $res->json('mismatches.total'));
        $this->assertSame(90000, $res->json('mismatches.items.0.price'));
        $this->assertSame($before['comments']['total'] + 1, $res->json('comments.total'), 'replies are not counted');
    }

    public function test_blocks_follow_the_admins_permissions(): void
    {
        $this->actingAsAdmin($this->admin(super: false));

        $res = $this->getJson('/admin/api/dashboard')->assertOk();

        $res->assertJsonStructure(['today', 'users' => ['total', 'today']]);
        $res->assertJsonMissingPath('queue');
        $res->assertJsonMissingPath('days');
        $res->assertJsonMissingPath('tickets');
        $res->assertJsonMissingPath('mismatches');
        $res->assertJsonMissingPath('comments');
    }
}
