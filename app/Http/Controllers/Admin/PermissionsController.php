<?php

namespace App\Http\Controllers\Admin;

use App\Models\Permission;
use App\Models\PermissionsGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Requests\Admin\PermissionRequest;

class PermissionsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'permissions';
        $this->path = 'permissions';
    }

    protected function savePermission(PermissionRequest $request, $id = null)
    {
        $isUpdate = $id !== null;

        $permission = $isUpdate ? Permission::find($id) : new Permission();

        if (!$permission) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        $validatedData = $request->validated();

        $permission->name = $validatedData['name'] ?? $permission->name;
        $permission->group_id = $validatedData['group_id'] ?? $permission->group_id;
        $permission->guard_name = $validatedData['guard_name'] ?? $permission->guard_name;

        if ($permission->save()) {
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
        parent::$data['permissions'] = PermissionsGroup::all();
        parent::$data['info'] = new Permission();
        parent::$data['guards'] = array_keys(config('auth.guards'));

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('generalSearch') ?? $request->get('search_value') ?? '';
        $group_id = $request->get('group_id');
        
        $info = Permission::with('permission_group')
            ->when($name, function ($q) use ($name) {
                return $q->where('name', 'like', "%{$name}%");
            })
            ->when($group_id, function ($q) use ($group_id) {
                return $q->where('group_id', $group_id);
            })
            ->latest()
            ->get();

        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        
        $datatable->editColumn('name', function ($row) {
            return '<a href="javascript:void(0)" class="btn btn-sm btn-light-primary">' . $row->name . '</a>';
        });

        $datatable->editColumn('group_id', function ($row) {
            $data['x'] = 2;
            $data['name'] = $row->permission_group ? $row->permission_group->{'name_' . app()->getLocale()} : '-';
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        });

        $datatable->editColumn('guard_name', function ($row) {
            $data['x'] = 1;
            $data['guard_name'] = $row->guard_name;
            return view('admin.' . $this->path . '.parts.general', $data)->render();
        });

        $path = $this->path;
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', ['permission' => $row] + $data)->render();
        });

        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postAdd(PermissionRequest $request)
    {
        return $this->savePermission($request);
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $info = Permission::find($id);

        if ($info) {
            parent::$data['permissions'] = PermissionsGroup::all();
            parent::$data['guards'] = array_keys(config('auth.guards'));
            parent::$data['info'] = $info;

            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(PermissionRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        return $this->savePermission($request, $id);
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

        $info = Permission::find($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
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
