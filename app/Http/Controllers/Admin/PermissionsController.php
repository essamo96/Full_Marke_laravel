<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PermissionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'permissions';
        $this->path = 'permissions';
    }

    public function getIndex()
    {
        $roles = Role::where('guard_name', 'admin')->with('permissions')->paginate(15);

        return view('admin.permissions.view', self::$data + ['roles' => $roles]);
    }

    public function getAdd()
    {
        $permissions = Permission::where('guard_name', 'admin')->get();

        return view('admin.permissions.add', self::$data + ['permissions' => $permissions, 'info' => null]);
    }

    public function postAdd(PermissionRequest $request)
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'admin']);
        $role->syncPermissions($request->permissions ?? []);

        Cache::forget('spatie.permission.cache');

        return redirect()->route('permissions.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $role = Role::where('guard_name', 'admin')->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('permissions.view')->with('danger', __('app.not_found'));
        }

        $permissions = Permission::where('guard_name', 'admin')->get();

        return view('admin.permissions.add', self::$data + ['permissions' => $permissions, 'info' => $role]);
    }

    public function postEdit(PermissionRequest $request, $id)
    {
        try {
            $role = Role::where('guard_name', 'admin')->findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('permissions.view')->with('danger', __('app.not_found'));
        }

        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions ?? []);

        Cache::forget('spatie.permission.cache');

        return redirect()->route('permissions.view')->with('success', __('app.update_success'));
    }

    public function postDelete(Request $request)
    {
        try {
            $role = Role::where('guard_name', 'admin')->findOrFail(Crypt::decrypt($request->id));
            $role->delete();

            Cache::forget('spatie.permission.cache');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
