<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\lib\PhpEncrypt;
use App\Models\AppleId;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Pages;
use App\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /* لیست مدیران */
    public function index(Request $request)
    {
        $list = Admin::orderby('id', 'DESC')->paginate(50);
        return view('admin-v2.admin.index', compact('list'));
    }

    /* افزودن مدیر */
    public function create(Request $request)
    {
        $Role = Role::orderby('id', 'DESC')->get();
        $Categories = Category::where('type', 'permission')->get();
        $Permissions = Permission::all();
        return view('admin-v2.admin.create', compact('Role', 'Permissions', 'Categories'));
    }

    /* ثبت مدیر */
    public function store(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'full_name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
            'password' => 'required',
        ], [
            'full_name.required' => 'لطفا نام و نام خانوادگی را وارد نمایید',
            'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
            'email.required' => 'لطفا ایمیل را وارد نمایید',
            'password.required' => 'لطفا پسورد را وارد نمایید',
        ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            $Role = Role::find($request->input('role'));

            $admin = new Admin();
            $admin->full_name = $request->input('full_name');
            $admin->mobile = $request->input('mobile');
            $admin->email = $request->input('email');
            $admin->password = bcrypt($request->input('password'));
            $admin->role = $Role->name;
            $admin->save();


            $admin->syncRoles($Role->name);

            if ($request->has('permissions')) {
                $Permissions = Permission::wherein('id', $request->input('permissions'))->pluck('name')->toarray();
                $admin->givePermissionTo([$Permissions]);
            }
            return success($request, 'مدیر با موفقیت ثبت شد.');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ویرایش مدیر */
    public function edit($id, Request $request)
    {
        $edit = Admin::find($id);
        $Role = Role::orderby('id', 'DESC')->get();
        $Categories = Category::where('type', 'permission')->get();
        $Permissions = Permission::all();
        return view('admin-v2.admin.create', compact('edit', 'Role', 'Permissions', 'Categories'));
    }

    /* بروز رسانی مدیر */
    public function update(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'full_name' => 'required',
            'mobile' => 'required',
            'email' => 'required',
        ], [
            'full_name.required' => 'لطفا نام و نام خانوادگی را وارد نمایید',
            'mobile.required' => 'لطفا شماره موبایل را وارد نمایید',
            'email.required' => 'لطفا ایمیل را وارد نمایید',
            'password.required' => 'لطفا پسورد را وارد نمایید',
        ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            $Role = Role::find($request->input('role'));

            $admin = Admin::find($request->input('id'));
            $admin->full_name = $request->input('full_name');
            $admin->mobile = $request->input('mobile');
            $admin->email = $request->input('email');
            if (!is_null($request->input('password'))) {
                $admin->password = bcrypt($request->input('password'));
            }
            $admin->role = $Role->name;
            $admin->save();


            $admin->syncRoles($Role->name);

            if ($request->has('permissions')) {
                $Permissions = Permission::wherein('id', $request->input('permissions'))->pluck('name')->toarray();
                $admin->syncPermissions([$Permissions]);
            }
            return success($request, 'مدیر با موفقیت ویرایش شد.');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* تغییر رول */
    public function changeRole(Request $request)
    {
        $Roles = Role::find($request->input('id'));
        if (!is_null($Roles)) {
            $Permissions = Permission::orderby('id', 'DESC')->get();
            $Categories = Category::where('type', 'permission')->get();
            $data = view('admin-v2.comp.roleAndPermissions', compact('Categories', 'Permissions', 'Roles'))->render();
            return response()->json(['success' => 'as', 'data' => $data], 200);
        } else {
            return response()->json(['error' => ['سطح دسترسی مورد نظر یافت نشد.']], 400);
        }
    }

}
