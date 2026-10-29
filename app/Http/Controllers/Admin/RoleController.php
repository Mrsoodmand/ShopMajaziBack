<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Plan;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /* لیست نقش */
    public function index(Request $request)
    {
        $list = Role::orderby('id', 'DESC')->paginate(50);
        return view('admin-v2.role.index', compact('list'));
    }

    /* ایجاد رول */
    public function create(Request $request)
    {
        $Categories = Category::where('type', 'permission')->get();
        $Permissions = Permission::all();
        return view('admin-v2.role.create', compact('Categories', 'Permissions'));
    }

    /* ثبت رول */
    public function store(Request $request)
    {
        $newRole = new Role();
        $newRole->name = $request->input('name');
        $newRole->faName = $request->input('faName');
        $newRole->admin_id = Auth::guard('admin')->user()->id;
        $newRole->guard_name = 'admin';
        $newRole->save();

        $newRole->syncPermissions($request->input('permissions'));
        return response()->json(['success' => 'دسترسی با موفقیت ثبت شد'], 200);
    }

    /* ویرایش رول */
    public function edit($id, Request $request)
    {
        $Categories = Category::where('type', 'permission')->get();
        $Permissions = Permission::all();
        $edit = Role::findById($id);
        return view('admin-v2.role.create', compact('Categories', 'Permissions', 'edit'));
    }

    /* بروز رسانی رول */
    public function update(Request $request)
    {
        $newRole = Role::find($request->input('id'));
        $newRole->name = $request->input('name');
        $newRole->faName = $request->input('faName');
        $newRole->save();

        $newRole->syncPermissions($request->input('permissions'));
        return response()->json(['success' => 'دسترسی با موفقیت ثبت شد'], 200);
    }
}
