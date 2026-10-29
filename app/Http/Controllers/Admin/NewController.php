<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NewController extends Controller
{

    /* لیست بلاگ ها */
    public function index(Request $request)
    {
        $list = Blog::orderby('id', 'DESC')->where('type','news');
        if ($request->has('search') && $request->input('search') != ' ') {
            $list->where('title', 'Like', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(40);
        $jdf = new Jdf();
        return view('admin-v2.news.index', compact(
            'list', 'jdf'
        ));
    }

    /* افزودن بلاگ */
    public function create()
    {
        return view('admin-v2.news.create');
    }

    /*ثبت بلاگ */
    public function store(Request $request)
    {

        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required',
            'short_detail' => 'required',
            'detail' => 'required',
            'meta_title' => 'required',
            'meta_desc' => 'required',
            'image_gallery' => 'required',
        ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Blog = new Blog();
            $Blog->title = $request->input('title');
            $Blog->slug = str_replace(' ','-',$request->input('slug'));
            $Blog->short_detail = $request->input('short_detail');
            $Blog->detail = $request->input('detail');
            $Blog->image = $request->input('image_gallery');
            $Blog->image_big = $request->input('image_gallery_2');
            $Blog->meta_title = $request->input('meta_title');
            $Blog->status = $request->input('status');
            $Blog->meta_desc = $request->input('meta_desc');
            $Blog->admin_id = Auth::guard('admin')->user()->id;
            $Blog->seo = $request->input('seo');
            $Blog->type = 'news';
            $Blog->save();

            return success($request, 'بلاگ با موفقیت ثبت شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ویرایش بلاگ */
    public function edit($id)
    {
        $edit = Blog::find($id);
        return view('admin-v2.news.create', compact('edit'));
    }

    /* بروز رسانی بلاگ */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'slug' => 'required',
            'short_detail' => 'required',
            'detail' => 'required',
            'meta_title' => 'required',
            'meta_desc' => 'required',
            'image_gallery' => 'required',
        ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Blog = Blog::find($request->input('id'));
            $Blog->title = $request->input('title');
            $Blog->slug = str_replace(' ','-',$request->input('slug'));
            $Blog->short_detail = $request->input('short_detail');
            $Blog->detail = $request->input('detail');
            $Blog->image = $request->input('image_gallery');
            $Blog->image_big = $request->input('image_gallery_2');
            $Blog->meta_title = $request->input('meta_title');
            $Blog->meta_desc = $request->input('meta_desc');
            $Blog->admin_id = Auth::guard('admin')->user()->id;
            $Blog->seo = $request->input('seo');
            $Blog->status = $request->input('status');
            $Blog->type = 'news';
            $Blog->save();

            return success($request, 'بلاگ با ویرایش ثبت شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* حذف بلاگ */
    public function delete($id, Request $request)
    {
        Blog::find($id)->delete();
        return success($request,'بلاگ با موفقیت حذف شد');
    }

}
