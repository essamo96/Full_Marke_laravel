<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RoleRequest;
use App\Models\PermissionsGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Spatie\Permission\Models\Role;

class RolesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'role';
        $this->path = 'role';
    }

    protected function saveRole(RoleRequest $request, $id = null)
    {
        $isUpdate = $id !== null;

        $role = $isUpdate ? Role::find($id) : new Role();

        if (!$role) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        if ($isUpdate && $role->id == 1) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found')); // Or specialized error for super admin
        }

        $validatedData = $request->validated();

        $role->name = $validatedData['name'] ?? $role->name;
        $role->status = $request->has('status') ? 1 : 0;
        $role->is_user = $request->has('is_user') ? 1 : 0;
        
        if (!$isUpdate) {
            $role->guard_name = 'admin';
        }

        if ($role->save()) {
            Cache::forget('spatie.permission.cache');

            $message = $isUpdate ? __('app.update_success') : __('app.insert_success');

            return redirect()
                ->route($this->path . '.view')
                ->with('success', $message);
        }

        return back()
            ->withInput()
            ->with('danger', __('app.execution_error'));
    }

    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd()
    {
        parent::$data['info'] = new Role();
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('generalSearch') ?? $request->get('search_value') ?? '';
        $status = $request->get('status');
        
        $info = Role::when($name, function ($q) use ($name) {
                return $q->where('name', 'like', "%{$name}%");
            })
            ->when($status !== null, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->latest()
            ->get();

        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        
        $datatable->editColumn('name', function ($row) {
            return '<a href="javascript:void(0)" class="btn btn-sm btn-light-primary">' . $row->name . '</a>';
        });

        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', ['role' => $row] + $data)->render();
        });

        $path = $this->path;
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', ['role' => $row] + $data)->render();
        });

        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postAdd(RoleRequest $request)
    {
        return $this->saveRole($request);
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        if ($id == 1) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $info = Role::find($id);

        if ($info) {
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(RoleRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        return $this->saveRole($request, $id);
    }

    public function getPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route($this->path . '.view')->with('danger', __('app.execution_error'));
        }

        $info = Role::find($id);
        if ($info) {
            parent::$data['btn_primary'] = 'btn-success';
            $permission_group = new PermissionsGroup();
            parent::$data['permission_group'] = $permission_group->getAllPermissionGroup();
            parent::$data['role_permissions'] = \Illuminate\Support\Facades\DB::table('role_has_permissions')
                ->where('role_id', $id)
                ->get()
                ->toArray();
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.permissions', parent::$data);
        } else {
            return redirect()->route($this->path . '.view')->with('danger', __('app.not_found'));
        }
    }

    public function postPermissions(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()->route($this->path . '.view')->with('danger', __('app.execution_error'));
        }

        $permissions = $request->get('permissions') ?? [];

        $role = Role::find($id);
        if ($role) {
            $role->syncPermissions($permissions);
        }

        Cache::forget('spatie.permission.cache');

        return redirect()->route($this->path . '.permissions', ['id' => Crypt::encrypt($id)])->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $info = Role::find($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $newStatus = $info->status == 0 ? 1 : 0;
        $info->status = $newStatus;
        $update = $info->save();

        if ($update) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1
                    ? __('app.activation_success') ?? 'نجاح، تم التفعيل بنجاح'
                    : __('app.disable_success') ?? 'نجاح، تم التعطيل بنجاح',
                'type' => $newStatus == 1 ? 'yes' : 'no'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }

    public function postDelete(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $info = Role::find($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        if ($info->id == 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cannot delete the main administrator role.'
            ]);
        }

        $delete = $info->delete();

        if ($delete) {
            Cache::forget('spatie.permission.cache');
            return response()->json([
                'status' => 'success',
                'message' => __('app.delete_success')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }
}
