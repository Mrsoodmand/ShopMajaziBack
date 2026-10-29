<?php

namespace Tests\Feature\Admin;

use App\lib\Jdf;
use Illuminate\Support\Facades\DB;

/** The React lists' filter panel: dates, amounts, sort and page size sent as query parameters. */
class ListFiltersTest extends AdminTestCase
{
    private function jalali($when): string
    {
        return (new Jdf())->jdate('Y/m/d', strtotime((string) $when), '', 'Asia/Tehran', 'en');
    }

    private function ids($res, string $key): array
    {
        return array_column($res->json($key), 'id');
    }

    public function test_orders_filter_by_amount_date_payment_unread_and_sort(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $old = $this->order($user, ['price' => 10000, 'created_at' => now()->subDays(10)]);
        $big = $this->order($user, ['price' => 90000, 'pay_status' => 'waiting to pay']);
        $chat = $this->order($user, ['price' => 50000]);
        DB::table('order_chats')->insert(['order_id' => $chat, 'type' => 'user', 'text' => 'کجاست؟', 'new' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $base = '/admin/api/orders?view=all&user=' . $user->id;

        $this->assertEqualsCanonicalizing([$big, $chat], $this->ids($this->getJson("$base&min=40000")->assertOk(), 'orders'));
        $this->assertSame([$old], $this->ids($this->getJson("$base&max=۲۰,۰۰۰"), 'orders')); // Persian digits and separators
        $this->assertEqualsCanonicalizing([$old, $chat], $this->ids($this->getJson("$base&pay=Complete"), 'orders'));
        $this->assertSame([$chat], $this->ids($this->getJson("$base&unread=1"), 'orders'));

        $today = $this->jalali(now());
        $this->assertEqualsCanonicalizing([$big, $chat], $this->ids($this->getJson("$base&from=$today"), 'orders'));
        $this->assertSame([$old], $this->ids($this->getJson("$base&to=" . $this->jalali(now()->subDays(2))), 'orders'));

        $this->assertSame([$big, $chat, $old], $this->ids($this->getJson("$base&sort=price_desc"), 'orders'));
        $this->assertSame(20, $this->getJson("$base&per=20")->json('perPage'));
        $this->assertSame(30, $this->getJson("$base&per=7")->json('perPage')); // only the offered sizes

        $this->getJson("$base&from=1404/13/40")->assertStatus(422)->assertJsonValidationErrors('from');
    }

    public function test_users_filter_by_purchases_wallet_and_block(): void
    {
        $this->actingAsAdmin();
        $tag = 'فیلتر' . random_int(1000, 9999);
        $none = $this->user(['name' => "$tag یک"]);
        $buyer = $this->user(['name' => "$tag دو", 'wallet' => 5000]);
        $this->order($buyer);
        $blocked = $this->user(['name' => "$tag سه", 'wallet_blocked' => 1, 'wallet' => 100]);
        $base = '/admin/api/users?q=' . urlencode($tag);

        $this->assertEqualsCanonicalizing([$none->id, $blocked->id], $this->ids($this->getJson("$base&buyer=no")->assertOk(), 'users'));
        $this->assertSame([$buyer->id], $this->ids($this->getJson("$base&buyer=yes"), 'users'));
        $this->assertSame([$buyer->id], $this->ids($this->getJson("$base&min=1000"), 'users'));
        $this->assertSame([$blocked->id], $this->ids($this->getJson("$base&block=wallet"), 'users'));
        $this->assertSame($buyer->id, $this->getJson("$base&sort=wallet_desc")->json('users.0.id'));
        $this->assertSame($buyer->id, $this->getJson("$base&sort=orders_desc")->json('users.0.id'));
    }

    public function test_tickets_list_departments_and_filter_by_one(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $sales = DB::table('tickets')->insertGetId(['user_id' => $user->id, 'subject' => 'فروش', 'service' => '2', 'status' => 'User-Ticket', 'new_for_admin' => 0, 'created_at' => now(), 'updated_at' => now()]);
        $tech = DB::table('tickets')->insertGetId(['user_id' => $user->id, 'subject' => 'فنی', 'service' => '3', 'status' => 'User-Ticket', 'new_for_admin' => 1, 'created_at' => now(), 'updated_at' => now()]);
        $base = '/admin/api/tickets?filter=all&q=' . $user->mobile;

        $res = $this->getJson($base)->assertOk();
        $this->assertContains(['value' => '3', 'label' => 'پشتیبانی فنی'], $res->json('departments'));
        $this->assertSame([$tech], $this->ids($this->getJson("$base&department=3"), 'tickets'));
        $this->assertSame([$tech], $this->ids($this->getJson("$base&unread=1"), 'tickets'));
        $this->assertSame([$sales, $tech], $this->ids($this->getJson("$base&sort=oldest"), 'tickets'));
    }

    public function test_transactions_filter_by_amount(): void
    {
        $this->actingAsAdmin();
        $user = $this->user();
        $small = $this->payment($user, null, ['price' => 1000]);
        $large = $this->payment($user, null, ['price' => 800000]);

        $base = '/admin/api/transactions?view=all&user=' . $user->id;
        $this->assertSame([$large], $this->ids($this->getJson("$base&min=500000")->assertOk(), 'transactions'));
        $this->assertSame([$small, $large], $this->ids($this->getJson("$base&sort=price_asc"), 'transactions'));
    }
}
