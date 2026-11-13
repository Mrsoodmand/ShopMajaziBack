<?php

namespace Tests\Feature\Storefront;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The public blog API the Next.js site reads (/api/v1/storefront/blog*): every published post and news item with its
 * topic, and one post with its cleaned body, contents, related posts and the next one.
 */
class BlogTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('blogs')->delete();
    }

    private function article(string $slug, array $attrs = []): int
    {
        return DB::table('blogs')->insertGetId(array_merge([
            'title' => 'نوشته ' . $slug, 'slug' => $slug, 'type' => 'blog', 'status' => 2, 'short_detail' => '<p>خلاصه &amp; بیشتر</p>',
            'detail' => '<p>یک دو سه</p>', 'image' => 'upload/blog/small.webp', 'image_big' => 'upload/blog/big.webp', 'view' => 5,
            'share_at' => '2025-12-23 10:28:00', 'created_at' => '2025-12-23 11:01:24', 'updated_at' => '2025-12-23 11:01:24',
        ], $attrs));
    }

    public function test_list_has_published_posts_and_news_newest_first_with_topics(): void
    {
        $this->article('telegram-tips');
        $this->article('virtual-number-guide', ['topic' => 'apple']); // the admin's choice beats the guess
        $this->article('iphone-15-news', ['type' => 'news']);
        $this->article('draft-post', ['status' => 1]);
        $this->article('hidden-post', ['status' => -1]);

        $posts = $this->getJson('/api/v1/storefront/blog')->assertOk()->json('data.posts');

        $this->assertSame(['iphone-15-news', 'virtual-number-guide', 'telegram-tips'], array_column($posts, 'slug'));
        $this->assertSame(['news', 'apple', 'telegram'], array_column($posts, 'topic'));
        $this->assertSame('خلاصه & بیشتر', $posts[0]['excerpt']);
        $this->assertSame('/upload/blog/big.webp', $posts[0]['image']);
        $this->assertSame('/upload/blog/small.webp', $posts[0]['thumb']);
        $this->assertSame('2025-12-23T10:28:00+03:30', $posts[0]['published_at'], 'the admin publish date');
        $this->assertSame(1, $posts[0]['reading_minutes']);

        $news = $this->getJson('/api/v1/storefront/blog?type=news')->assertOk()->json('data.posts');
        $this->assertSame(['iphone-15-news'], array_column($news, 'slug'));
    }

    public function test_topic_is_guessed_from_slug_and_title(): void
    {
        foreach (['how-to-get-esim' => 'numbers', 'telegram-account-with-virtual-number' => 'numbers', 'recover-icloud' => 'apple',
            'grow-instagram' => 'social', 'chatgpt-prompts' => 'guides'] as $slug => $topic) {
            $this->article($slug);
            $this->assertSame($topic, $this->getJson("/api/v1/storefront/blog/$slug")->json('data.post.topic'), $slug);
        }
        $this->article('untitled-1', ['title' => 'آموزش تلگرام']);
        $this->assertSame('telegram', $this->getJson('/api/v1/storefront/blog/untitled-1')->json('data.post.topic'));
    }

    public function test_post_body_is_cleaned_and_has_contents(): void
    {
        $this->article('guide', ['detail' => '<h1 style="color:red">عنوان</h1><p>&nbsp;</p><script>alert(1)</script>'
            . '<p onclick="x()"><span style="font-size:20px">متن <a href="https://shopmajazi.com/apple-id" target="_blank">اپل</a>'
            . ' <a href="javascript:alert(1)">بد</a></span></p><img src="https://www.shopmajazi.com/upload/a.webp" style="width:1920px">'
            . '<h3>نکته اول</h3><iframe src="https://evil.example/x"></iframe><table><tr><td>۱</td></tr></table>']);

        $post = $this->getJson('/api/v1/storefront/blog/guide')->assertOk()->json('data.post');
        $html = $post['html'];

        foreach (['<script', 'onclick', 'style=', 'javascript:', 'evil.example', '<h1', '<span', '&nbsp;'] as $gone) {
            $this->assertStringNotContainsString($gone, $html, $gone);
        }
        $this->assertStringContainsString('<h2 id="s-1">عنوان</h2>', $html);
        $this->assertStringContainsString('<a href="/apple-id" target="_blank" rel="noopener noreferrer">اپل</a>', $html);
        $this->assertStringContainsString('<img src="/upload/a.webp" alt="" loading="lazy" decoding="async">', $html);
        $this->assertStringContainsString('<div class="table-wrap"><table>', $html);
        $this->assertSame([['id' => 's-1', 'text' => 'عنوان', 'level' => 2], ['id' => 's-2', 'text' => 'نکته اول', 'level' => 3]], $post['toc']);
        $this->assertArrayNotHasKey('content', $post, 'only the cleaned body is sent');
    }

    public function test_related_prefers_the_same_topic_and_next_wraps_around(): void
    {
        $this->article('telegram-a');
        $this->article('apple-b');
        $this->article('telegram-c');
        $this->article('telegram-d');

        $data = $this->getJson('/api/v1/storefront/blog/telegram-a')->assertOk()->json('data');
        $this->assertSame(['telegram-d', 'telegram-c', 'apple-b'], array_column($data['related'], 'slug'));
        $this->assertSame('telegram-d', $data['next']['slug'], 'the oldest post leads to the newest');

        $this->assertSame('telegram-c', $this->getJson('/api/v1/storefront/blog/telegram-d')->json('data.next.slug'));
    }

    public function test_drafts_and_unknown_posts_are_not_found(): void
    {
        $this->article('draft-post', ['status' => 1]);
        $this->getJson('/api/v1/storefront/blog/draft-post')->assertNotFound();
        $this->getJson('/api/v1/storefront/blog/no-such-post')->assertNotFound();
    }

    public function test_a_view_does_not_change_the_last_edit_date(): void
    {
        $id = $this->article('counted');
        $this->postJson('/api/v1/storefront/blog/counted/view')->assertOk();

        $row = DB::table('blogs')->find($id);
        $this->assertSame(6, (int) $row->view);
        $this->assertSame('2025-12-23 11:01:24', $row->updated_at);
    }
}
