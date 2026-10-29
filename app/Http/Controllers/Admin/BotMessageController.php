<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HegzaMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\lib\Jdf;

class BotMessageController extends Controller
{
    public function index(Request $request)
    {
        $List = HegzaMessage::orderby('id', 'DESC');
        if ($request->has('q') && $request->input('q') != ' ' && $request->input('q') != null) {
            $List->where('title', 'LIKE', '%' . $request->input('q') . '%');
        }
        $list = $List->paginate(40);
        $jdf = new Jdf();
        return view('admin-v2.hegza.message.index', compact('list','jdf'));
    }

    public function create()
    {
        $title = 'ایجاد اطلاعیه';
        return view('admin-v2.hegza.message.create',compact('title'));
    }

    public function store(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'title' => 'required|max:220',
                'type' => 'required|max:220',
                'long_detail' => 'required',
                'cover' => 'required|max:220',
            ], [
                'title.required' => 'لطفا عنوان را وارد نمایید',
                'short_detail.required' => 'لطفا توضحیات کوتاه را وارد کنید',
                'long_detail.required' => 'لطفا توضیحات تکمیلی را وارد کنید',
                'cover.required' => 'لطفا عکس را انتخاب نمایید',
                'title.max' => 'حداکثر 220 کاراکتر برای عنوان',
                'short_detail.max' => 'حداکثر 220 کاراکتر برای توضحیات کوتاه',
                'cover.max' => 'حداکثر 220 کاراکتر برای کاور',
            ]);
        if ($Validate->fails()) {
            return response()->json(['error' => $Validate->errors()->all()], 400);
        }
        try {

            $Message = new HegzaMessage();
            $Message->title = $request->input('title');
            $Message->short_detail = $request->input('type');
            $Message->long_detail = $request->input('long_detail');
            $Message->cover = $request->input('cover');
            $Message->status = $request->input('status');
            $Message->creator = Auth::guard('admin')->user()->id;
            $Message->save();
            return response()->json(['success' => 'اطلاعیه با موفقیت ثبت شد'], 200);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }

    public function edit($id)
    {
        $edit = HegzaMessage::find($id);
        return view('admin-v2.hegza.message.create', compact('edit'));
    }

    public function update(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'title' => 'required|max:220',
                'type' => 'required|max:220',
                'long_detail' => 'required',
                'cover' => 'required|max:220',
            ], [
                'title.required' => 'لطفا عنوان را وارد نمایید',
                'short_detail.required' => 'لطفا توضحیات کوتاه را وارد کنید',
                'long_detail.required' => 'لطفا توضیحات تکمیلی را وارد کنید',
                'cover.required' => 'لطفا عکس را انتخاب نمایید',
                'title.max' => 'حداکثر 220 کاراکتر برای عنوان',
                'short_detail.max' => 'حداکثر 220 کاراکتر برای توضحیات کوتاه',
                'cover.max' => 'حداکثر 220 کاراکتر برای کاور',
            ]);
        if ($Validate->fails()) {
            return response()->json(['error' => $Validate->errors()->all()], 400);
        }
        try {

            $Message = HegzaMessage::find($request->input('id'));
            $Message->title = $request->input('title');
            $Message->short_detail = $request->input('type');
            $Message->long_detail = $request->input('long_detail');
            $Message->cover = $request->input('cover');
            $Message->status = $request->input('status');
            $Message->creator = Auth::guard('admin')->user()->id;
            $Message->save();

            return response()->json(['success' => 'اطلاعیه با موفقیت ویرایش شد'], 200);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }
}
