<?php

namespace Tests\Feature\Admin;

use Illuminate\Support\Facades\DB;

/** Blog, news, pages and comments in the React admin. */
class ContentApiTest extends AdminTestCase
{
    private function postData(array $over = []): array
    {
        return array_merge([
            'title' => 'راهنمای شماره مجازی',
            'slug' => 'virtual-number-guide-' . uniqid(),
            'status' => 'published',
            'short' => 'خلاصه',
            'content' => '<p>متن</p>',
            'image' => '/upload/blog/a.webp',
            'metaTitle' => 'عنوان سئو',
            'metaDesc' => 'توضیح سئو',
            'date' => '1404/08/07',
            'time' => '10:30',
            'faq' => [['q' => 'سؤال؟', 'a' => 'پاسخ.'], ['q' => '', 'a' => '']],
        ], $over);
    }

    public function test_blog_needs_its_permission(): void
    {
        $this->actingAsAdmin($this->admin(super: false));
        $this->getJson('/admin/api/posts/blog')->assertForbidden();
        $this->postJson('/admin/api/posts/news', $this->postData())->assertForbidden();
    }

    public function test_blog_post_create_edit_list_delete(): void
    {
        $this->actingAsAdmin();
        $this->postJson('/admin/api/posts/blog', ['slug' => 'Bad Slug'])->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'slug', 'short', 'content', 'image', 'metaTitle', 'metaDesc']);

        $id = $this->postJson('/admin/api/posts/blog', $this->postData())->assertOk()->json('id');
        $row = DB::table('blogs')->find($id);
        $this->assertSame('blog', $row->type);
        $this->assertSame(2, (int) $row->status);
        $this->assertSame('2025-10-29 10:30:00', $row->share_at);
        $this->assertSame([['question' => 'سؤال؟', 'answer' => 'پاسخ.']], json_decode($row->faq, true));
        $this->assertSame('/upload/blog/a.webp', $row->image_big, 'big image falls back to the main one');

        $post = $this->getJson("/admin/api/posts/blog/$id")->assertOk()->json('post');
        $this->assertSame('1404/08/07', $post['date']);
        $this->assertSame('10:30', $post['time']);
        $this->assertSame([['q' => 'سؤال؟', 'a' => 'پاسخ.']], $post['faq']);

        $this->postJson("/admin/api/posts/blog/$id", $this->postData(['slug' => $post['slug'], 'status' => 'draft']))->assertOk();
        $this->assertSame(1, (int) DB::table('blogs')->where('id', $id)->value('status'));

        $list = $this->getJson('/admin/api/posts/blog?status=draft&q=' . urlencode($post['slug']))->assertOk();
        $this->assertSame([$id], collect($list->json('posts'))->pluck('id')->all());
        $this->getJson("/admin/api/posts/news/$id")->assertNotFound(); // a blog post is not news

        $this->postJson("/admin/api/posts/blog/$id/delete")->assertOk();
        $this->assertNull(DB::table('blogs')->find($id));
    }

    public function test_slug_is_unique_per_type(): void
    {
        $this->actingAsAdmin();
        $slug = 'same-' . uniqid();
        $this->postJson('/admin/api/posts/blog', $this->postData(['slug' => $slug]))->assertOk();
        $this->postJson('/admin/api/posts/blog', $this->postData(['slug' => $slug]))->assertStatus(422)->assertJsonValidationErrors('slug');
        $this->postJson('/admin/api/posts/news', $this->postData(['slug' => $slug]))->assertOk();
        $this->assertNull(DB::table('blogs')->where('type', 'news')->where('slug', $slug)->value('faq'), 'news has no FAQ');
    }

    public function test_page_editor_keeps_other_detail_keys_and_saves_prices(): void
    {
        $this->actingAsAdmin();
        DB::table('pages')->updateOrInsert(['id' => 3], ['title' => 'اپل آیدی', 'h1' => 'قدیمی', 'long_detail' => '', 'related_blogs' => '',
            'detail' => json_encode(['custom_price' => '1', 'normal_price' => '2', 'extra' => 'keep']), 'seo' => json_encode(['meta' => '<meta x>'])]);

        $page = $this->getJson('/admin/api/pages/3')->assertOk()->json('page');
        $this->assertSame('apple-id', $page['kind']);
        $this->assertSame(2, $page['settings']['normalPrice']);

        $this->postJson('/admin/api/pages/3', ['h1' => 'خرید اپل آیدی', 'content' => '<p>x</p>', 'faq' => [['q' => 'q', 'a' => 'a']],
            'metaTitle' => 'عنوان', 'headHtml' => '<meta x>', 'settings' => ['normalPrice' => null, 'customPrice' => 500000]])
            ->assertStatus(422)->assertJsonValidationErrors('settings.normalPrice');

        $this->postJson('/admin/api/pages/3', ['h1' => 'خرید اپل آیدی', 'content' => '<p>x</p>', 'faq' => [['q' => 'q', 'a' => 'a']],
            'metaTitle' => 'عنوان', 'headHtml' => '<meta x>', 'settings' => ['normalPrice' => 320000, 'customPrice' => 500000]])->assertOk();
        $row = DB::table('pages')->find(3);
        $this->assertSame(['custom_price' => '500000', 'normal_price' => '320000', 'extra' => 'keep'], json_decode($row->detail, true));
        $seo = json_decode($row->seo, true);
        $this->assertSame('عنوان', $seo['meta_title']);
        $this->assertSame('<meta x>', $seo['meta']);
        $this->assertSame('خرید اپل آیدی', $row->h1);
    }

    public function test_comments_queue_reply_and_bulk(): void
    {
        $this->actingAsAdmin();
        $blog = DB::table('blogs')->insertGetId(['title' => 'نوشته', 'slug' => 'p-' . uniqid(), 'type' => 'blog', 'status' => 2]);
        $ids = [];
        foreach (['Pending', 'Pending', '1', '-1'] as $i => $status) {
            $ids[] = DB::table('comments')->insertGetId(['post_id' => $blog, 'type' => 'blog', 'name' => "n$i", 'text' => $i === 1 ? '<a href=https://spam.example>x</a>' : 'سلام',
                'status' => $status, 'parent_id' => 0, 'created_at' => now()]);
        }
        $res = $this->getJson('/admin/api/comments?status=pending&q=n')->assertOk();
        $this->assertGreaterThanOrEqual(2, $res->json('counts.pending'));
        $mine = collect($res->json('comments'))->whereIn('id', $ids)->values();
        $this->assertSame([$ids[0], $ids[1]], $mine->pluck('id')->all(), 'pending queue is oldest first');
        $this->assertSame('نوشته', $mine[0]['about']);
        $this->assertTrue($mine[1]['hasLink']);

        $this->postJson("/admin/api/comments/{$ids[0]}/reply", ['text' => 'ممنون', 'approve' => true])->assertOk();
        $this->assertSame('1', DB::table('comments')->where('id', $ids[0])->value('status'));
        $this->assertSame('ممنون', DB::table('comments')->where('parent_id', $ids[0])->value('text'));
        $this->postJson("/admin/api/comments/{$ids[0]}/reply", ['text' => 'ویرایش شد'])->assertOk();
        $this->assertSame(1, DB::table('comments')->where('parent_id', $ids[0])->count(), 'one reply per comment');

        $this->postJson("/admin/api/comments/{$ids[3]}/status", ['status' => 'approved'])->assertOk();
        $this->assertSame('1', DB::table('comments')->where('id', $ids[3])->value('status'));

        $this->postJson('/admin/api/comments/bulk', ['ids' => [$ids[0], $ids[1]], 'action' => 'delete'])->assertOk()->assertJson(['count' => 2]);
        $this->assertSame(0, DB::table('comments')->whereIn('id', [$ids[0], $ids[1]])->orWhere('parent_id', $ids[0])->count());
    }

    public function test_comment_actions_need_accept_permission(): void
    {
        $admin = $this->admin(super: false);
        \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'comments-list', 'guard_name' => 'admin']);
        $admin->givePermissionTo('comments-list');
        $this->actingAsAdmin($admin);
        $this->getJson('/admin/api/comments')->assertOk();
        $this->postJson('/admin/api/comments/bulk', ['ids' => [1], 'action' => 'delete'])->assertForbidden();
    }
}
