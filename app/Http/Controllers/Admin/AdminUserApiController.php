<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Admin accounts for the React admin. Each admin has one role (admins.role mirrors its name, as the old admin kept it)
 * plus optional extra permissions of their own. Admins sign in with their email.
 * Nobody can delete themselves or take away the last super-admin.
 */
class AdminUserApiController extends Controller
{
    use AdminApi;

    public function index()
    {
        $extra = DB::table('model_has_permissions')->where('model_type', Admin::class)
            ->select('model_id', DB::raw('count(*) c'))->groupBy('model_id')->pluck('c', 'model_id');
        $me = Auth::guard('admin')->id();

        return response()->json([
            'admins' => Admin::with('roles:id,name,faName')->orderBy('id')->get()->map(function (Admin $a) use ($extra, $me) {
                $role = $a->roles->first();
                return [
                    'id' => $a->id,
                    'name' => (string) $a->full_name,
                    'email' => (string) $a->email,
                    'mobile' => (string) $a->mobile,
                    'role' => $role ? ['id' => $role->id, 'name' => $role->name, 'faName' => (string) ($role->faName ?: $role->name)] : null,
                    'extraPermissions' => (int) ($extra[$a->id] ?? 0),
                    'me' => $a->id === $me,
                    'createdAt' => $this->jdate($a->created_at, 'Y/m/d'),
                ];
            })->values(),
        ]);
    }

    public function show($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return $this->missing('این مدیر پیدا نشد.');
        }
        $role = $admin->roles()->first();
        return response()->json([
            'admin' => [
                'id' => $admin->id,
                'name' => (string) $admin->full_name,
                'email' => (string) $admin->email,
                'mobile' => (string) $admin->mobile,
                'roleId' => $role?->id,
                'permissionIds' => $admin->permissions()->pluck('id')->map(fn ($v) => (int) $v)->values(),
                'me' => $admin->id === Auth::guard('admin')->id(),
            ],
        ] + $this->choices());
    }

    /** Roles and permission groups, for the new-admin form. */
    public function options()
    {
        return response()->json($this->choices());
    }

    public function save(Request $request, $id = null)
    {
        $admin = $id ? Admin::find($id) : new Admin();
        if (!$admin) {
            return $this->missing('این مدیر پیدا نشد.');
        }
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'email' => ['required', 'email', 'max:190', Rule::unique('admins', 'email')->ignore($admin->id)],
            'mobile' => ['required', 'regex:/^09\d{9}$/'],
            'password' => [$admin->exists ? 'nullable' : 'required', 'string', Password::min(8)],
            'roleId' => ['required', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'admin')],
            'permissionIds' => 'array',
            'permissionIds.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'admin')],
        ], $this->messages() + [
            'email.unique' => 'مدیری با این ایمیل هست.',
            'email.email' => 'ایمیل درست نیست.',
            'mobile.regex' => 'شماره موبایل را مثل 09121234567 بنویسید.',
            'password.min' => 'رمز دست‌کم ۸ نویسه باشد.',
            'roleId.required' => 'نقش را انتخاب کنید.',
        ]);
        $role = Role::find($data['roleId']);
        if ($admin->exists && $admin->hasRole(RoleApiController::SUPER) && $role->name !== RoleApiController::SUPER) {
            if ($admin->id === Auth::guard('admin')->id()) {
                return response()->json(['message' => 'نقش', 'errors' => ['roleId' => ['نقش ادمین کل را از خودتان نمی‌توانید بگیرید.']]], 422);
            }
            if ($this->superCount() <= 1) {
                return response()->json(['message' => 'نقش', 'errors' => ['roleId' => ['این آخرین ادمین کل است.']]], 422);
            }
        }

        DB::transaction(function () use ($admin, $data, $role) {
            $admin->full_name = $data['name'];
            $admin->email = $data['email'];
            $admin->mobile = $data['mobile'];
            if (($data['password'] ?? '') !== '') {
                $admin->password = bcrypt($data['password']);
            }
            $admin->role = $role->name;
            $admin->save();
            $admin->syncRoles([$role]);
            // Extra permissions are only the ones the role doesn't already give.
            $fromRole = $role->permissions()->pluck('id')->all();
            $admin->syncPermissions(Permission::whereIn('id', array_diff($data['permissionIds'] ?? [], $fromRole))->get());
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => $id ? 'مدیر ذخیره شد.' : 'مدیر ساخته شد.', 'id' => $admin->id]);
    }

    public function delete($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return $this->missing('این مدیر پیدا نشد.');
        }
        if ($admin->id === Auth::guard('admin')->id()) {
            return $this->refuse('خودتان را نمی‌توانید حذف کنید.');
        }
        if ($admin->hasRole(RoleApiController::SUPER) && $this->superCount() <= 1) {
            return $this->refuse('این آخرین ادمین کل است.');
        }
        $admin->delete(); // spatie detaches its roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => 'مدیر حذف شد.']);
    }

    private function choices(): array
    {
        return [
            'roles' => Role::where('guard_name', 'admin')->with('permissions:id')->orderBy('id')->get()->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'faName' => (string) ($r->faName ?: $r->name),
                'all' => $r->name === RoleApiController::SUPER,
                'permissionIds' => $r->permissions->pluck('id')->map(fn ($v) => (int) $v)->values(),
            ])->values(),
            'groups' => RoleApiController::catalog(),
        ];
    }

    private function superCount(): int
    {
        return Admin::role(RoleApiController::SUPER, 'admin')->count();
    }
}
