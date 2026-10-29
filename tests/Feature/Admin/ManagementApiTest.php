<?php

namespace Tests\Feature\Admin;

use App\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/** The management section of the React admin: roles, admins, partners and the Telegram bot. */
class ManagementApiTest extends AdminTestCase
{
    private function permission(string $name): Permission
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        return Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']);
    }

    public function test_only_super_admins_reach_roles_admins_and_bot(): void
    {
        $plain = $this->admin(super: false);
        $plain->givePermissionTo($this->permission('partner-list'));
        $this->actingAsAdmin($plain);

        foreach (['roles', 'permissions', 'admins', 'admins/options', 'bot/users', 'bot/orders', 'bot/messages', 'bot/ip/locations'] as $path) {
            $this->getJson("/admin/api/$path")->assertForbidden();
        }
        $this->postJson('/admin/api/bot/users/1/wallet', ['action' => 'add', 'amount' => 1, 'note' => 'x'])->assertForbidden();
        $this->get('/admin/hegza/user')->assertForbidden();
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

    public function test_bot_users_orders_and_messages(): void
    {
        $this->actingAsAdmin();
        $user = DB::table('hegza_user')->insertGetId(['user_id' => '99887766', 'username' => 'tester_bot', 'name' => 'Tester', 'wallet' => 5000, 'parent' => '0', 'message' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $order = DB::table('hegza_orders')->insertGetId(['user_id' => $user, 'name' => 'آی پی آزمایشی', 'status' => 1, 'created_at' => now(), 'updated_at' => now()]);

        $this->assertSame($user, $this->getJson('/admin/api/bot/users?q=tester_bot')->assertOk()->json('users.0.id'));
        $this->assertSame(1, $this->getJson('/admin/api/bot/users?q=tester_bot&filter=buyers')->json('counts.buyers'));

        $this->postJson("/admin/api/bot/users/$user/wallet", ['action' => 'subtract', 'amount' => 9000, 'note' => 'x'])->assertStatus(422)->assertJsonValidationErrors('amount');
        $this->postJson("/admin/api/bot/users/$user/wallet", ['action' => 'add', 'amount' => 2000])->assertStatus(422)->assertJsonValidationErrors('note');
        $this->postJson("/admin/api/bot/users/$user/wallet", ['action' => 'add', 'amount' => 2000, 'note' => 'جبران'])->assertOk()->assertJson(['wallet' => 7000]);
        $show = $this->getJson("/admin/api/bot/users/$user")->assertOk();
        $this->assertSame(2000, $show->json('history.0.change'));
        $this->assertSame('جبران', $show->json('history.0.note'));

        $this->getJson("/admin/api/bot/orders/$order")->assertOk()->assertJsonPath('order.status', 'active')->assertJsonPath('order.user.id', $user);
        $this->postJson("/admin/api/bot/orders/$order/status", ['status' => 'nope'])->assertStatus(422);
        $this->postJson("/admin/api/bot/orders/$order/status", ['status' => 'suspended'])->assertOk();
        $this->assertSame(-2, (int) DB::table('hegza_orders')->where('id', $order)->value('status'));
        $this->assertSame(1, DB::table('hegza_history')->where('post_id', $order)->where('type', 'change-order-status')->count());

        // Messages: a queued one is sent by the bot's schedule; stopping it clears the "got it" marks.
        DB::table('hegza_messages')->where('status', 0)->update(['status' => -1]);
        $this->postJson('/admin/api/bot/messages', ['title' => 't', 'text' => str_repeat('a', 4001), 'status' => 'queued'])->assertStatus(422)->assertJsonValidationErrors('text');
        $this->postJson('/admin/api/bot/messages', ['title' => '🔰 تخفیف', 'text' => '🔰 سلام', 'status' => 'draft'])->assertStatus(422)->assertJsonValidationErrors('title');
        $msg = $this->postJson('/admin/api/bot/messages', ['title' => 'تخفیف', 'text' => '<b>سلام</b>', 'status' => 'queued'])->assertOk()->json('id');
        $this->assertSame(0, (int) DB::table('hegza_messages')->where('id', $msg)->value('status'));
        DB::table('hegza_user')->where('id', $user)->update(['message' => 1]);
        $this->assertNotNull($this->getJson("/admin/api/bot/messages/$msg")->assertOk()->json('message.progress'));
        $this->postJson("/admin/api/bot/messages/$msg", ['title' => 'تخفیف', 'text' => '<b>سلام</b>', 'status' => 'draft'])->assertOk();
        $this->assertSame(-1, (int) DB::table('hegza_messages')->where('id', $msg)->value('status'));
        $this->assertSame(0, (int) DB::table('hegza_user')->where('id', $user)->value('message'));

        // A sent message saved again stays sent.
        DB::table('hegza_messages')->where('id', $msg)->update(['status' => 1]);
        $this->postJson("/admin/api/bot/messages/$msg", ['title' => 'تخفیف ۲', 'text' => 'سلام', 'status' => 'draft'])->assertOk();
        $this->assertSame(1, (int) DB::table('hegza_messages')->where('id', $msg)->value('status'));
        $this->postJson("/admin/api/bot/messages/$msg/delete")->assertOk();
    }

    public function test_bot_ip_screens_use_the_bot_tables(): void
    {
        $this->actingAsAdmin();
        $res = $this->postJson('/admin/api/bot/ip/locations', [
            'name' => 'آی پی آلمان ربات', 'enName' => 'Bot Germany', 'slug' => 'bot-germany-' . uniqid(), 'live' => true,
            'plans' => [['name' => 'یک ماهه', 'price' => 70000, 'days' => 30, 'volume' => 40]],
        ])->assertOk(); // no image or SEO text needed for the bot
        $id = $res->json('id');
        $this->assertSame('pu-ip-sabet', DB::table('hegza_country')->where('id', $id)->value('type'));
        $this->assertSame(1, DB::table('hegza_products')->where('category_id', $id)->count());
        $this->assertNull(DB::table('categories')->where('id', $id)->where('name', 'آی پی آلمان ربات')->first());

        $server = DB::table('hegza_ip')->insertGetId(['type' => 'pu-ip-sabet', 'category_id' => $id, 'status' => 1, 'detail' => json_encode(['ip_name' => 'bde1', 'ip_port' => '2053', 'ip_password' => 'x'])]);
        $buyer = DB::table('hegza_user')->insertGetId(['user_id' => '55', 'username' => 'buyer', 'name' => 'b', 'wallet' => 0, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('hegza_ip')->insert([
            ['type' => 'pu-ip-sabet-user', 'category_id' => $id, 'parent_id' => $server, 'status' => 0, 'user_id' => null, 'detail' => '{}'],
            ['type' => 'pu-ip-sabet-user', 'category_id' => $id, 'parent_id' => $server, 'status' => -2, 'user_id' => $buyer, 'detail' => '{}'],
        ]);
        $clients = $this->getJson("/admin/api/bot/ip/locations/$id/clients?status=suspended")->assertOk();
        $this->assertSame(1, $clients->json('counts.free'));
        $this->assertSame('@buyer', $clients->json('clients.0.user.name'));

        $made = $this->postJson("/admin/api/bot/ip/servers/$server/clients", ['count' => 2])->assertOk();
        $this->assertSame(2, $made->json('made'));
        $this->assertSame(0, $made->json('delivered'));
        $this->assertSame(3, DB::table('hegza_ip')->where('parent_id', $server)->where('status', 0)->count());
        $this->getJson('/admin/api/bot/ip/locations')->assertOk()->assertJsonPath('waitingOrders', 0);

        // The shop's own location still needs its flag and SEO text.
        $this->postJson('/admin/api/ip/locations', ['name' => 'x', 'enName' => 'x', 'slug' => 'shop-x-' . uniqid()])->assertStatus(422)
            ->assertJsonValidationErrors(['image', 'metaTitle', 'metaDesc']);
    }

    public function test_menu_links_management_to_the_spa(): void
    {
        $this->actingAsAdmin();
        $html = $this->get('/admin/app')->assertOk()->getContent();
        foreach (['/roles', '/admins', '/partners', '/bot/users', '/bot/orders', '/bot/ip', '/bot/messages'] as $to) {
            $this->assertStringContainsString('"to":"' . str_replace('/', '\/', $to) . '"', $html);
        }
    }
}
