<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Pages;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Customer comments for the Next.js storefront (Front/src/lib/comments.ts).
 *
 * Comments live in the `comments` table exactly as the old site stored them, so the admin's comment queue
 * (React admin › Comments) moderates them: `type` + `post_id` say what a comment is about (page = a `pages` row,
 * service = a product, blog = a post); status 1 is approved and shown, "Pending" waits for review, -1 is rejected.
 * A reply is a row with parent_id = the comment; it is written by the shop (its `name` column holds the asker's
 * name, so it is never shown).
 *
 * A comment is addressed with a key: "page:<name>" for the named pages below, "service:<product id>" or
 * "blog:<post id>".
 */
class StorefrontCommentController extends Controller
{
    use ApiResponse;

    // The `pages` rows each storefront page's comments belong to (the old site used the same ids).
    // rent-number and telegram-stars were added by migration 2026_11_14_120000_add_comment_pages.
    public const PAGES = [
        'virtual-number' => 1,
        'apple-id' => 4,
        'static-ip' => 5,
        'google-voice' => 6,
        'accounts' => 7,
        'rent-number' => 8,
        'telegram-stars' => 9,
    ];

    /** GET comments?key=page:virtual-number — the approved comments, newest first, each with the shop's reply. */
    public function index(Request $request): JsonResponse
    {
        $target = $this->target((string) $request->query('key', ''));
        if (!$target) {
            return $this->error('Unknown comment key', 404);
        }
        [$type, $id] = $target;
        $rows = Comment::where('type', $type)->where('post_id', (string) $id)->where('status', '1')
            ->orderByDesc('id')->get(['id', 'parent_id', 'name', 'text', 'created_at']);
        return $this->success(['comments' => $this->threads($rows)]);
    }

    /**
     * GET testimonials — approved comments that tell an experience (not questions), from the product pages, for the
     * home page. Nothing here is written by the shop: what customers posted and the admin approved.
     */
    public function testimonials(): JsonResponse
    {
        $pages = array_flip(self::PAGES);
        $rows = Comment::whereIn('type', ['page', 'service'])->where('parent_id', 0)->where('status', '1')
            ->orderByDesc('id')->get(['id', 'type', 'post_id', 'name', 'text', 'created_at'])
            ->filter(fn (Comment $c) => $this->isExperience((string) $c->text))
            ->take(12);
        $products = Product::whereIn('id', $rows->where('type', 'service')->pluck('post_id'))->pluck('faName', 'id');
        return $this->success(['testimonials' => $rows->map(fn (Comment $c) => [
            'id' => $c->id,
            'name' => $this->name($c->name),
            'text' => trim((string) $c->text),
            'date' => optional($c->created_at)->toIso8601String(),
            // What it is about: a page name (translated by the storefront) or a product's title.
            'about' => $c->type === 'page' ? ($pages[(int) $c->post_id] ?? null) : null,
            'product' => $c->type === 'service' ? ($products[(int) $c->post_id] ?? null) : null,
        ])->values()]);
    }

    /** POST comments {key, name, text} — saved as Pending; it appears once the admin approves it. */
    public function store(Request $request): JsonResponse
    {
        // Honeypot: a field people never see. Bots that fill it get the same answer and nothing is saved.
        if (filled($request->input('website'))) {
            return $this->success(null, 'دیدگاه شما ثبت شد و پس از بررسی نمایش داده می‌شود.');
        }
        $data = $request->validate([
            'key' => ['required', 'string', 'max:40'],
            'name' => ['required', 'string', 'min:2', 'max:60'],
            'text' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'name.required' => 'نام خود را بنویسید.',
            'name.min' => 'نام خود را بنویسید.',
            'text.required' => 'متن دیدگاه را بنویسید.',
            'text.min' => 'دیدگاه باید دست‌کم ۱۰ حرف باشد.',
            'text.max' => 'دیدگاه حداکثر ۱۰۰۰ حرف می‌تواند باشد.',
        ]);
        $target = $this->target($data['key']);
        if (!$target) {
            return $this->error('Unknown comment key', 404);
        }
        [$type, $id] = $target;
        $comment = new Comment();
        $comment->type = $type;
        $comment->post_id = (string) $id;
        $comment->parent_id = 0;
        $comment->name = strip_tags(trim($data['name']));
        $comment->text = strip_tags(trim($data['text']));
        $comment->status = 'Pending';
        $comment->save();
        return $this->success(null, 'دیدگاه شما ثبت شد و پس از بررسی نمایش داده می‌شود.', 201);
    }

    /** "page:apple-id" → ['page', 4]; "service:3293" → ['service', 3293] when that product is on sale; else null. */
    private function target(string $key): ?array
    {
        if (!preg_match('/^(page|service|blog):([a-z0-9-]{1,30})$/', $key, $m)) {
            return null;
        }
        [, $type, $ref] = $m;
        if ($type === 'page') {
            $id = self::PAGES[$ref] ?? null;
            return $id && Pages::whereKey($id)->exists() ? ['page', $id] : null;
        }
        if (!ctype_digit($ref)) {
            return null;
        }
        $exists = $type === 'service'
            ? Product::whereKey((int) $ref)->where('status', 1)->exists()
            : Blog::whereKey((int) $ref)->exists();
        return $exists ? [$type, (int) $ref] : null;
    }

    private function threads(Collection $rows): array
    {
        $replies = $rows->where('parent_id', '>', 0)->groupBy('parent_id');
        return $rows->where('parent_id', 0)->map(fn (Comment $c) => [
            'id' => $c->id,
            'name' => $this->name($c->name),
            'text' => trim((string) $c->text),
            'date' => optional($c->created_at)->toIso8601String(),
            'replies' => ($replies[$c->id] ?? collect())->sortBy('id')->map(fn (Comment $r) => [
                'text' => trim((string) $r->text),
                'date' => optional($r->created_at)->toIso8601String(),
            ])->values(),
        ])->values()->all();
    }

    /**
     * A testimonial tells how buying went; a question or a request ("آیا…", "چطور…", "…می‌خوام") belongs on its page
     * with the shop's answer, not on the home page.
     */
    private function isExperience(string $text): bool
    {
        $text = trim($text);
        $question = '/[?؟]|(^|\s)(آیا|چطور|چگونه|چرا|کجا|کی|چند|چه\s*زمانی|چقدر)(\s|$)|(^|\s)(می[\x{200C}\s]?(خوام|خواستم|شه|تونم)|میخوام|میخواستم|میشه|میتونم)/u';
        return mb_strlen($text) >= 25 && !preg_match($question, $text);
    }

    private function name(?string $name): string
    {
        $name = trim((string) $name);
        return $name !== '' ? $name : 'کاربر شاپ مجازی';
    }
}
