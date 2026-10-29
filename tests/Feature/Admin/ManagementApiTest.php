<?php

namespace Tests\Feature\Admin;

use App\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** The management section of the React admin: roles, admins and partners. */
class ManagementApiTest extends AdminTestCase
{
    private function permission(string $name): Permission
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
    }

    public function test_only_super_admins_reach_roles_and_admins(): void
    {
        $plain = $this->admin(super: false);
        $plain->givePermissionTo($this->permission('partner-list'));
        $this->actingAsAdmin($plain);

        foreach (['roles', 'permissions', 'admins', 'admins/options'] as $path) {
            $this->getJson("/admin/api/$path")->assertForbidden();
        }
        // partner-list is enough for partners.
        $this->getJson('/admin/api/partners')->assertOk();
    }

    public function test_roles_are_saved_renamed_and_deleted_safely(): void
    {
        $me = $this->actingAsAdmin();
        $a = $this->permission('test-perm-a');
        $b = $this->permission('test-perm-b');

        $this->postJson('/admin/api/roles', ['faName' => 'آزمایشی', 'name' => 'Bad Name'])->assertStatus(422)->assertJsonValidationErrors('name');
        $id = $this->postJson('/admin/api/roles', ['faName' => 'آزمایشی', 'name' => 'tester', 'permissionIds' => [$a->id]])->assertOk()->json('id');
        $role = Role::findById($id, 'admin');
        $this->assertTrue($role->hasPermissionTo('test-perm-a'));
        $this->assertSame($me->id, (int) $role->admin_id);

        $other = $this->admin(super: false);
        $other->assignRole($role);
        $other->forceFill(['role' => 'tester'])->save();

        // Renaming carries over to admins.role; permissions are replaced.
        $this->postJson("/admin/api/roles/$id", ['faName' => 'آزمایشی ۲', 'name' => 'tester-two', 'permissionIds' => [$b->id]])->assertOk();
        $this->assertSame('tester-two', $other->fresh()->role);
        $role = $role->fresh();
        $this->assertFalse($role->hasPermissionTo('test-perm-a'));
        $this->assertTrue($role->hasPermissionTo('test-perm-b'));
        $this->assertSame($other->id, $this->getJson("/admin/api/roles/$id")->assertOk()->json('role.admins.0.id'));

        $this->postJson("/admin/api/roles/$id/delete")->assertStatus(422);
        $other->syncRoles([]);
        $this->postJson("/admin/api/roles/$id/delete")->assertOk();
        $this->assertNull(Role::where('id', $id)->first());

        $super = Role::findByName('super-admin', 'admin');
        $this->postJson("/admin/api/roles/{$super->id}/delete")->assertStatus(422);
        $this->postJson("/admin/api/roles/{$super->id}", ['faName' => 'ادمین کل', 'name' => 'hacked'])->assertOk();
        $this->assertSame('super-admin', $super->fresh()->name);
    }

    public function test_admins_are_created_with_extra_permissions_and_protected(): void
    {
        $me = $this->actingAsAdmin();
        $a = $this->permission('test-perm-a');
        $b = $this->permission('test-perm-b');
        $role = Role::create(['name' => 'tester', 'guard_name' => 'admin', 'faName' => 'آزمایشی']);
        $role->givePermissionTo($a);

        $this->postJson('/admin/api/admins', ['name' => 'x', 'email' => 'bad', 'mobile' => '123', 'roleId' => $role->id])->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'mobile', 'password']);

        $email = 'n' . uniqid() . '@test.local';
        $id = $this->postJson('/admin/api/admins', [
            'name' => 'مدیر تازه', 'email' => $email, 'mobile' => '09121112233', 'password' => 'longpass1',
            'roleId' => $role->id, 'permissionIds' => [$a->id, $b->id],
        ])->assertOk()->json('id');
        $new = Admin::find($id);
        $this->assertTrue(Hash::check('longpass1', $new->password));
        $this->assertSame('tester', $new->role);
        $this->assertTrue($new->hasRole('tester'));
        // Only the permission the role doesn't give is stored on the admin.
        $this->assertSame(['test-perm-b'], $new->getDirectPermissions()->pluck('name')->all());

        $this->postJson('/admin/api/admins', ['name' => 'y', 'email' => $email, 'mobile' => '09121112233', 'password' => 'longpass1', 'roleId' => $role->id])
            ->assertStatus(422)->assertJsonValidationErrors('email');

        // A blank password keeps the old one.
        $this->postJson("/admin/api/admins/$id", ['name' => 'مدیر', 'email' => $email, 'mobile' => '09121112233', 'password' => '', 'roleId' => $role->id])->assertOk();
        $this->assertTrue(Hash::check('longpass1', $new->fresh()->password));

        // Nobody can take super-admin from themselves or delete themselves.
        $this->postJson("/admin/api/admins/{$me->id}", ['name' => 'من', 'email' => $me->email, 'mobile' => '09120000000', 'roleId' => $role->id])
            ->assertStatus(422)->assertJsonValidationErrors('roleId');
        $this->postJson("/admin/api/admins/{$me->id}/delete")->assertStatus(422);

        $this->postJson("/admin/api/admins/$id/delete")->assertOk();
        $this->assertNull(Admin::find($id));
        $this->assertSame(0, DB::table('model_has_roles')->where('model_type', Admin::class)->where('model_id', $id)->count());
    }

    public function test_partners_services_settlement_and_profit(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/partners', ['name' => 'همکار', 'mobile' => '0912', 'percent' => 150, 'serviceIds' => [], 'deposit' => 'yearly'])
            ->assertStatus(422)->assertJsonValidationErrors(['mobile', 'percent', 'serviceIds', 'deposit']);

        $id = $this->postJson('/admin/api/partners', ['name' => 'همکار', 'mobile' => '09121234567', 'live' => true, 'percent' => 40, 'serviceIds' => [5, 6], 'deposit' => 'weekly'])
            ->assertOk()->json('id');
        $this->assertEqualsCanonicalizing([5, 6], DB::table('partner_service')->where('partner_id', $id)->pluck('service_id')->all());
        $this->assertSame(2, (int) DB::table('partner')->where('id', $id)->value('deposit_type'));

        $this->postJson("/admin/api/partners/$id", ['name' => 'همکار', 'mobile' => '09121234567', 'live' => true, 'percent' => 40, 'serviceIds' => [6], 'deposit' => 'weekly'])->assertOk();
        $this->assertSame([6], DB::table('partner_service')->where('partner_id', $id)->pluck('service_id')->map(fn ($v) => (int) $v)->all());

        // A paid order of service 6 with no buy price credits the active partner.
        DB::table('partner')->whereNot('id', $id)->update(['status' => 0]);
        FactorProfit(['orderId' => 1, 'paymentId' => 1, 'userId' => 1, 'serviceType' => 6, 'paymentType' => 1, 'amount' => 100000, 'buyPrice' => 0]);
        $this->assertEquals(40000, DB::table('partner')->where('id', $id)->value('wallet'));
        $this->assertSame(1, DB::table('partner_profit')->where('partner_id', $id)->count());
        // No active partner on a service: the payment is not blocked.
        FactorProfit(['orderId' => 2, 'paymentId' => 2, 'userId' => 1, 'serviceType' => 3, 'paymentType' => 1, 'amount' => 100000, 'buyPrice' => 0]);
        $this->assertEquals(100000, DB::table('order_profit')->where('order_id', 2)->value('profit'));

        $this->postJson("/admin/api/partners/$id/settle", ['amount' => 50000])->assertStatus(422)->assertJsonValidationErrors('amount');
        $this->postJson("/admin/api/partners/$id/settle", ['amount' => 30000, 'note' => 'کارت'])->assertOk();
        $p = DB::table('partner')->find($id);
        $this->assertEquals(10000, $p->wallet);
        $this->assertEquals(30000, $p->deposit);
        $this->assertSame(30000, $this->getJson("/admin/api/partners/$id")->assertOk()->json('settlements.0.amount'));

        $this->postJson("/admin/api/partners/$id/delete")->assertStatus(422);
        $fresh = $this->postJson('/admin/api/partners', ['name' => 'دیگری', 'mobile' => '09121234568', 'percent' => 10, 'serviceIds' => [1], 'deposit' => 'monthly'])->json('id');
        $this->postJson("/admin/api/partners/$fresh/delete")->assertOk();
        $this->assertSame(0, DB::table('partner_service')->where('partner_id', $fresh)->count());
    }

    public function test_the_telegram_bot_is_gone(): void
    {
        $this->actingAsAdmin();
        foreach (['/admin/api/bot/users', '/admin/api/bot/orders', '/admin/api/bot/messages', '/admin/api/bot/ip/locations', '/admin/hegza/user', '/admin/bot/user'] as $path) {
            $this->getJson($path)->assertNotFound();
        }
        foreach (['/api/hegza/home', '/api/hegza/telegramSendMessage', '/api/bot/checkNumber', '/api/new-services'] as $path) {
            $this->getJson($path)->assertNotFound();
        }
        $this->assertStringNotContainsString('"to":"\/bot', $this->get('/admin/app')->assertOk()->getContent());

        // The shop's own static-IP locations still need their flag and SEO text.
        $this->postJson('/admin/api/ip/locations', ['name' => 'x', 'enName' => 'x', 'slug' => 'shop-x-' . uniqid()])->assertStatus(422)
            ->assertJsonValidationErrors(['image', 'metaTitle', 'metaDesc']);
    }

    public function test_menu_links_management_to_the_spa(): void
    {
        $this->actingAsAdmin();
        $html = $this->get('/admin/app')->assertOk()->getContent();
        foreach (['/roles', '/admins', '/partners'] as $to) {
            $this->assertStringContainsString('"to":"' . str_replace('/', '\/', $to) . '"', $html);
        }
    }
}
