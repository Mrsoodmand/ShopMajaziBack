<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Comment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Blog posts and tech news for the React admin. Both live in `blogs` (type blog|news) in the shape the old
 * admin saved, so the storefront (v2\BlogController, status 2 = published) reads them unchanged.
 * The route picks the type (routes/admin.php passes it as a default).
 */
class PostApiController extends Controller
{
    use AdminApi;

    const PER_PAGE = 30;

    /** Stored status → what the admin sees. Only 2 is shown on the site. */
    const STATUS = ['2' => 'published', '1' => 'draft', '-1' => 'hidden'];

    public function index(Request $request)
    {
        $type = $this->type($request);
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status', 'all');

        $base = Blog::where('type', $type);
        if ($search !== '') {
            $base->where(fn ($q) => $q->where('title', 'LIKE', "%$search%")->orWhere('slug', 'LIKE', "%$search%"));
        }
        $counts = ['all' => (clone $base)->count()];
        foreach (self::STATUS as $stored => $key) {
            $counts[$key] = (clone $base)->where('status', $stored)->count();
        }
        $list = clone $base;
        if ($code = array_search($status, self::STATUS, true)) {
            $list->where('status', $code);
        }
        $page = $list->orderByDesc('id')->paginate(self::PER_PAGE, ['id', 'title', 'slug', 'image', 'status', 'view', 'share_at', 'created_at', 'updated_at']);
        $comments = Comment::where('type', 'blog')->where('parent_id', 0)
            ->whereIn('post_id', $page->getCollection()->pluck('id'))->selectRaw('post_id, count(*) c')->groupBy('post_id')->pluck('c', 'post_id');

        return response()->json($this->pageMeta($page) + [
            'counts' => $counts,
            'posts' => $page->getCollection()->map(fn (Blog $b) => [
                'id' => $b->id,
                'title' => $b->title,
                'slug' => $b->slug,
                'image' => $b->image,
                'status' => self::STATUS[(string) $b->status] ?? 'hidden',
                'views' => (int) $b->view,
                'comments' => (int) ($comments[$b->id] ?? 0),
                'publishedAt' => $this->jdate($b->share_at ?: $b->created_at, 'Y/m/d'),
                'updatedAt' => $this->jdate($b->updated_at),
            ])->values(),
        ]);
    }

    public function show(Request $request)
    {
        $type = $this->type($request);
        $id = $request->route('id');
        $b = Blog::where('type', $type)->find($id);
        if (!$b) {
            return $this->missing('این نوشته پیدا نشد.');
        }
        $when = $b->share_at ?: $b->created_at;

        return response()->json(['post' => [
            'id' => $b->id,
            'title' => (string) $b->title,
            'slug' => (string) $b->slug,
            'status' => self::STATUS[(string) $b->status] ?? 'hidden',
            'short' => (string) $b->short_detail,
            'content' => (string) $b->detail,
            'image' => (string) $b->image,
            'imageBig' => (string) $b->image_big,
            'metaTitle' => (string) $b->meta_title,
            'metaDesc' => (string) $b->meta_desc,
            'date' => $this->jdate($when, 'Y/m/d') ?? '',
            'time' => $this->jdate($when, 'H:i') ?? '',
            'faq' => $this->faqFromStore($b->faq),
            'views' => (int) $b->view,
        ]]);
    }

    public function save(Request $request)
    {
        $type = $this->type($request);
        $id = $request->route('id');
        $post = $id ? Blog::where('type', $type)->find($id) : new Blog();
        if (!$post) {
            return $this->missing('این نوشته پیدا نشد.');
        }
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('blogs', 'slug')->where('type', $type)->ignore($post->id)],
            'status' => ['required', Rule::in(array_values(self::STATUS))],
            'short' => 'required|string',
            'content' => 'required|string',
            'image' => 'required|string|max:500',
            'imageBig' => 'nullable|string|max:500',
            'metaTitle' => 'required|string|max:255',
            'metaDesc' => 'required|string|max:1000',
            'date' => ['nullable', 'regex:/^1[34]\d\d\/\d{2}\/\d{2}$/'],
            'time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'faq' => 'array',
            'faq.*.q' => 'nullable|string|max:500',
            'faq.*.a' => 'nullable|string',
        ], $this->messages() + [
            'date.regex' => 'تاریخ را به شکل ۱۴۰۴/۰۸/۰۷ بنویسید.',
            'time.regex' => 'ساعت را به شکل ۱۴:۳۰ بنویسید.',
        ]);

        $shareAt = $post->share_at ?: now();
        if (!empty($data['date'])) {
            // The app runs on Tehran time, the same clock the old admin stored.
            $shareAt = Carbon::parse(jalali_to_gregorian($data['date']) . ' ' . (($data['time'] ?? '') ?: '00:00'));
        }

        $post->forceFill([
            'type' => $type,
            'title' => $data['title'],
            'slug' => $data['slug'],
            'status' => (int) array_search($data['status'], self::STATUS, true),
            'short_detail' => $data['short'],
            'detail' => $data['content'],
            'image' => $data['image'],
            'image_big' => ($data['imageBig'] ?? '') ?: $data['image'],
            'meta_title' => $data['metaTitle'],
            'meta_desc' => $data['metaDesc'],
            'share_at' => $shareAt,
            'admin_id' => Auth::guard('admin')->id(),
        ]);
        if ($type === 'blog') {
            $faq = $this->faqToStore($data['faq'] ?? []);
            $post->faq = $faq ? json_encode($faq, JSON_UNESCAPED_UNICODE) : null;
        }
        $post->save();

        return response()->json(['success' => $id ? 'تغییرات ذخیره شد.' : 'نوشته ساخته شد.', 'id' => $post->id]);
    }

    public function delete(Request $request)
    {
        $type = $this->type($request);
        $id = $request->route('id');
        $post = Blog::where('type', $type)->find($id);
        if (!$post) {
            return $this->missing('این نوشته پیدا نشد.');
        }
        $post->delete();
        return response()->json(['success' => 'نوشته حذف شد.']);
    }

    /** blog or news, set by the route (routes/admin.php). */
    private function type(Request $request): string
    {
        return $request->route('type') === 'news' ? 'news' : 'blog';
    }

    public function upload(Request $request)
    {
        return $this->storeImage($request, 'blog');
    }
}
