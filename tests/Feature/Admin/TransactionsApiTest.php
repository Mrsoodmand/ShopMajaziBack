<?php

namespace Tests\Feature\Admin;

use App\Admin;
use App\lib\Jdf;
use App\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class TransactionsApiTest extends AdminTestCase
{
    public function test_guests_are_redirected_to_login(): void
    {
        $this->getJson('/admin/api/transactions')->assertRedirect();
    }

    public function test_review_queue_lists_mismatches_oldest_first_with_view_counts(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $newer = $this->topUp($user, ['status' => '-3', 'created_at' => now()->subDay()]);
        $older = $this->topUp($user, ['status' => '-3', 'created_at' => now()->subDays(9)]);
        $this->payment($user, null, ['status' => 'Complete']);
        $this->payment($user, null, ['status' => 'waiting to pay']);
        $this->payment($user, null, ['status' => '-4']);

        $res = $this->getJson("/admin/api/transactions?user={$user->id}")->assertOk();

        $this->assertSame([$older, $newer], array_column($res->json('transactions'), 'id'));
        $this->assertSame(['review' => 2, 'paid' => 1, 'waiting' => 1, 'failed' => 1, 'all' => 5], $res->json('counts'));
        $this->assertSame('all', $res->json('scope'));
        $this->assertSame($user->id, $res->json('user.id'));
    }

    public function test_other_views_are_newest_first(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $first = $this->payment($user, null);
        $second = $this->payment($user, null);

        $res = $this->getJson("/admin/api/transactions?view=paid&user={$user->id}")->assertOk();
        $this->assertSame([$second, $first], array_column($res->json('transactions'), 'id'));
    }

    public function test_type_gateway_and_search_filters(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $orderId = $this->order($user);
        $bank = $this->payment($user, $orderId, ['gate' => 'Bank', 'refid' => '58501986301']);
        $wallet = $this->payment($user, $orderId, ['gate' => 'Wallet']);
        $refund = $this->payment($user, $orderId, ['type' => 2, 'gate' => null]);
        $oldRefund = $this->payment($user, $orderId, ['type' => 'in', 'gate' => null]);

        $ids = fn (string $query) => array_column($this->getJson("/admin/api/transactions?view=all&user={$user->id}&$query")->assertOk()->json('transactions'), 'id');

        // Refunds stored with the old type "in" count as refunds and are reported as type 2.
        $this->assertEqualsCanonicalizing([$refund, $oldRefund], $ids('type=2'));
        $this->assertSame(['2'], array_values(array_unique(array_column(
            $this->getJson("/admin/api/transactions?view=all&type=2&user={$user->id}")->json('transactions'), 'type'))));
        $this->assertSame([$bank], $ids('gate=Bank'));
        $this->assertSame([$wallet], $ids('gate=Wallet'));
        $this->assertEqualsCanonicalizing([$refund, $oldRefund], $ids('gate=none'));

        $this->assertSame([$bank], $ids('q=58501986301'));
        $this->assertContains($bank, $ids("q=$bank"));
        $this->assertCount(4, $ids('q=' . substr($user->mobile, -7)));
        // Persian digits work like Latin ones.
        $this->assertSame([$bank], $ids('q=' . urlencode(strtr('58501986301', array_combine(range(0, 9), ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'])))));
    }

    public function test_date_range_takes_jalali_dates(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $inside = $this->payment($user, null, ['created_at' => '2025-03-21 10:00:00']); // 1404/01/01
        $this->payment($user, null, ['created_at' => '2025-03-19 10:00:00']); // 1403/12/29

        $res = $this->getJson("/admin/api/transactions?view=all&user={$user->id}&from=" . urlencode('۱۴۰۴/۰۱/۰۱') . '&to=1404/01/01')->assertOk();
        $this->assertSame([$inside], array_column($res->json('transactions'), 'id'));

        $this->getJson('/admin/api/transactions?from=1404/13/40')->assertStatus(422)->assertJsonValidationErrors('from');
    }

    public function test_admins_without_a_transactions_permission_are_refused(): void
    {
        $this->actingAsAdmin($this->admin(false));
        $this->getJson('/admin/api/transactions')->assertForbidden();
    }

    public function test_bank_permission_sees_only_bank_payments_and_mismatch_permission_only_mismatches(): void
    {
        $user = $this->user();
        $bank = $this->payment($user, null, ['gate' => 'Bank']);
        $wallet = $this->payment($user, null, ['gate' => 'Wallet']);
        $mismatch = $this->topUp($user, ['status' => '-3']);

        $this->actingAsAdmin($this->adminWith('bank-transaction'));
        $res = $this->getJson("/admin/api/transactions?view=all&user={$user->id}")->assertOk();
        $this->assertEqualsCanonicalizing([$bank, $mismatch], array_column($res->json('transactions'), 'id'));
        $this->assertSame(['bank'], $res->json('scope'));
        $this->getJson("/admin/api/transactions/$wallet")->assertNotFound();

        $this->actingAsAdmin($this->adminWith('moghayerat'));
        $res = $this->getJson("/admin/api/transactions?view=all&user={$user->id}")->assertOk();
        $this->assertSame([$mismatch], array_column($res->json('transactions'), 'id'));
    }

    public function test_detail_of_a_mismatch_compares_the_cards(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['wallet' => 5000]);
        $id = $this->topUp($user, ['status' => '-3', 'card' => '502229******0800', 'fee' => 62500]);

        $this->getJson("/admin/api/transactions/$id")
            ->assertOk()
            ->assertJsonPath('type', '3')
            ->assertJsonPath('registeredCard', '603799******5814')
            ->assertJsonPath('cardMatches', false)
            ->assertJsonPath('fee', 62500)
            ->assertJsonPath('customer.wallet', 5000)
            ->assertJsonPath('can.resolve', true)
            ->assertJsonPath('can.refund', false);
    }

    public function test_detail_links_a_refund_to_the_payment_it_refunded(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $orderId = $this->order($user, ['price' => 36050]);
        $paid = $this->payment($user, $orderId, ['price' => 36050]);

        $this->getJson("/admin/api/transactions/$paid")->assertOk()
            ->assertJsonPath('can.refund', true)
            ->assertJsonPath('order.id', $orderId)
            ->assertJsonPath('refundedBy', null);

        // The panel refunds through the order route, which records the refunded payment's id.
        $this->post('/admin/orders/charge-wallet', ['order_id' => $orderId, 'payment_id' => $paid, 'detail' => 'لغو'], $this->ajax())->assertOk();
        $refund = DB::table('payments')->where('order_id', $orderId)->where('type', 2)->value('id');

        $this->getJson("/admin/api/transactions/$paid")->assertOk()
            ->assertJsonPath('can.refund', false)
            ->assertJsonPath('refundedBy.id', $refund);
        $this->getJson("/admin/api/transactions/$refund")->assertOk()
            ->assertJsonPath('refundOf', $paid)
            ->assertJsonPath('error', null);
    }

    public function test_missing_transaction_is_a_404(): void
    {
        $this->actingAsAdmin();
        $this->getJson('/admin/api/transactions/999999999')->assertNotFound()->assertJsonStructure(['error']);
    }

    public function test_crediting_a_mismatch_adds_the_amount_once_and_logs_it(): void
    {
        $admin = $this->actingAsAdmin();
        $user = $this->user(['wallet' => 120000]);
        $id = $this->topUp($user, ['status' => '-3', 'price' => 3000000]);

        $this->postJson("/admin/api/transactions/$id/resolve", ['action' => 'credit', 'note' => 'کارت در تیکت ۵۱۲ بررسی شد'])
            ->assertOk()->assertJsonStructure(['success']);

        $this->assertSame(3120000, (int) $user->fresh()->wallet);
        $row = DB::table('payments')->find($id);
        $this->assertSame('Complete', $row->status);
        $this->assertNotNull($row->paid_at);

        $log = DB::table('histories')->where('type', 'payment')->where('post_id', $id)->first();
        $this->assertSame($admin->id, (int) $log->admin_id);
        $this->assertSame(['old_status' => '-3', 'new_status' => 'Complete', 'detail' => 'کارت در تیکت ۵۱۲ بررسی شد', 'amount' => 3000000], json_decode($log->detail, true));
        $this->getJson("/admin/api/transactions/$id")->assertJsonPath('history.0.to', 'Complete')->assertJsonPath('can.resolve', false);

        // The credit shows in the user's account history as a wallet increase, with no stray block line.
        $changes = $this->getJson("/admin/api/users/{$user->id}")->assertOk()->json('history.0.changes');
        $this->assertSame(['credit', 'reason'], array_column($changes, 'kind'));

        // A second attempt is refused and the balance stays.
        $this->postJson("/admin/api/transactions/$id/resolve", ['action' => 'credit', 'note' => 'دوباره'])->assertStatus(409);
        $this->assertSame(3120000, (int) $user->fresh()->wallet);
    }

    public function test_rejecting_a_mismatch_marks_it_failed_without_touching_the_wallet(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['wallet' => 7000]);
        $id = $this->topUp($user, ['status' => '-3']);

        $this->postJson("/admin/api/transactions/$id/resolve", ['action' => 'reject'])->assertStatus(422)->assertJsonValidationErrors('note');
        $this->postJson("/admin/api/transactions/$id/resolve", ['action' => 'reject', 'note' => 'بانک مبلغ را به کارت برگرداند'])->assertOk();

        $this->assertSame('failed', DB::table('payments')->where('id', $id)->value('status'));
        $this->assertSame(7000, (int) $user->fresh()->wallet);
    }

    public function test_only_mismatched_top_ups_can_be_resolved(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $paid = $this->topUp($user, ['status' => 'Complete']);
        $orderPayment = $this->payment($user, null, ['status' => '-3', 'type' => 1]);

        $this->postJson("/admin/api/transactions/$paid/resolve", ['action' => 'credit', 'note' => 'x'])->assertStatus(409);
        $this->postJson("/admin/api/transactions/$orderPayment/resolve", ['action' => 'credit', 'note' => 'x'])->assertStatus(409);
        $this->assertSame(0, (int) $user->fresh()->wallet);
    }

    public function test_resolving_needs_the_mismatch_permission(): void
    {
        $user = $this->user();
        $id = $this->topUp($user, ['status' => '-3']);

        $this->actingAsAdmin($this->adminWith('transaction-list'));
        $this->getJson("/admin/api/transactions/$id")->assertOk()->assertJsonPath('can.resolve', false);
        $this->postJson("/admin/api/transactions/$id/resolve", ['action' => 'credit', 'note' => 'x'])->assertForbidden();
        $this->assertSame(0, (int) $user->fresh()->wallet);
    }

    /** A wallet top-up from a registered card, the way v2\ShopController stores it. */
    private function topUp(User $user, array $attrs = []): int
    {
        $card = DB::table('cards')->insertGetId(['user_id' => $user->id, 'card' => '6037991234565814', 'status' => 1, 'type' => 1, 'created_at' => now(), 'updated_at' => now()]);
        return $this->payment($user, null, array_merge([
            'type' => 3,
            'gate' => 'Bank',
            'card_id' => $card,
            'card' => '603799******5814',
            'price' => 1000000,
            'refid' => '40634157501',
            'details' => 'ZarinPal',
        ], $attrs));
    }

    private function adminWith(string $permission): Admin
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']);
        $admin = $this->admin(false);
        $admin->givePermissionTo($permission);
        return $admin;
    }
}
