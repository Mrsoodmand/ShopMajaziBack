<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\Virtual_Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BlogController extends Controller
{

    /* سایت مپ بلاگ */
    public function blogSiteMap()
    {
        $Posts = Blog::where('status', 2)->orderby('id', 'DESC')->select('slug','created_at','updated_at')->where('type', 'blog')->get();
        return response()->view('site-v2.sitemap.blogSitemap', [
            'Posts' => $Posts
        ])->header('Content-Type', 'text/xml');
    }

    /* سایت مپ نیوز */
    public function newsSiteMap()
    {
        $Posts = Blog::where('status', 2)->orderby('id', 'DESC')->select('slug','created_at','updated_at')->where('type', 'news')->get();
        return response()->view('site-v2.sitemap.techNewsSitemap', [
            'Posts' => $Posts
        ])->header('Content-Type', 'text/xml');
    }

    /* لیست بلاگ ها */
    public function index(Request $request)
    {
        return $this->listing('blog');
    }

    /* لیست اخبار */
    public function news(Request $request)
    {
        return $this->listing('news');
    }

    private function listing($Type)
    {
        $Blogs = Blog::where('status', 2)->orderby('id', 'DESC')->select('short_detail', 'title', 'slug', 'image', 'created_at')->where('type', $Type)->paginate(18);
        $News = Blog::orderby('view', 'DESC')->select('created_at', 'short_detail', 'title', 'slug', 'image', 'image_big')->take(12)->where('type', 'news')->where('status', 2)->get();
        $Jdf = new Jdf();
        return view('site-v2.blog.index', compact('Blogs', 'Jdf', 'News', 'Type'));
    }

    /* جزئیات بلاگ */
    public function single($Slug, Request $request)
    {
        return $this->show($Slug, 'blog');
    }

    /* جزئیات خبر */
    public function singleNews($Slug, Request $request)
    {
        return $this->show($Slug, 'news');
    }

    // A post is served only under its own section (/blog or /tech-news); the other one 301s to it.
    private function show($Slug, $Type)
    {
        $Blog = Blog::where('status', 2)->where('slug', $Slug)->first();
        if (is_null($Blog)) {
            abort(404);
        }
        if ($Blog->type != $Type) {
            return redirect(($Blog->type == 'news' ? 'tech-news/' : 'blog/') . $Blog->slug, 301);
        }

        $jdf = new Jdf();
        $Jdf = $jdf;
        $TopBlogs = Blog::where('id', '!=', $Blog->id)->orderby('view', 'DESC')->select('created_at', 'short_detail', 'title', 'slug', 'image')->take(4)->where('type', 'blog')->where('status', 2)->get();
        $News = Blog::where('id', '!=', $Blog->id)->orderby('id', 'DESC')->select('created_at', 'short_detail', 'title', 'slug', 'image')->take(6)->where('type', 'news')->where('status', 2)->get();
        $Related = Blog::where('id', '!=', $Blog->id)->orderby('id', 'DESC')->select('created_at', 'short_detail', 'title', 'slug', 'image_big')->where('type', $Type)->where('status', 2)->take(12)->get();
        Blog::where('id', $Blog->id)->increment('view'); // don't touch updated_at on every view

        $Comment = Comment::where('type', 'blog')->where('post_id', $Blog->id)->where('status', 1)->orderby('id', 'DESC')->select('parent_id', 'name', 'text', 'created_at', 'id')->get();

        return view('site-v2.blog.single', compact('Blog', 'jdf', 'Related', 'TopBlogs', 'Jdf', 'News', 'Comment', 'Type'));
    }

    public function redirect($slug)
    {
        return redirect('blog/'.$slug, 301);
    }
}
