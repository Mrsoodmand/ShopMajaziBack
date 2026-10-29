<?php

namespace App\Http\Controllers\Admin;

use App\Admin;
use App\Http\Controllers\Admin\Concerns\AdminApi;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Roles (spatie, guard "admin") for the React admin. A role is a named set of permissions; permissions are grouped by
 * categories of type "permission". super-admin passes every check, so its name and permissions are fixed here.
 * admins.role keeps the role's name (the old admin wrote it), so renaming a role renames it there too.
 */
class RoleApiController extends Controller
{
    use AdminApi;

    const SUPER = 'super-admin';

    public function index()
    {
        $roles = Role::where('guard_name', 'admin')->withCount('permissions')->orderBy('id')->get();
        $admins = DB::table('model_has_roles')->where('model_type', Admin::class)
            ->select('role_id', DB::raw('count(*) c'))->groupBy('role_id')->pluck('c', 'role_id');

        return response()->json([
            'permissionTotal' => Permission::where('guard_name', 'admin')->count(),
            'roles' => $roles->map(fn (Role $r) => [
                'id' => $r->id,
                'name' => $r->name,
                'faName' => (string) $r->faName,
                'permissions' => $r->name === self::SUPER ? null : (int) $r->permissions_count,
                'admins' => (int) ($admins[$r->id] ?? 0),
                'system' => $r->name === self::SUPER,
            ])->values(),
        ]);
    }

    /** Every permission, grouped as the old admin grouped them. */
    public function permissions()
    {
        return response()->json(['groups' => self::catalog()]);
    }

    public function show($id)
    {
        $role = Role::where('guard_name', 'admin')->find($id);
        if (!$role) {
            return $this->missing('این نقش پیدا نشد.');
        }
        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'faName' => (string) $role->faName,
                'system' => $role->name === self::SUPER,
                'permissionIds' => $role->permissions()->pluck('id')->map(fn ($v) => (int) $v)->values(),
                'admins' => Admin::role($role->name, 'admin')->orderBy('id')->get(['id', 'full_name', 'email'])
                    ->map(fn ($a) => ['id' => $a->id, 'name' => (string) $a->full_name, 'email' => (string) $a->email])->values(),
            ],
            'groups' => self::catalog(),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $role = $id ? Role::where('guard_name', 'admin')->find($id) : new Role();
        if (!$role) {
            return $this->missing('این نقش پیدا نشد.');
        }
        $system = $role->exists && $role->name === self::SUPER;
        $data = $request->validate([
            'faName' => 'required|string|max:100',
            'name' => $system ? 'nullable' : ['required', 'string', 'max:60', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('roles', 'name')->where('guard_name', 'admin')->ignore($role->id)],
            'permissionIds' => 'array',
            'permissionIds.*' => ['integer', Rule::exists('permissions', 'id')->where('guard_name', 'admin')],
        ], $this->messages() + [
            'name.regex' => 'فقط حروف کوچک انگلیسی، عدد و خط تیره (مثل support).',
            'name.unique' => 'نقشی با این نام انگلیسی هست.',
        ]);

        $oldName = $role->name;
        DB::transaction(function () use ($role, $data, $system, $oldName) {
            $role->faName = $data['faName'];
            if (!$system) {
                $role->name = $data['name'];
            }
            if (!$role->exists) {
                $role->guard_name = 'admin';
                $role->admin_id = Auth::guard('admin')->id();
            }
            $role->save();
            if (!$system) {
                $role->syncPermissions(Permission::whereIn('id', $data['permissionIds'] ?? [])->get());
            }
            if ($oldName && $oldName !== $role->name) {
                Admin::where('role', $oldName)->update(['role' => $role->name]);
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => $id ? 'نقش ذخیره شد.' : 'نقش ساخته شد.', 'id' => $role->id]);
    }

    public function delete($id)
    {
        $role = Role::where('guard_name', 'admin')->find($id);
        if (!$role) {
            return $this->missing('این نقش پیدا نشد.');
        }
        if ($role->name === self::SUPER) {
            return $this->refuse('نقش ادمین کل حذف نمی‌شود.');
        }
        $count = Admin::role($role->name, 'admin')->count();
        if ($count) {
            return $this->refuse("$count مدیر این نقش را دارند. اول نقش آن‌ها را عوض کنید.");
        }
        $role->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return response()->json(['success' => 'نقش حذف شد.']);
    }

    /** [{id, name, permissions: [{id, name, faName}]}], with permissions outside any group last. */
    public static function catalog(): array
    {
        $permissions = Permission::where('guard_name', 'admin')->orderBy('id')->get(['id', 'name', 'faName', 'category_id'])->groupBy('category_id');
        $groups = Category::where('type', 'permission')->orderBy('id')->get(['id', 'name'])
            ->map(fn ($c) => ['id' => $c->id, 'name' => (string) $c->name, 'permissions' => $permissions->pull($c->id) ?? collect()])
            ->filter(fn ($g) => $g['permissions']->isNotEmpty())->values();
        $rest = $permissions->flatten(1);
        if ($rest->isNotEmpty()) {
            $groups->push(['id' => 0, 'name' => 'سایر', 'permissions' => $rest]);
        }
        return $groups->map(fn ($g) => [
            'id' => $g['id'],
            'name' => $g['name'],
            'permissions' => collect($g['permissions'])->map(fn ($p) => ['id' => (int) $p->id, 'name' => $p->name, 'faName' => (string) ($p->faName ?: $p->name)])->values()->all(),
        ])->values()->all();
    }
}
