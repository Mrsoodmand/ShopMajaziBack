<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class VirtualNumbersApiTest extends AdminTestCase
{
    private function service(array $attrs = []): int
    {
        return DB::table('virtual__services')->insertGetId(array_merge([
            'faName' => 'شماره مجازی تلگرام', 'enName' => 'Telegram', 'slug' => 'telegram-' . uniqid(), 'status' => 1, 'sort' => 1,
            'pva' => 'opt29', 'man' => '3', 'active' => 'tg', 'land' => 'empty', 'image' => '/image/services/svg/telegram.svg',
            'meta_title' => 'خرید شماره مجازی تلگرام', 'meta_desc' => 'توضیح', 'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function country(array $attrs = []): int
    {
        return DB::table('virtual__countries')->insertGetId(array_merge([
            'faName' => 'شماره مجازی کشور روسیه', 'enName' => 'Russia', 'slug' => 'russia-' . uniqid(), 'status' => 1, 'sort' => 1,
            'code' => 'RU', 'pva' => 'RU', 'man' => '1', 'active' => '0', 'land' => '1', 'category_id' => 5,
            'meta_title' => 'خرید شماره مجازی روسیه', 'meta_desc' => 'توضیح', 'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function offer(int $serviceId, int $countryId, string $type, array $attrs = []): int
    {
        return DB::table('virtual__products')->insertGetId(array_merge([
            'service_id' => $serviceId, 'country_id' => $countryId, 'service_code' => 'x', 'country_code' => 'y', 'type' => $type,
            'faName' => 'تلگرام -1', 'countryName' => 'کشور روسیه -1', 'countryImage' => 'image/svg/ru.svg',
            'status' => 1, 'toman_price' => 80030, 'dollar_price' => '0.20', 'created_at' => now(), 'updated_at' => now(),
        ], $attrs));
    }

    private function servicePayload(array $over = []): array
    {
        return array_merge([
            'name' => 'شماره مجازی واتساپ',
            'enName' => 'WhatsApp',
            'slug' => 'whatsapp-' . uniqid(),
            'sort' => 3,
            'live' => true,
            'featured' => true,
            'image' => '/upload/virtual-number/whatsapp.svg',
            'codes' => ['pva' => 'opt20', 'man' => ' 1012 ', 'active' => '', 'land' => null],
            'content' => '<p>متن صفحه</p>',
            'metaTitle' => 'خرید شماره مجازی واتساپ',
            'metaDesc' => 'شماره مجازی واتساپ با تحویل آنی',
            'seo' => '<script type="application/ld+json">{}</script>',
        ], $over);
    }

    private function countryPayload(array $over = []): array
    {
        return array_merge([
            'name' => 'شماره مجازی کشور آلمان',
            'enName' => 'Germany',
            'slug' => 'germany-' . uniqid(),
            'sort' => 3,
            'live' => true,
            'flagCode' => 'de',
            'regionId' => $this->region(),
            'image' => '',
            'codes' => ['pva' => 'DE', 'man' => '123', 'active' => '43', 'land' => ''],
            'content' => 'شماره‌های آلمان',
            'metaTitle' => 'خرید شماره مجازی آلمان',
            'metaDesc' => 'شماره مجازی آلمان',
            'seo' => '',
        ], $over);
    }

    private function region(): int
    {
        return DB::table('categories')->insertGetId(['name' => 'اروپا', 'slug' => 'europe-' . uniqid(), 'type' => 'virtual-number-category', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function adminWith(string $permission)
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $admin = $this->admin(super: false);
        $admin->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'admin']));
        return $this->actingAsAdmin($admin);
    }

    /* ---------- Access ---------- */

    public function test_each_screen_needs_its_old_menu_permission(): void
    {
        $this->actingAsAdmin($this->admin(super: false));
        $this->getJson('/admin/api/virtual-numbers/services')->assertForbidden();
        $this->getJson('/admin/api/virtual-numbers/countries')->assertForbidden();
        $this->getJson('/admin/api/virtual-numbers/settings')->assertForbidden();
        $this->getJson('/admin/api/virtual-numbers/offers?service=1')->assertForbidden();

        $this->adminWith('virtual-number-services');
        $this->getJson('/admin/api/virtual-numbers/services')->assertOk();
        $this->getJson('/admin/api/virtual-numbers/countries')->assertForbidden();
        $this->getJson('/admin/api/virtual-numbers/settings')->assertForbidden();
    }

    /* ---------- Services ---------- */

    public function test_services_are_listed_in_site_order_with_codes_and_offer_counts(): void
    {
        $this->actingAsAdmin();
        $second = $this->service(['faName' => 'اینستاگرام', 'sort' => 2]);
        $first = $this->service(['faName' => 'تلگرام', 'sort' => 1, 'special' => 1]);
        $country = $this->country();
        $this->offer($first, $country, 'pva');
        $this->offer($first, $country, 'man', ['status' => 0]);
        $this->offer($first, $country, 'active', ['status' => 1, 'toman_price' => 0]);

        $rows = collect($this->getJson('/admin/api/virtual-numbers/services')->assertOk()->json('services'))->whereIn('id', [$first, $second])->values();
        $this->assertSame([$first, $second], $rows->pluck('id')->all());
        $this->assertSame(['pva' => 'opt29', 'man' => '3', 'active' => 'tg', 'land' => ''], $rows[0]['codes'], '"empty" is shown as blank');
        $this->assertSame(['total' => 3, 'live' => 1], $rows[0]['offers'], 'only priced rows with status 1 are on sale');
        $this->assertTrue($rows[0]['featured']);
        $this->assertSame(['total' => 0, 'live' => 0], $rows[1]['offers']);
    }

    public function test_service_create_validates(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/virtual-numbers/services', ['name' => '', 'slug' => 'Bad Slug', 'codes' => ['pva' => 'two words']])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'enName', 'slug', 'sort', 'metaTitle', 'metaDesc', 'codes.pva']);

        $this->service(['slug' => 'taken-slug']);
        $this->postJson('/admin/api/virtual-numbers/services', $this->servicePayload(['slug' => 'taken-slug']))
            ->assertStatus(422)->assertJsonValidationErrors(['slug']);
        // Old slugs like "1668-.com" are allowed.
        $this->postJson('/admin/api/virtual-numbers/services', $this->servicePayload(['slug' => '1668-.com-' . uniqid()]))->assertOk();
    }

    public function test_service_is_saved_in_the_old_admin_columns(): void
    {
        $this->actingAsAdmin();
        $payload = $this->servicePayload();
        $id = $this->postJson('/admin/api/virtual-numbers/services', $payload)->assertOk()->assertJsonPath('success', 'سرویس ساخته شد.')->json('id');

        $row = DB::table('virtual__services')->find($id);
        $this->assertSame('شماره مجازی واتساپ', $row->faName);
        $this->assertSame($payload['slug'], $row->slug);
        $this->assertSame(['opt20', '1012', 'empty', 'empty'], [$row->pva, $row->man, $row->active, $row->land], 'blank codes are stored as "empty"');
        $this->assertSame([1, 1, 3], [(int) $row->status, (int) $row->special, (int) $row->sort]);
        $this->assertSame('<p>متن صفحه</p>', $row->detail);
        $this->assertSame('خرید شماره مجازی واتساپ', $row->meta_title);
        $this->assertSame($payload['seo'], $row->seo);

        $form = $this->getJson("/admin/api/virtual-numbers/services/$id")->assertOk()->json('service');
        $this->assertSame(['pva' => 'opt20', 'man' => '1012', 'active' => '', 'land' => ''], $form['codes']);
        $this->assertSame('<p>متن صفحه</p>', $form['content']);

        $this->postJson("/admin/api/virtual-numbers/services/$id", array_merge($payload, ['live' => false, 'featured' => false]))
            ->assertOk()->assertJsonPath('success', 'سرویس ذخیره شد.');
        $row = DB::table('virtual__services')->find($id);
        $this->assertSame([0, 0], [(int) $row->status, (int) $row->special]);

        $this->getJson('/admin/api/virtual-numbers/services/999999')->assertNotFound();
        $this->postJson('/admin/api/virtual-numbers/services/999999', $payload)->assertNotFound();
    }

    public function test_service_codes_can_be_saved_from_the_list(): void
    {
        $this->actingAsAdmin();
        $id = $this->service();
        $this->postJson("/admin/api/virtual-numbers/services/$id/codes", ['codes' => ['pva' => 'opt99', 'man' => '', 'active' => 'tg', 'land' => 'a-very-long-code']])
            ->assertStatus(422)->assertJsonValidationErrors(['codes.land']);

        $this->postJson("/admin/api/virtual-numbers/services/$id/codes", ['codes' => ['pva' => 'opt99', 'man' => '', 'active' => 'tg', 'land' => '7']])
            ->assertOk()->assertJsonPath('codes', ['pva' => 'opt99', 'man' => '', 'active' => 'tg', 'land' => '7']);
        $row = DB::table('virtual__services')->find($id);
        $this->assertSame(['opt99', 'empty', 'tg', '7'], [$row->pva, $row->man, $row->active, $row->land]);
        $this->assertSame('شماره مجازی تلگرام', $row->faName, 'only the codes change');
    }

    /* ---------- Countries ---------- */

    public function test_countries_are_listed_with_flags_regions_and_offer_counts(): void
    {
        $this->actingAsAdmin();
        $region = $this->region();
        $id = $this->country(['code' => 'gb-eng', 'category_id' => $region, 'sort' => 0, 'active' => '0']);
        $this->offer($this->service(), $id, 'pva');

        $json = $this->getJson('/admin/api/virtual-numbers/countries')->assertOk();
        $this->assertContains(['id' => $region, 'name' => 'اروپا'], $json->json('regions'));
        $row = collect($json->json('countries'))->firstWhere('id', $id);
        $this->assertSame('/image/svg/gb-eng.svg', $row['flag']);
        $this->assertSame($region, $row['regionId']);
        $this->assertSame('0', $row['codes']['active'], 'SMS-Activate\'s Russia code "0" is a real code');
        $this->assertSame(['total' => 1, 'live' => 1], $row['offers']);
    }

    public function test_country_is_saved_in_the_old_admin_columns(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/virtual-numbers/countries', $this->countryPayload(['flagCode' => 'Germany', 'regionId' => 999999, 'content' => str_repeat('ا', 256)]))
            ->assertStatus(422)->assertJsonValidationErrors(['flagCode', 'regionId', 'content']);

        $payload = $this->countryPayload();
        $id = $this->postJson('/admin/api/virtual-numbers/countries', $payload)->assertOk()->json('id');
        $row = DB::table('virtual__countries')->find($id);
        $this->assertSame(['DE', '/image/svg/de.svg', $payload['regionId']], [$row->code, $row->image_2, (int) $row->category_id]);
        $this->assertSame(['DE', '123', '43', 'empty'], [$row->pva, $row->man, $row->active, $row->land]);
        $this->assertSame('شماره‌های آلمان', $row->long_detail);

        DB::table('virtual__countries')->where('id', $id)->update(['image' => 'upload/country/Germany-min.jpg']);
        $form = $this->getJson("/admin/api/virtual-numbers/countries/$id")->assertOk()->json('country');
        $this->assertSame('DE', $form['flagCode']);
        $this->assertSame('شماره‌های آلمان', $form['content']);
        $this->assertSame('/upload/country/Germany-min.jpg', $form['image'], 'relative paths get a slash so the preview loads');
        $sid = $this->service(['image' => 'http://localhost:8000/image/services/svg/1668.svg']);
        $this->assertSame('/image/services/svg/1668.svg', $this->getJson("/admin/api/virtual-numbers/services/$sid")->json('service.image'));

        // Changing the PVA code no longer overwrites the flag.
        $this->postJson("/admin/api/virtual-numbers/countries/$id", array_merge($payload, ['codes' => ['pva' => 'empty', 'man' => '123', 'active' => '43', 'land' => '']]))->assertOk();
        $row = DB::table('virtual__countries')->find($id);
        $this->assertSame(['DE', 'empty'], [$row->code, $row->pva]);

        $this->postJson("/admin/api/virtual-numbers/countries/$id/codes", ['codes' => ['pva' => 'DE', 'man' => '', 'active' => '43', 'land' => '']])->assertOk();
        $this->assertSame(['DE', 'empty'], [DB::table('virtual__countries')->find($id)->pva, DB::table('virtual__countries')->find($id)->man]);
    }

    /* ---------- Rebuilding offers ---------- */

    public function test_rebuilding_a_service_creates_one_offer_per_provider_both_sides_list(): void
    {
        $this->actingAsAdmin();
        $service = $this->service(['pva' => 'opt29', 'man' => '3', 'active' => 'empty']);
        $russia = $this->country(['pva' => 'RU', 'man' => '1', 'sort' => 4]);
        $noPva = $this->country(['faName' => 'شماره مجازی کشور قزاقستان', 'code' => 'KZ', 'pva' => 'empty', 'man' => '2']);

        $res = $this->postJson("/admin/api/virtual-numbers/services/$service/rebuild")->assertOk();
        $res->assertJsonPath('created', 3)->assertJsonPath('updated', 0)->assertJsonPath('offers', ['total' => 3, 'live' => 0]);

        $rows = DB::table('virtual__products')->where('service_id', $service)->get();
        $this->assertEqualsCanonicalizing(["$russia:pva", "$russia:man", "$noPva:man"], $rows->map(fn ($r) => "$r->country_id:$r->type")->all());
        $man = $rows->first(fn ($r) => $r->country_id == $russia && $r->type === 'man');
        $this->assertSame(['3', '1', ' تلگرام -2', ' کشور روسیه -2', 'image/svg/ru.svg', 4, 0, 10],
            [$man->service_code, $man->country_code, $man->faName, $man->countryName, $man->countryImage, (int) $man->country_sort, (int) $man->status, (int) $man->commission_percent],
            'new rows wait off sale for the price check');

        // Nothing changed: nothing to do. A changed code updates the existing rows and keeps their prices.
        $this->postJson("/admin/api/virtual-numbers/services/$service/rebuild")->assertOk()->assertJsonPath('created', 0)->assertJsonPath('updated', 0);
        DB::table('virtual__products')->where('id', $man->id)->update(['status' => 1, 'toman_price' => 55000]);
        DB::table('virtual__services')->where('id', $service)->update(['man' => '4']);
        $this->postJson("/admin/api/virtual-numbers/services/$service/rebuild")->assertOk()->assertJsonPath('updated', 2);
        $man = DB::table('virtual__products')->find($man->id);
        $this->assertSame(['4', 1, 55000.0], [$man->service_code, (int) $man->status, (float) $man->toman_price]);
    }

    public function test_rebuilding_a_country_checks_each_providers_own_code(): void
    {
        $this->actingAsAdmin();
        // The old button checked the PVA code for the SMS-Man and SMS-Activate rows too.
        $country = $this->country(['pva' => 'empty', 'man' => '7', 'active' => 'empty']);
        $both = $this->service(['pva' => 'opt1', 'man' => '10', 'active' => 'go']);
        $manOff = $this->service(['pva' => 'opt2', 'man' => 'empty', 'active' => 'wa']);

        $this->postJson("/admin/api/virtual-numbers/countries/$country/rebuild")->assertOk()->assertJsonPath('created', 1);
        $rows = DB::table('virtual__products')->where('country_id', $country)->whereIn('service_id', [$both, $manOff])->get();
        $this->assertSame(["$both:man"], $rows->map(fn ($r) => "$r->service_id:$r->type")->all());

        $this->postJson('/admin/api/virtual-numbers/countries/999999/rebuild')->assertNotFound();
    }

    public function test_old_admin_update_buttons_use_the_same_rebuild(): void
    {
        $this->actingAsAdmin();
        $service = $this->service(['pva' => 'opt1', 'man' => 'empty', 'active' => 'empty']);
        $country = $this->country(['pva' => 'empty', 'man' => '7', 'active' => 'empty']);

        $this->get(route('admin.GenerateProduct', $country))->assertRedirect(route('admin.Single-Virtual-Countries', $country));
        $this->assertSame(0, DB::table('virtual__products')->where('country_id', $country)->where('service_id', $service)->count());

        DB::table('virtual__countries')->where('id', $country)->update(['pva' => 'RU']);
        $this->get(route('admin.GenerateService', $service))->assertRedirect(route('admin.edit-Virtual-Services', $service));
        $this->assertSame(1, DB::table('virtual__products')->where('country_id', $country)->where('service_id', $service)->where('type', 'pva')->count());
    }

    /* ---------- Offers ---------- */

    public function test_offers_of_a_service_show_price_and_sale_state(): void
    {
        $this->actingAsAdmin();
        $service = $this->service();
        $russia = $this->country(['sort' => 2]);
        $germany = $this->country(['faName' => 'شماره مجازی کشور آلمان', 'code' => 'DE', 'sort' => 1]);
        $live = $this->offer($service, $russia, 'pva', ['toman_price' => 80080, 'dollar_price' => '0.20', 'countryImage' => 'image/svg/ru.svg']);
        $this->offer($service, $russia, 'man', ['status' => 0, 'toman_price' => null]);
        $this->offer($service, $germany, 'active', ['status' => -1, 'toman_price' => 12000]);

        $this->getJson('/admin/api/virtual-numbers/offers')->assertStatus(422);
        $offers = $this->getJson("/admin/api/virtual-numbers/offers?service=$service")->assertOk()->json('offers');

        $this->assertSame(['کشور آلمان', 'کشور روسیه', 'کشور روسیه'], array_column($offers, 'name'), 'in the site\'s country order');
        $this->assertSame(['gone', 'live', 'waiting'], array_column($offers, 'state'));
        $this->assertSame([3, 1, 2], array_column($offers, 'server'));
        $this->assertSame($live, $offers[1]['id']);
        $this->assertSame(80050, $offers[1]['price'], 'rounded like the storefront');
        $this->assertSame(0.2, $offers[1]['providerPrice']);
        $this->assertSame('/image/svg/ru.svg', $offers[1]['image']);
        $this->assertNull($offers[2]['price']);

        $byCountry = $this->getJson("/admin/api/virtual-numbers/offers?country=$russia")->assertOk()->json('offers');
        $this->assertSame(['تلگرام', 'تلگرام'], array_column($byCountry, 'name'));
    }

    /* ---------- Settings ---------- */

    public function test_settings_save_switches_and_landing_text_without_touching_seo_screen_fields(): void
    {
        $this->actingAsAdmin();
        DB::table('settings')->insert(['id' => 3, 'name' => 'virtual number', 'key' => '{"pva":"1","man":"1","land":null,"active":"1"}']);
        DB::table('pages')->insert(['id' => 1, 'title' => 'شماره مجازی', 'slug' => 'virtual-number', 'h1' => 'خرید شماره مجازی', 'long_detail' => '<p>قدیم</p>', 'related_blogs' => '',
            'seo' => json_encode(['meta_title' => 'عنوان', 'meta_desc' => 'توضیح', 'meta_image' => '/upload/seo/share.png', 'meta' => '<meta>']),
            'faq' => json_encode([['question' => 'تحویل آنی است؟', 'answer' => 'بله']])]);
        $service = $this->service(['pva' => 'opt1', 'man' => 'empty', 'active' => 'x', 'land' => 'empty']);
        $country = $this->country(['pva' => 'RU', 'man' => '1', 'active' => '0', 'land' => '1']);
        $this->offer($service, $country, 'pva');

        $json = $this->getJson('/admin/api/virtual-numbers/settings')->assertOk();
        $json->assertJsonPath('providers', ['pva' => true, 'man' => true, 'active' => true, 'land' => false]);
        $json->assertJsonPath('page.faq', [['q' => 'تحویل آنی است؟', 'a' => 'بله']]);
        $json->assertJsonPath('page.seoHtml', '<meta>');
        $pva = collect($json->json('stats'))->firstWhere('key', 'pva');
        $this->assertSame([1, 1, 1, 1], [$pva['server'], $pva['offers'], $pva['live'], $pva['pairs'] > 0 ? 1 : 0]);

        $this->postJson('/admin/api/virtual-numbers/settings', ['page' => ['faq' => [['q' => '', 'a' => 'x']]]])
            ->assertStatus(422)->assertJsonValidationErrors(['page.faq.0.q']);

        $this->postJson('/admin/api/virtual-numbers/settings', [
            'providers' => ['pva' => true, 'man' => false, 'active' => true, 'land' => true],
            'page' => ['image' => '/upload/virtual-number/hero.jpg', 'content' => '<p>جدید</p>', 'seoHtml' => '', 'faq' => [['q' => 'سؤال', 'a' => 'پاسخ']]],
        ])->assertOk()->assertJsonPath('success', 'تنظیمات شماره مجازی ذخیره شد.');

        $this->assertSame(['pva' => '1', 'man' => null, 'land' => '1', 'active' => '1'], json_decode(DB::table('settings')->find(3)->key, true));
        $page = DB::table('pages')->find(1);
        $this->assertSame(['<p>جدید</p>', '/upload/virtual-number/hero.jpg', 'خرید شماره مجازی'], [$page->long_detail, $page->image, $page->h1]);
        $this->assertSame([['question' => 'سؤال', 'answer' => 'پاسخ']], json_decode($page->faq, true));
        $this->assertSame(['meta_title' => 'عنوان', 'meta_desc' => 'توضیح', 'meta_image' => '/upload/seo/share.png', 'meta' => null], json_decode($page->seo, true));
    }

    public function test_old_settings_form_no_longer_wipes_the_faq_or_share_image(): void
    {
        $this->actingAsAdmin();
        DB::table('settings')->insert(['id' => 3, 'name' => 'virtual number', 'key' => '{}']);
        DB::table('pages')->insert(['id' => 1, 'title' => 'شماره مجازی', 'slug' => 'virtual-number', 'long_detail' => '', 'related_blogs' => '',
            'seo' => json_encode(['meta_image' => '/upload/seo/share.png']), 'faq' => json_encode([['question' => 'س', 'answer' => 'پ']])]);

        $this->post(route('admin.virtualNumber-setting-submit'), ['h1' => 'H1', 'detail' => '<p>x</p>', 'meta_title' => 'عنوان', 'meta_desc' => 'توضیح', 'seo' => '', 'pva_status' => '1'], $this->ajax())->assertOk();
        $page = DB::table('pages')->find(1);
        $this->assertSame([['question' => 'س', 'answer' => 'پ']], json_decode($page->faq, true));
        $this->assertSame('/upload/seo/share.png', json_decode($page->seo, true)['meta_image']);
        $this->assertSame('عنوان', json_decode($page->seo, true)['meta_title']);
    }

    /* ---------- Images and orders ---------- */

    public function test_image_upload_returns_a_public_path(): void
    {
        $this->actingAsAdmin();
        $path = $this->post('/admin/api/virtual-numbers/uploads/image', ['file' => UploadedFile::fake()->image('WhatsApp Icon.png', 64, 64)], $this->ajax())
            ->assertOk()->json('path');
        $this->assertMatchesRegularExpression('#^/upload/virtual-number/whatsapp-icon-\d+\.png$#', $path);
        $this->assertFileExists(public_path(ltrim($path, '/')));
        @unlink(public_path(ltrim($path, '/')));

        $this->post('/admin/api/virtual-numbers/uploads/image', ['file' => UploadedFile::fake()->create('notes.pdf', 10)], $this->ajax())->assertStatus(422);
    }

    public function test_a_number_order_shows_which_server_sold_it(): void
    {
        $this->actingAsAdmin();
        $offer = $this->offer($this->service(), $this->country(), 'man');
        $order = $this->order($this->user(), ['type' => '1']);
        DB::table('order_items')->insert(['order_id' => $order, 'product_id' => $offer, 'price' => 50000,
            'detail' => json_encode(['number' => '79001234567', 'sms' => null, 'numberId' => 5, 'status' => 'pending']), 'created_at' => now(), 'updated_at' => now()]);

        $rows = collect($this->getJson("/admin/api/orders/$order")->assertOk()->json('items.0.rows'));
        $this->assertSame('سرور 2 · SMS-Man', $rows->firstWhere('label', 'سرور')['value']);
    }
}
