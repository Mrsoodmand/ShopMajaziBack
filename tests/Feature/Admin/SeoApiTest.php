<?php

namespace Tests\Feature\Admin;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * React admin › SEO: H1, meta title, meta description and share image per page, stored in the old `pages` table
 * (App\Services\PageSeo), served to the storefront and used by the old about / contact / FAQ pages.
 */
class SeoApiTest extends AdminTestCase
{
    private function legacyRow(int $id, array $attrs = []): void
    {
        DB::table('pages')->insert(array_merge([
            'id' => $id, 'title' => 'صفحه ' . $id, 'h1' => null, 'long_detail' => '', 'related_blogs' => '', 'detail' => '{"price":1}',
            'seo' => json_encode(['meta_title' => null, 'meta_desc' => null, 'meta' => '<meta name="x">', 'meta_image' => null]),
        ], $attrs));
    }

    public function test_lists_every_page_with_what_the_old_admin_already_set(): void
    {
        $this->actingAsAdmin();
        $this->legacyRow(1, ['h1' => 'خرید شماره مجازی', 'seo' => json_encode(['meta_title' => 'شماره مجازی ارزان', 'meta_desc' => 'توضیح', 'meta_image' => '/upload/vn.jpg'])]);

        $pages = collect($this->getJson('/admin/api/seo-pages')->assertOk()->json('pages'))->keyBy('key');

        $this->assertSame([
            'virtual-number', 'google-voice', 'rent-number', 'apple-id', 'apple-id-usa', 'apple-id-custom', 'accounts',
            'telegram-stars', 'telegram-premium', 'telegram-ads', 'virtual-service', 'about-us', 'contact-us', 'faq',
        ], $pages->keys()->all());
        $this->assertSame('خرید شماره مجازی', $pages['virtual-number']['h1']);
        $this->assertSame('شماره مجازی ارزان', $pages['virtual-number']['title']);
        $this->assertSame('/upload/vn.jpg', $pages['virtual-number']['image']);
        $this->assertNull($pages['rent-number']['title']);
        $this->assertSame('درباره ما | شاپ مجازی', $pages['about-us']['default']['title']);
    }

    public function test_saving_a_legacy_page_keeps_its_other_settings(): void
    {
        $this->actingAsAdmin();
        $this->legacyRow(4);

        $this->postJson('/admin/api/seo-pages/google-voice', [
            'h1' => 'خرید گوگل ویس', 'title' => 'گوگل ویس دائمی', 'description' => 'شماره آمریکا', 'image' => '/upload/seo/gv.jpg',
        ])->assertOk()->assertJsonPath('page.title', 'گوگل ویس دائمی');

        $row = DB::table('pages')->find(4);
        $seo = json_decode($row->seo, true);
        $this->assertSame('خرید گوگل ویس', $row->h1);
        $this->assertSame(['meta_title' => 'گوگل ویس دائمی', 'meta_desc' => 'شماره آمریکا', 'meta' => '<meta name="x">', 'meta_image' => '/upload/seo/gv.jpg'], $seo);
        $this->assertSame('{"price":1}', $row->detail); // Google Voice prices live in the same row
        $this->assertSame(1, DB::table('pages')->count());
    }

    public function test_new_pages_get_a_row_and_empty_values_fall_back_to_the_page_text(): void
    {
        $this->actingAsAdmin();

        $this->postJson('/admin/api/seo-pages/telegram-stars', ['h1' => 'خرید استارز تلگرام', 'title' => '', 'description' => null])->assertOk();
        $this->postJson('/admin/api/seo-pages/telegram-stars', ['h1' => 'خرید استارز', 'title' => 'استارز ارزان'])->assertOk();

        $rows = DB::table('pages')->where('slug', 'telegram-stars')->get();
        $this->assertCount(1, $rows);
        $this->assertSame('خرید استارز', $rows[0]->h1);

        $seo = $this->getJson('/api/v1/storefront/seo')->assertOk()->json('data.pages');
        $this->assertSame(['h1' => 'خرید استارز', 'title' => 'استارز ارزان'], $seo['telegram-stars']);
        $this->assertArrayNotHasKey('telegram-ads', $seo);
    }

    public function test_storefront_gets_image_paths_it_can_load(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/seo-pages/accounts', ['image' => 'upload/seo/subs.jpg'])->assertStatus(422)->assertJsonValidationErrors('image');
        $this->postJson('/admin/api/seo-pages/accounts', ['image' => '/upload/seo/subs.jpg'])->assertOk();

        $this->assertSame('/upload/seo/subs.jpg', $this->getJson('/api/v1/storefront/seo')->json('data.pages.accounts.image'));
    }

    public function test_unknown_pages_and_long_texts_are_refused(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/seo-pages/blog', ['h1' => 'x'])->assertNotFound()->assertJsonPath('error.0', 'این صفحه در فهرست سئو نیست.');
        $this->postJson('/admin/api/seo-pages/faq', ['description' => str_repeat('ا', 501)])->assertStatus(422)->assertJsonValidationErrors('description');
    }

    public function test_viewing_needs_page_list_and_saving_needs_edit_page(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::firstOrCreate(['name' => 'page-list', 'guard_name' => 'admin']);
        $viewer = $this->admin(false);
        $viewer->givePermissionTo('page-list');
        $this->actingAsAdmin($viewer);

        $this->getJson('/admin/api/seo-pages')->assertOk();
        $this->postJson('/admin/api/seo-pages/faq', ['h1' => 'x'])->assertForbidden();

        $this->actingAsAdmin($this->admin(false));
        $this->getJson('/admin/api/seo-pages')->assertForbidden();
    }

    public function test_share_image_upload(): void
    {
        $this->actingAsAdmin();
        $path = $this->post('/admin/api/uploads/seo-image', ['file' => UploadedFile::fake()->image('Stars Share.png', 1200, 630)], $this->ajax())
            ->assertOk()->json('path');
        $this->assertMatchesRegularExpression('#^/upload/seo/stars-share-\d+\.png$#', $path);
        @unlink(public_path(ltrim($path, '/')));

        $this->post('/admin/api/uploads/seo-image', ['file' => UploadedFile::fake()->create('logo.svg', 4, 'image/svg+xml')], $this->ajax())
            ->assertStatus(422);
    }

    public function test_old_about_page_uses_the_admin_texts(): void
    {
        $this->actingAsAdmin();
        $this->get('/about-us')->assertOk()->assertSee('<title>درباره ما | شاپ مجازی</title>', false);

        $this->postJson('/admin/api/seo-pages/about-us', [
            'h1' => 'داستان شاپ مجازی', 'title' => 'درباره شاپ مجازی', 'description' => 'از ۱۳۹۸ کنار شما', 'image' => '/upload/seo/about.jpg',
        ])->assertOk();

        $this->get('/about-us')->assertOk()
            ->assertSee('<title>درباره شاپ مجازی</title>', false)
            ->assertSee('داستان شاپ مجازی</h1>', false)
            ->assertSee('<meta name="description" content="از ۱۳۹۸ کنار شما">', false)
            ->assertSee('/upload/seo/about.jpg', false);
    }
}
