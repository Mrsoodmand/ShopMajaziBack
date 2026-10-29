<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pages;
use Illuminate\Support\Facades\Validator;

class PagesController extends Controller
{
    /* لیست صفحات */
    public function index(Request $request)
    {
        $list = Pages::orderby('id', 'DESC')->paginate(50);
        return view('admin-v2.page.index', compact('list'));
    }

    /* ویرایش صفحات */
    public function edit($id)
    {
        $edit = Pages::find($id);
        if (!is_null($edit)) {
            return view('admin-v2.page.create', compact('edit'));
        } else {
            return back();
        }
    }

    public function update(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'detail' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
            ], [
                'h1.required' => 'لطفا تگ H1 را وارد نمایید.',
                'detail.required' => 'لطفا توضیحات بلند را وارد نمایید.',
                'meta_title.required' => 'لطفا متا تایتل را وارد نمایید',
                'meta_desc.required' => 'لطفا متا دسکریپشن را وارد نمایید.',
            ]);

        if ($Validate->fails()) {
            return response()->json(['error' => $Validate->errors()->all()], 400);
        }
        try {

            $Seo = Add_Seo($request);
            $Faq = Add_Faq($request);

            $Page = Pages::find($request->input('id'));
            $Page->h1 = $request->input('h1');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->seo = json_encode($Seo);
            $Page->faq = json_encode($Faq);
            $Page->save();
            
            return response()->json(['success' => 'تنظمیات با موفقیت ثبت شد.']);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }
}
