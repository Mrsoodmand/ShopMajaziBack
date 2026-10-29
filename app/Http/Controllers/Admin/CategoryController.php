<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /* لیست دسته بندی */
    public function index($type, Request $request)
    {

        $list = Category::where('type', $type);
        if ($request->has('search') && $request->input('search') != null) {
            $list->where('name', 'Like', '%' . $request->input('search') . '%');
        }
        $list = $list->paginate(50);
        return view('admin-v2.category.index', compact('list','type'));
    }

    /* ایجاد دسته بندی */
    public function create($type)
    {
        return view('admin-v2.category.create', compact('type'));
    }

    /* ثبت دسته بندی */
    public function store(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
//                'meta_title' => 'required|string',
                'type' => 'required|string',
//                'meta_desc' => 'required',
                'detail' => 'required|string',
            ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Category = new Category();
            $Category->name = $request->input('faName');
            $Category->enName = $request->input('enName');
            $Category->slug = $request->input('slug');
            $Category->type = $request->input('type');
            $Category->short_detail = $request->input('short_detail');
            $Category->long_detail = $request->input('detail');
            $Category->meta_desc = $request->input('meta_desc');
            $Category->meta_title = $request->input('meta_title');
            $Category->image = $request->input('image_gallery');
            $Category->save();
            return success($request, 'دسته بندی با موفقیت ثبت شد.');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }

    }

    /* ویرایش دسته بندی */
    public function edit($id)
    {
        $edit = Category::find($id);
        return view('admin-v2.category.create', compact('edit'));
    }

    /* به روز رسانی دسته بندی */
    public function update(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
//                'meta_title' => 'required|string',
                'type' => 'required|string',
//                'meta_desc' => 'required',
                'detail' => 'required|string',
            ]);

        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Category = Category::find($request->input('id'));
            $Category->name = $request->input('faName');
            $Category->enName = $request->input('enName');
            $Category->slug = $request->input('slug');
            $Category->type = $request->input('type');
            $Category->short_detail = $request->input('short_detail');
            $Category->long_detail = $request->input('detail');
            $Category->meta_desc = $request->input('meta_desc');
            $Category->meta_title = $request->input('meta_title');
            $Category->image = $request->input('image_gallery');
            $Category->save();
            return success($request, 'دسته بندی با موفقیت ثبت شد.');
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* حذف دسته بندی */
    public function delete(Request $request)
    {
        $Category = $request->input('id');
        $check = Category::find($Category);
        if (!is_null($check)){
            $check = $check->delete();
            return success($request, 'دسته بندی با موفقیت حذف شد.');

        }else{
            return exception_error($request, 'دسته بندی مورد نظر یافت نشد.');

        }
    }

}
