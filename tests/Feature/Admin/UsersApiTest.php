<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersApiTest extends AdminTestCase
{
    /** The fields the React settings form sends with every save (SubmitUserChange overwrites all of them). */
    private function save(int $id, array $fields): \Illuminate\Testing\TestResponse
    {
        return $this->post('/admin/users/edit/submio', array_merge(['id' => $id, 'wallet' => 0, 'wallet_action' => 1, 'blocked_wallet' => 0, 'blocked_wallet_action' => 1], $fields), $this->ajax());
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->getJson('/admin/api/users')->assertRedirect();
    }

    public function test_list_searches_and_filters(): void
    {
        $this->actingAsAdmin();
        $rich = $this->user(['name' => 'آزمون پولدار', 'wallet' => 500000]);
        $blocked = $this->user(['name' => 'آزمون مسدود', 'bank_blocked' => 1]);
        $this->order($rich);
        $this->order($rich);

        $res = $this->getJson('/admin/api/users?q=آزمون')->assertOk();
        $this->assertSame(2, $res->json('counts.all'));
        $this->assertSame(1, $res->json('counts.balance'));
        $this->assertSame(1, $res->json('counts.blocked'));

        $this->getJson('/admin/api/users?filter=balance&q=آزمون')->assertJsonPath('users.0.id', $rich->id)->assertJsonPath('users.0.orders', 2);
        $this->getJson('/admin/api/users?filter=blocked&q=آزمون')->assertJsonPath('users.0.id', $blocked->id)->assertJsonPath('users.0.flags.bankBlocked', true);
        $this->getJson('/admin/api/users?q=' . $blocked->mobile)->assertJsonCount(1, 'users');
    }

    public function test_detail_returns_stats_orders_and_links(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['wallet' => 1200]);
        $this->order($user, ['status' => 'complete', 'price' => 30000]);
        $this->order($user, ['status' => 'failed', 'price' => 9000]);
        $this->user(['parent_id' => $user->id, 'name' => 'معرفی‌شده']);

        $this->getJson("/admin/api/users/{$user->id}")
            ->assertOk()
            ->assertJsonPath('wallet', 1200)
            ->assertJsonPath('stats.orders', 2)
            ->assertJsonPath('stats.completed', 1)
            ->assertJsonPath('stats.spent', 30000)
            ->assertJsonPath('referrals.0.name', 'معرفی‌شده')
            ->assertJsonCount(2, 'orders');
    }

    public function test_missing_user_is_a_404(): void
    {
        $this->actingAsAdmin();
        $this->getJson('/admin/api/users/999999999')->assertNotFound();
    }

    public function test_wallet_credit_and_debit_keep_other_settings(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['name' => 'علی', 'wallet' => 10000, 'bank_blocked' => 1]);
        $keep = ['user_name' => 'علی', 'bank_blocked' => 1];

        $this->save($user->id, $keep + ['wallet' => 5000, 'wallet_action' => 1, 'detail' => 'جبران خسارت'])->assertOk();
        $this->assertSame(15000, (int) $user->fresh()->wallet);
        $this->assertSame(1, (int) $user->fresh()->bank_blocked);

        $this->save($user->id, $keep + ['wallet' => 2000, 'wallet_action' => 0])->assertOk();
        $this->assertSame(13000, (int) $user->fresh()->wallet);

        $this->getJson("/admin/api/users/{$user->id}")
            ->assertJsonPath('history.1.changes.0.text', 'افزایش کیف پول: 5,000 تومان')
            ->assertJsonPath('history.1.changes.2.text', 'جبران خسارت');
    }

    public function test_password_change_is_hashed_and_never_logged(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['name' => 'رضا']);

        $this->save($user->id, ['user_name' => 'رضا', 'password' => 'NewPass-123'])->assertOk();

        $this->assertTrue(Hash::check('NewPass-123', $user->fresh()->password));
        $detail = DB::table('histories')->where('type', 'user')->where('post_id', $user->id)->value('detail');
        $this->assertStringNotContainsString('NewPass-123', $detail);
        $this->assertSame('changed', json_decode($detail, true)['password']);
    }

    public function test_block_switches_are_saved(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['name' => 'سارا']);

        $this->save($user->id, ['user_name' => 'سارا', 'status' => 1, 'wallet_blocked' => 1])->assertOk();
        $this->getJson("/admin/api/users/{$user->id}")
            ->assertJsonPath('flags.loginBlocked', true)
            ->assertJsonPath('flags.walletBlocked', true)
            ->assertJsonPath('flags.bankBlocked', false);
    }
}
