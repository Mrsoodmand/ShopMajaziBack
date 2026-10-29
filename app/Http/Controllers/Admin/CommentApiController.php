<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Pages;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Site comments for the React admin. A comment is a `comments` row with parent_id 0; the shop's reply is a child row.
 * Stored status: "Pending" (or 0) waiting, 1 approved (shown on the site), -1 rejected.
 * post_id points at a blog post (type blog), a `pages` row (type page) or a product (type service).
 */
class CommentApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 30;

    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $type = $request->input('type');
        $search = trim((string) $request->input('q', ''));

        $base = Comment::where('parent_id', 0);
        if (in_array($type, ['blog', 'page', 'service'], true)) {
            $base->where('type', $type);
        }
        if ($search !== '') {
            $base->where(fn ($q) => $q->where('name', 'LIKE', "%$search%")->orWhere('mobile', 'LIKE', "%$search%")->orWhere('text', 'LIKE', "%$search%"));
        }
        $counts = [
            'pending' => $this->status(clone $base, 'pending')->count(),
            'approved' => $this->status(clone $base, 'approved')->count(),
            'rejected' => $this->status(clone $base, 'rejected')->count(),
            'all' => (clone $base)->count(),
        ];
        $list = $status === 'all' ? clone $base : $this->status(clone $base, $status);
        // Waiting comments oldest first (a queue); the others newest first.
        $page = ($status === 'pending' ? $list->orderBy('id') : $list->orderByDesc('id'))->paginate(self::PER_PAGE);
        $rows = $page->getCollection();
        $replies = Comment::whereIn('parent_id', $rows->pluck('id'))->orderBy('id')->get()->keyBy('parent_id');
        $titles = $this->titles($rows);

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'comments' => $rows->map(fn (Comment $c) => $this->row($c, $replies[$c->id] ?? null, $titles))->values(),
        ]);
    }

    public function setStatus(Request $request, $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'approved', 'rejected'])]], $this->messages());
        $comment = Comment::where('parent_id', 0)->find($id);
        if (!$comment) {
            return $this->missing('این نظر پیدا نشد.');
        }
        $comment->status = $this->stored($data['status']);
        $comment->save();

        return response()->json(['success' => match ($data['status']) {
            'approved' => 'نظر تأیید شد و در سایت نمایش داده می‌شود.',
            'rejected' => 'نظر رد شد.',
            default => 'نظر به صف بررسی برگشت.',
        }]);
    }

    /** Writes (or rewrites) the shop's reply. The reply is shown at once; the comment itself keeps its status. */
    public function reply(Request $request, $id)
    {
        $data = $request->validate([
            'text' => 'required|string|max:5000',
            'name' => 'nullable|string|max:100',
            'approve' => 'boolean',
        ], $this->messages());
        $comment = Comment::where('parent_id', 0)->find($id);
        if (!$comment) {
            return $this->missing('این نظر پیدا نشد.');
        }
        $reply = Comment::where('parent_id', $comment->id)->first() ?? new Comment();
        $reply->forceFill([
            'text' => $data['text'],
            'name' => ($data['name'] ?? '') ?: 'پشتیبانی شاپ مجازی',
            'parent_id' => $comment->id,
            'post_id' => $comment->post_id,
            'type' => $comment->type,
            'status' => 1,
            'operator' => Auth::guard('admin')->id(),
        ])->save();
        if (!empty($data['approve'])) {
            $comment->status = 1;
            $comment->save();
        }

        return response()->json(['success' => 'پاسخ ثبت شد.']);
    }

    /** Approve, reject or delete many at once (the spam queue). Deleting also removes the replies. */
    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
            'action' => ['required', Rule::in(['approved', 'rejected', 'pending', 'delete'])],
        ], $this->messages() + ['ids.required' => 'هیچ نظری انتخاب نشده است.']);
        $ids = Comment::where('parent_id', 0)->whereIn('id', $data['ids'])->pluck('id');
        if ($data['action'] === 'delete') {
            Comment::whereIn('parent_id', $ids)->delete();
            Comment::whereIn('id', $ids)->delete();
            return response()->json(['success' => count($ids) . ' نظر حذف شد.', 'count' => count($ids)]);
        }
        Comment::whereIn('id', $ids)->update(['status' => $this->stored($data['action'])]);

        return response()->json(['success' => count($ids) . ' نظر به‌روز شد.', 'count' => count($ids)]);
    }

    /* ---------- Helpers ---------- */

    private function status($query, string $status)
    {
        return match ($status) {
            'approved' => $query->where('status', '1'),
            'rejected' => $query->where('status', '-1'),
            default => $query->where(fn ($q) => $q->whereIn('status', ['Pending', '0'])->orWhereNull('status')),
        };
    }

    private function stored(string $status): string
    {
        return match ($status) {
            'approved' => '1',
            'rejected' => '-1',
            default => 'Pending',
        };
    }

    private function label($stored): string
    {
        return match ((string) $stored) {
            '1' => 'approved',
            '-1' => 'rejected',
            default => 'pending',
        };
    }

    /** "type:id" → title and storefront path of what each comment is about. */
    private function titles($rows): array
    {
        $ids = fn (string $type) => $rows->where('type', $type)->pluck('post_id')->unique()->filter()->values();
        $out = [];
        foreach (Blog::whereIn('id', $ids('blog'))->get(['id', 'title', 'slug', 'type']) as $b) {
            $out['blog:' . $b->id] = ['title' => $b->title, 'path' => ($b->type === 'news' ? '/tech-news/' : '/blog/') . $b->slug];
        }
        foreach (Pages::whereIn('id', $ids('page'))->get(['id', 'title']) as $p) {
            $out['page:' . $p->id] = ['title' => 'صفحه ' . $p->title, 'path' => null];
        }
        foreach (Product::whereIn('id', $ids('service'))->get(['id', 'faName', 'slug']) as $p) {
            $out['service:' . $p->id] = ['title' => $p->faName, 'path' => null];
        }
        return $out;
    }

    private function row(Comment $c, ?Comment $reply, array $titles): array
    {
        $about = $titles[$c->type . ':' . $c->post_id] ?? null;
        return [
            'id' => $c->id,
            'name' => (string) $c->name,
            'mobile' => $c->mobile,
            'text' => (string) $c->text,
            'status' => $this->label($c->status),
            'type' => $c->type,
            'about' => $about['title'] ?? null,
            'aboutPath' => $about['path'] ?? null,
            // Spam is mostly links; flag them so the list can warn.
            'hasLink' => (bool) preg_match('~https?://|<a\s~i', (string) $c->text),
            'createdAt' => $this->jdate($c->created_at),
            'reply' => $reply ? ['text' => (string) $reply->text, 'name' => (string) $reply->name, 'createdAt' => $this->jdate($reply->created_at)] : null,
        ];
    }
}
