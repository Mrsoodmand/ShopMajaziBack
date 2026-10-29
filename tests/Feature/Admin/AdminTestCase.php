<?php

namespace Tests\Feature\Admin;

use App\Admin;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * Base for admin panel tests. Every test runs inside a transaction on the shop_majazi_test database
 * (composer test:setup), so rows created here never persist.
 */
abstract class AdminTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function admin(bool $super = true): Admin
    {
        $admin = new Admin();
        $admin->forceFill([
            'full_name' => 'مدیر آزمایشی',
            'mobile' => '09120000000',
            'email' => 'admin' . uniqid() . '@test.local',
            'password' => bcrypt('secret'),
            'role' => 1,
        ])->save();

        if ($super) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $role = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'admin']);
            $admin->assignRole($role);
        }
        return $admin;
    }

    protected function actingAsAdmin(?Admin $admin = null): Admin
    {
        $admin ??= $this->admin();
        $this->actingAs($admin, 'admin');
        return $admin;
    }

    protected function user(array $attrs = []): User
    {
        $user = new User();
        $user->forceFill(array_merge([
            'name' => 'کاربر آزمایشی',
            'mobile' => '0912' . random_int(1000000, 9999999),
            'password' => bcrypt('secret'),
            'wallet' => 0,
            'blocked_wallet' => 0,
            'status' => 0,
            'wallet_blocked' => 0,
            'bank_blocked' => 0,
            'account_blocked' => 0,
        ], $attrs))->save();
        return $user;
    }

    protected function order(User $user, array $attrs = []): int
    {
        return DB::table('orders')->insertGetId(array_merge([
            'user_id' => $user->id,
            'type' => '2',
            'service' => 'سرویس آزمایشی',
            'price' => 50000,
            'status' => 'pending',
            'pay_status' => 'Complete',
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    protected function payment(User $user, ?int $orderId, array $attrs = []): int
    {
        return DB::table('payments')->insertGetId(array_merge([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'type' => 1,
            'status' => 'Complete',
            'gate' => 'Bank',
            'price' => 50000,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));
    }

    /** Headers the React app sends, so legacy controllers answer with JSON. */
    protected function ajax(): array
    {
        return ['X-Requested-With' => 'XMLHttpRequest', 'Accept' => 'application/json'];
    }
}
