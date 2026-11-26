<?php

namespace Tests\Feature\Admin;

use App\lib\PhpEncrypt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/** admin › user actions (Admin\UserActionApiController), the retired Blade routes, and «ورود به پنل کاربر». */
class UserActionsTest extends AdminTestCase
{
    public function test_disable_needs_a_reason_and_is_written_to_history(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();

        $this->postJson("/admin/api/users/{$user->id}/status", ['active' => false, 'reason' => ''])->assertStatus(422)->assertJsonPath('errors.reason.0', 'دلیل غیرفعال کردن را بنویسید.');
        $this->assertSame(0, (int) $user->fresh()->status);

        $this->postJson("/admin/api/users/{$user->id}/status", ['active' => false, 'reason' => 'کلاهبرداری'])->assertOk();
        $this->assertSame(1, (int) $user->fresh()->status);

        $this->postJson("/admin/api/users/{$user->id}/status", ['active' => true])->assertOk();
        $this->assertSame(0, (int) $user->fresh()->status);

        $changes = collect($this->getJson("/admin/api/users/{$user->id}")->json('history'))->pluck('changes')->flatten(1)->pluck('text')->all();
        $this->assertContains('حساب غیرفعال شد', $changes);
        $this->assertContains('کلاهبرداری', $changes);
        $this->assertContains('حساب فعال شد', $changes);
    }

    public function test_a_disabled_user_cannot_sign_in_and_an_open_session_ends(): void
    {
        $user = $this->user(['password' => bcrypt('secret123')]);

        // Signed in, then disabled: the next request finds them signed out.
        $this->actingAs($user, 'user');
        $this->getJson('/api/v1/storefront/account/me')->assertJsonPath('data.authenticated', true);
        DB::table('users')->where('id', $user->id)->update(['status' => 1]);
        Auth::forgetGuards();
        $this->getJson('/api/v1/storefront/account/me')->assertJsonPath('data.authenticated', false);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCaptcha::class)
            ->postJson('/api/v1/storefront/account/login', ['mobile' => $user->mobile, 'password' => 'secret123'])
            ->assertStatus(403)->assertJsonPath('code', 'account_disabled');
        $this->assertFalse(Auth::guard('user')->check());
    }

    public function test_an_admin_on_the_same_browser_stays_signed_in(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->user(['status' => 1]);
        $this->actingAs($user, 'user');

        $this->getJson('/admin/api/users')->assertOk();
        $this->assertSame($admin->id, Auth::guard('admin')->id());
    }

    public function test_send_ticket_opens_an_unread_ticket_for_the_customer(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $other = $this->user();
        $order = $this->order($user);
        $foreign = $this->order($other);

        $this->postJson("/admin/api/users/{$user->id}/ticket", ['subject' => '', 'dept' => 'sales', 'message' => ''])->assertStatus(422)
            ->assertJsonValidationErrors(['subject', 'message']);
        $this->postJson("/admin/api/users/{$user->id}/ticket", ['subject' => 'س', 'dept' => 'orders', 'order_id' => $foreign, 'message' => 'م'])->assertStatus(422);

        $id = $this->postJson("/admin/api/users/{$user->id}/ticket", ['subject' => 'پیگیری سفارش', 'dept' => 'orders', 'order_id' => $order, 'message' => 'سلام، لطفاً مدارک را بفرستید.'])
            ->assertOk()->json('ticketId');

        $ticket = DB::table('tickets')->find($id);
        $this->assertSame([$user->id, 'پیگیری سفارش', 1, $order, 'Admin-answer', 1, 0], [(int) $ticket->user_id, $ticket->subject, (int) $ticket->service, (int) $ticket->order_id, $ticket->status, (int) $ticket->new_for_user, (int) $ticket->new_for_admin]);
        $this->assertSame('admin', DB::table('ticket_details')->where('ticket_id', $id)->value('type'));

        // The customer sees it in /account › tickets.
        $this->actingAs($user, 'user');
        $tickets = collect($this->getJson('/api/v1/storefront/account/panel')->json('data.tickets'));
        $this->assertTrue($tickets->contains(fn ($t) => (int) $t['id'] === (int) $id));
    }

    public function test_login_as_user_opens_the_new_panel(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $res = $this->get("/admin/users/panel/{$user->id}");
        $this->assertStringEndsWith('/account', $res->headers->get('Location'));
        $this->assertSame($user->id, Auth::guard('user')->id());
    }

    public function test_old_pages_redirect_to_react_and_to_account(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $this->get('/admin/home')->assertRedirect('/admin/app');
        $this->get('/admin/orders/12')->assertRedirect('/admin/app/orders/12');
        $this->get('/admin/users/34')->assertRedirect('/admin/app/users/34');
        $this->get('/admin/tickets/56')->assertRedirect('/admin/app/tickets/56');
        $this->get('/admin/blog')->assertNotFound();
        $this->get('/admin/v2/product')->assertNotFound();

        $this->actingAs($user, 'user');
        $this->assertStringEndsWith('/account/orders/7', $this->get('/user/orders/7')->headers->get('Location'));
        $this->assertStringEndsWith('/account/support/new', $this->get('/user/tickets/create')->headers->get('Location'));
        $this->assertStringEndsWith('/account/funds', $this->get('/user/wallet')->headers->get('Location'));
    }

    public function test_order_shows_delivered_google_voice_and_completes_custom_apple_id(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();

        $gvOrder = $this->order($user, ['type' => '6', 'status' => 'complete']);
        $serial = '1234567890123456';
        DB::table('google_voices')->insert(['number' => '+12025550100', 'code' => PhpEncrypt::encrypt($serial, $serial, json_encode(['number' => '+12025550100', 'email' => 'gv@example.com', 'password' => 'pw', 'backup-email' => 'b@example.com', '2fa' => 'X'])), 'serial' => $serial, 'status' => 1, 'order_id' => $gvOrder, 'user_id' => $user->id, 'created_at' => now(), 'updated_at' => now()]);
        $rows = collect($this->getJson("/admin/api/orders/$gvOrder")->assertOk()->json('delivery.items.0.rows'));
        $this->assertSame('gv@example.com', $rows->firstWhere('label', 'ایمیل')['value']);

        $appleOrder = $this->order($user, ['type' => '4', 'status' => 'pending']);
        $this->getJson("/admin/api/orders/$appleOrder")->assertJsonPath('delivery.canCompleteAppleId', true);
        $this->postJson("/admin/api/orders/$appleOrder/apple-id", ['appleid' => 'a@icloud.com'])->assertStatus(422);
        $this->postJson("/admin/api/orders/$appleOrder/apple-id", ['appleid' => 'a@icloud.com', 'password' => 'P', 'brithday' => '1990/01/01', 'q1' => '1', 'q2' => '2', 'q3' => '3'])->assertOk();

        $this->assertSame('complete', DB::table('orders')->where('id', $appleOrder)->value('status'));
        $this->getJson("/admin/api/orders/$appleOrder")->assertJsonPath('delivery.canCompleteAppleId', false)->assertJsonPath('delivery.items.0.title', 'a@icloud.com');
        $this->postJson("/admin/api/orders/$appleOrder/apple-id", ['appleid' => 'b@icloud.com', 'password' => 'P', 'brithday' => '1', 'q1' => '1', 'q2' => '2', 'q3' => '3'])->assertStatus(422);
    }
}
