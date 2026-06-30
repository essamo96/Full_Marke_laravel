<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PermissionRequest;
use App\Models\PermissionsGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
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

    public function getIndex(Request $request)
    {
        $search = $request->get('search');

        $roles = Role::where('guard_name', 'admin')
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->with('permissions')
            ->paginate(15)
            ->withQueryString();

        return view('admin.permissions.view', self::$data + ['roles' => $roles]);
    }

    public function getAdd()
    {
        return view('admin.permissions.add', self::$data + ['permissionGroups' => $this->getGroupedPermissions(), 'info' => null]);
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

        return view('admin.permissions.add', self::$data + ['permissionGroups' => $this->getGroupedPermissions(), 'info' => $role]);
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

    /**
     * Permissions grouped by their owning sidebar module (permissions_groups),
     * ordered view→add→edit→status→delete, for the grouped checkbox UI.
     */
    private function getGroupedPermissions()
    {
        $actionOrder = "FIELD(SUBSTRING_INDEX(name, '.', -1),'view','add','edit','status','delete')";

        return PermissionsGroup::where('status', 1)
            ->where('parent_id', 0)
            ->orderByRaw('COALESCE(sort, 9999) ASC')
            ->with([
                'permissions' => fn ($q) => $q->where('guard_name', 'admin')->orderByRaw($actionOrder),
                'children' => fn ($q) => $q->where('status', 1)->orderBy('sort')
                    ->with(['permissions' => fn ($pq) => $pq->where('guard_name', 'admin')->orderByRaw($actionOrder)]),
            ])
            ->get();
    }
}
