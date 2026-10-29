<?php

namespace Tests\Feature\Admin;

use App\lib\PhpEncrypt;
use Illuminate\Support\Facades\DB;

/** Apple IDs, Google Voice, foreign accounts, static IPs and tickets in the React admin. */
class StockApiTest extends AdminTestCase
{
    private function appleRow(array $over = []): array
    {
        return array_merge(['appleId' => 'a' . uniqid() . '@icloud.com', 'password' => 'P@ss1', 'birthday' => '1990/01/01', 'q1' => 'x', 'q2' => 'y', 'q3' => 'z'], $over);
    }

    public function test_apple_ids_are_added_encrypted_and_waiting_orders_get_them(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $order = $this->order($user, ['type' => '3', 'status' => 'pending']);
        DB::table('order_items')->insert(['order_id' => $order, 'type' => 3, 'price' => 1, 'detail' => '2']);

        $dup = $this->appleRow();
        $this->postJson('/admin/api/apple-ids', ['items' => [$dup, $dup]])->assertStatus(422)->assertJsonValidationErrors('items');
        $this->postJson('/admin/api/apple-ids', ['items' => [['appleId' => '']]])->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.appleId', 'items.0.password']);

        $res = $this->postJson('/admin/api/apple-ids', ['items' => [$this->appleRow(), $this->appleRow(), $this->appleRow()]])->assertOk();
        $this->assertSame(1, $res->json('delivered'));
        $this->assertSame('complete', DB::table('orders')->where('id', $order)->value('status'));
        $this->assertSame(2, DB::table('apple_id')->where('order_id', $order)->where('status', 1)->count());

        $left = DB::table('apple_id')->where('type', 1)->where('status', 0)->orderByDesc('id')->first();
        $login = json_decode(PhpEncrypt::decrypt($left->serial, $left->code), true);
        $this->assertSame('P@ss1', $login['password']);
        $this->assertSame($left->apple_id, $this->getJson("/admin/api/apple-ids/{$left->id}")->assertOk()->json('item.appleId'));

        $sold = DB::table('apple_id')->where('order_id', $order)->value('id');
        $this->postJson("/admin/api/apple-ids/$sold/delete")->assertStatus(422);
        $this->postJson("/admin/api/apple-ids/{$left->id}", $this->appleRow(['password' => 'new']))->assertOk();
        $this->postJson("/admin/api/apple-ids/{$left->id}/delete")->assertOk();
        $this->assertNull(DB::table('apple_id')->find($left->id));

        $this->postJson('/admin/api/apple-ids', ['items' => [$this->appleRow(['appleId' => DB::table('apple_id')->where('id', $sold)->value('apple_id')])]])
            ->assertStatus(422)->assertJsonValidationErrors('items.0.appleId');
    }

    public function test_google_voice_stock(): void
    {
        $this->actingAsAdmin($this->admin(super: false));
        $this->getJson('/admin/api/google-voices')->assertForbidden();

        $this->actingAsAdmin();
        $this->postJson('/admin/api/google-voices', ['items' => [['number' => '+1 (502) 000-0000', 'email' => 'g@gmail.com', 'password' => 'pw', 'twoFactor' => 'abcd']]])->assertOk();
        $id = DB::table('google_voices')->orderByDesc('id')->value('id');
        $item = $this->getJson("/admin/api/google-voices/$id")->assertOk()->json('item');
        $this->assertSame('abcd', $item['twoFactor']);
        $this->assertGreaterThanOrEqual(1, $this->getJson('/admin/api/google-voices?filter=stock')->json('counts.stock'));
    }

    public function test_accounts_save_in_the_old_shapes(): void
    {
        $this->actingAsAdmin();
        $payload = [
            'name' => 'اشتراک چت جی پی تی', 'slug' => 'chatgpt-' . uniqid(), 'live' => true, 'withPlans' => true, 'currency' => 'dollar',
            'short' => '<p>کوتاه</p>', 'content' => '<p>بلند</p>', 'metaTitle' => 'm', 'metaDesc' => 'd', 'commission' => 5,
            'fields' => [['name' => 'email', 'title' => 'ایمیل', 'type' => 'text', 'options' => []], ['name' => 'region', 'title' => 'منطقه', 'type' => 'select', 'options' => ['US', 'EU']]],
            'notes' => [['label' => 'تحویل', 'value' => 'آنی']],
            'plans' => [['name' => 'یک ماهه', 'price' => '20', 'discount' => '', 'count' => '', 'live' => true], ['name' => 'سه ماهه', 'price' => '55', 'discount' => '50', 'live' => false]],
        ];
        $id = $this->postJson('/admin/api/accounts', $payload)->assertOk()->json('id');
        $p = DB::table('products')->find($id);
        $this->assertSame('accounts', $p->type);
        $this->assertSame(['تحویل' => 'آنی'], json_decode($p->notes, true));
        $fields = json_decode($p->filds, true);
        $this->assertSame('select_box', $fields[1]['type']);
        $this->assertSame('US,EU', $fields[1]['select_value']);
        $this->assertSame('1', json_decode($p->detail, true)['price_type']);

        $account = $this->getJson("/admin/api/accounts/$id")->assertOk()->json('account');
        $this->assertSame(['US', 'EU'], $account['fields'][1]['options']);
        $this->assertCount(2, $account['plans']);

        $payload['plans'] = [array_merge($account['plans'][1], ['name' => 'سه ماهه ویژه'])];
        $this->postJson("/admin/api/accounts/$id", $payload)->assertOk();
        $plans = DB::table('plans')->where('product_id', $id)->get();
        $this->assertCount(1, $plans);
        $this->assertSame($account['plans'][1]['id'], $plans[0]->id, 'plans are updated in place');
    }

    public function test_ip_location_servers_clients_and_waiting_orders(): void
    {
        $this->actingAsAdmin();
        $location = [
            'name' => 'آی پی ثابت آلمان', 'enName' => 'Germany', 'slug' => 'de-' . uniqid(), 'live' => true, 'image' => '/image/svg/de.svg',
            'metaTitle' => 'm', 'metaDesc' => 'd',
            'plans' => [['name' => 'یک ماهه', 'price' => 100000, 'days' => 30, 'volume' => 30, 'salePrice' => 0, 'percent' => 0, 'image' => '']],
        ];
        $id = $this->postJson('/admin/api/ip/locations', $location)->assertOk()->json('id');
        $this->assertSame('image/svg/de.svg', DB::table('categories')->where('id', $id)->value('image'));
        $plan = DB::table('products')->where('category_id', $id)->where('type', 'pu-ip-sabet')->first();

        $user = $this->user();
        $order = $this->order($user, ['type' => '5', 'status' => 'pending']);
        DB::table('order_items')->insert(['order_id' => $order, 'type' => 5, 'price' => 1, 'product_id' => $plan->id, 'detail' => '1']);

        $server = ['name' => 'de1', 'address' => 't1.example.com', 'ip' => '1.2.3.4', 'panelUrl' => 'https://panel.example.com', 'panelPort' => 2053,
            'username' => 'admin', 'password' => 'secret', 'inbound' => 3, 'inboundPort' => 443, 'status' => 'active'];
        $this->postJson("/admin/api/ip/locations/$id/servers", array_merge($server, ['panelUrl' => 'panel']))->assertStatus(422)->assertJsonValidationErrors('panelUrl');
        $serverId = $this->postJson("/admin/api/ip/locations/$id/servers", $server)->assertOk()->json('id');
        $this->assertSame('dev-session', json_decode(DB::table('ips')->where('id', $serverId)->value('detail'), true)['session']);

        // Saving without a password keeps the stored one.
        $this->postJson("/admin/api/ip/locations/$id/servers/$serverId", array_merge($server, ['password' => '']))->assertOk();
        $this->assertSame('secret', json_decode(DB::table('ips')->where('id', $serverId)->value('detail'), true)['ip_password']);
        $shown = $this->getJson("/admin/api/ip/locations/$id")->assertOk()->json('servers.0');
        $this->assertArrayNotHasKey('password', $shown);
        $this->assertTrue($shown['hasPassword']);

        $res = $this->postJson("/admin/api/ip/servers/$serverId/clients", ['count' => 3])->assertOk();
        $this->assertSame(3, $res->json('made'));
        $this->assertSame(1, $res->json('delivered'));
        $this->assertSame('complete', DB::table('orders')->where('id', $order)->value('status'));
        $given = DB::table('ips')->where('order_id', $order)->first();
        $this->assertSame(60, (int) $given->volume, '30 GB plan + 30 GB extra (Volume-Gb 1)');

        $clients = $this->getJson("/admin/api/ip/locations/$id/clients?status=free")->assertOk();
        $this->assertSame(2, $clients->json('counts.free'));
        $this->assertSame(1, $clients->json('counts.active'));
        $this->postJson('/admin/api/ip/clients/' . $given->id . '/delete')->assertStatus(422);
        $this->postJson('/admin/api/ip/clients/' . $clients->json('clients.0.id') . '/delete')->assertOk();

        // A plan someone bought is not deleted when it is left out.
        $this->postJson("/admin/api/ip/locations/$id", array_merge($location, ['slug' => DB::table('categories')->where('id', $id)->value('slug'), 'plans' => []]))->assertOk();
        $this->assertNotNull(DB::table('products')->find($plan->id));
    }

    public function test_uploads_take_svg_only_for_ip_flags_and_name_files_by_content(): void
    {
        $this->actingAsAdmin();
        $svg = fn () => \Illuminate\Http\UploadedFile::fake()->createWithContent('flag.svg', '<svg xmlns="http://www.w3.org/2000/svg" width="4" height="4"></svg>');
        $this->post('/admin/api/uploads/post-image', ['file' => $svg()], $this->ajax())->assertStatus(422)->assertJsonValidationErrors('file');

        $path = $this->post('/admin/api/ip/uploads/image', ['file' => $svg()], $this->ajax())->assertOk()->json('path');
        $this->assertStringEndsWith('.svg', $path);
        @unlink(public_path(ltrim($path, '/')));

        // A PNG sent under a .php name is saved as .png.
        $png = \Illuminate\Http\UploadedFile::fake()->createWithContent('shell.php.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='));
        $path = $this->post('/admin/api/uploads/account-image', ['file' => $png], $this->ajax())->assertOk()->json('path');
        $this->assertMatchesRegularExpression('~^/upload/accounts/shell-?php-\d+\.png$~', $path);
        @unlink(public_path(ltrim($path, '/')));
    }

    public function test_tickets_queue_and_reply(): void
    {
        $this->actingAsAdmin();
        $user = $this->user(['name' => 'علی تیکتی']);
        $old = DB::table('tickets')->insertGetId(['user_id' => $user->id, 'subject' => 'قدیمی', 'service' => '1', 'status' => 'User-Ticket', 'new_for_admin' => 1,
            'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]);
        $new = DB::table('tickets')->insertGetId(['user_id' => $user->id, 'subject' => 'جدید', 'service' => '4', 'status' => 'User-answer', 'new_for_admin' => 1,
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('ticket_details')->insert(['ticket_id' => $old, 'user_id' => $user->id, 'detail' => '<img src=x onerror=alert(1)> سلام', 'type' => 'user', 'created_at' => now()]);

        $list = collect($this->getJson('/admin/api/tickets?filter=waiting&q=' . urlencode('علی تیکتی'))->assertOk()->json('tickets'));
        $this->assertSame([$old, $new], $list->pluck('id')->all(), 'longest wait first');
        $this->assertSame('سفارشات', $list[0]['department']);

        $show = $this->getJson("/admin/api/tickets/$old")->assertOk();
        $this->assertSame('user', $show->json('messages.0.from'));
        $this->assertSame(0, (int) DB::table('tickets')->where('id', $old)->value('new_for_admin'));

        $this->postJson("/admin/api/tickets/$old/reply", ['message' => ''])->assertStatus(422)->assertJsonValidationErrors('message');
        $this->postJson("/admin/api/tickets/$old/reply", ['message' => 'سلام، بررسی شد.'])->assertOk();
        $t = DB::table('tickets')->find($old);
        $this->assertSame('Admin-answer', $t->status);
        $this->assertSame(1, (int) $t->new_for_user);

        $this->postJson("/admin/api/tickets/$old/status", ['open' => false])->assertOk();
        $this->assertSame('close', DB::table('tickets')->where('id', $old)->value('status'));
    }
}
