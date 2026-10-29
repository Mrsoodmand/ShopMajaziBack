<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $Categories = Category::where('type', 'permission')->pluck('name','id')->toArray();
        $list = Permission::orderby('id', 'DESC')->paginate(50);
        return view('admin-v2.permission.index', compact('list','Categories'));
    }

    public function create(Request $request)
    {
        $Categories = Category::where('type', 'permission')->get();
        $Data = view('admin-v2.permission.single-modal', compact('Categories'))->render();
        return response()->json(['data' => $Data], 200);
    }

    public function store(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'name' => 'required',
                'faName' => 'required',
                'category_id' => 'required',
                'table' => 'required',
            ], [

                'name.required' => 'لطفا نام را وارد نمایید.',
                'faName.required' => 'لطفا نام فارسی را وارد نمایید',
                'category_id.required' => 'لطفا دسته بندی را انتخاب نمایید',
                'table.required' => 'لطفا جدول را انتخاب نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        $newPermission = new Permission();
        $newPermission->name = $request->input('name');
        $newPermission->faName = $request->input('faName');
        $newPermission->category_id = $request->input('category_id');
        $newPermission->table = $request->input('table');
        $newPermission->guard_name = 'admin';
        $newPermission->admin_id = Auth::guard('admin')->user()->id;
        $newPermission->save();


        $role = Role::findById(1);
        $role->givePermissionTo($newPermission);

        return response()->json(['success' => 'دسترسی با موفقیت ایجاد شد.'], 200);
    }

    public function edit(Request $request)
    {
        $Categories = Category::where('type', 'permission')->get();
        $edit = Permission::find($request->input('id'));
        $Data = view('admin-v2.permission.single-modal', compact('Categories','edit'))->render();
        return response()->json(['data' => $Data], 200);
    }

    public function update(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'name' => 'required',
                'faName' => 'required',
                'category_id' => 'required',
                'table' => 'required',
            ], [

                'name.required' => 'لطفا نام را وارد نمایید.',
                'faName.required' => 'لطفا نام فارسی را وارد نمایید',
                'category_id.required' => 'لطفا دسته بندی را انتخاب نمایید',
                'table.required' => 'لطفا جدول را انتخاب نمایید.',
            ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        $newPermission = Permission::find($request->input('id'));
        $newPermission->name = $request->input('name');
        $newPermission->faName = $request->input('faName');
        $newPermission->category_id = $request->input('category_id');
        $newPermission->table = $request->input('table');
        $newPermission->guard_name = 'admin';
        $newPermission->admin_id = Auth::guard('admin')->user()->id;
        $newPermission->save();

        $role = Role::findById(1);
        $role->givePermissionTo($newPermission);

        return response()->json(['success' => 'دسترسی با موفقیت ایجاد شد.'], 200);
    }
}
