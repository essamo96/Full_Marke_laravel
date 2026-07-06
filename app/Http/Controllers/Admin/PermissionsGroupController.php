<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\PermissionsGroup;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Requests\Admin\PermissionsGroupRequest;

class PermissionsGroupController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'permissions_group';
        $this->path = 'permissions_group';
    }

    public function getIndex()
    {
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd()
    {
        $name = null;
        $roles = new PermissionsGroup();
        parent::$data['permissions'] = $roles->getAllPermissionGroupSearch($name);
        parent::$data['info'] = NULL;
        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name');
        $role = new PermissionsGroup();
        $info = $role->getPermissionGroupSearch($name);
        return Datatables::of($info)
            ->editColumn('status', function ($row) {
                $data['id'] = $row->id;
                $data['status'] = $row->status;
                return view('admin.' . $this->path . '.parts.status', $data)->render();
            })
            ->editColumn('parent_id', function ($row) {
                $record = PermissionsGroup::find($row->parent_id);
                if ($record) {
                    return '<div class="badge badge-warning fw-bold">' . $record->name_ar . '</div>';
                } else {
                    return '<div class="badge badge-danger fw-bold">لايوجد</div>';
                }
            })
            ->addColumn('actions', function ($row) {
                $data['active_menu'] = $this->path;
                $data['id'] = $row->id;
                return view('admin.' . $this->path . '.parts.actions', $data)->render();
            })
            ->rawColumns(['status', 'parent_id', 'actions'])
            ->addIndexColumn()
            ->make(true);
    }

    public function postAdd(PermissionsGroupRequest $request)
    {
        $validatedData = $request->validated();
        $validatedData['status'] = $request->has('status') ? 1 : 0;
        $validatedData['guard_name'] = 'web';

        $permissionsGroup = PermissionsGroup::create($validatedData);

        if ($permissionsGroup) {
            $slug = strtolower(str_replace(' ', '_', $permissionsGroup->name_en));
            
            if (empty($permissionsGroup->parent_id) || $permissionsGroup->parent_id == 0) {
                \App\Models\Permission::create([
                    'name' => 'admin.' . $slug . '.view',
                    'group_id' => $permissionsGroup->id,
                    'guard_name' => 'web'
                ]);
            } else {
                $actions = ['view', 'add', 'edit', 'delete', 'status', 'export', 'import', 'permissions'];
                foreach ($actions as $action) {
                    \App\Models\Permission::create([
                        'name' => 'admin.' . $slug . '.' . $action,
                        'group_id' => $permissionsGroup->id,
                        'guard_name' => 'web'
                    ]);
                }
            }

            Cache::forget('spatie.permission.cache');
            return redirect(route($this->path . '.view'))->with('success', __('app.insert_success'));
        } else {
            return redirect(route($this->path . '.add'))->withInput()->with('danger', __('app.execution_error'));
        }
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        $user = new PermissionsGroup();
        $info = $user->getPermissionsGroup($id);
        if ($info) {
            parent::$data['permissions'] = $user->getAllPermissionGroupSearch($name = null);
            parent::$data['info'] = $info;
            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(PermissionsGroupRequest $request, $id)
    {
        try {
            $decryptedId = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $permissionsGroup = PermissionsGroup::findOrFail($decryptedId);
        $validatedData = $request->validated();
        $validatedData['status'] = $request->has('status') ? 1 : 0;

        $update = $permissionsGroup->update($validatedData);

        if ($update) {
            Cache::forget('spatie.permission.cache');
            $request->session()->flash('success', __('app.update_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.edit', ['id' => $id]))->withInput();
        }
    }

    public function postStatus(Request $request)
    {
        $id = $request->get('id');
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
        $permissions_group = new PermissionsGroup();
        $info = $permissions_group->getPermissionsGroup($id);
        if ($info) {
            $status = $info->status;
            if ($status == 0) {
                $delete = $permissions_group->updateStatus($id, 1);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => __('app.activation_success'), 'type' => 'yes']);
                } else {
                    return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                }
            } else {
                $delete = $permissions_group->updateStatus($id, 0);
                if ($delete) {
                    return response()->json(['status' => 'success', 'message' => __('app.disable_success'), 'type' => 'no']);
                } else {
                    return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
                }
            }
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
    }

    public function postDelete(Request $request)
    {
        $id = $request->get('id');

        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
        $permissions_group = new PermissionsGroup();
        $info = $permissions_group->getPermissionsGroup($id);
        if ($info) {
            $delete = $permissions_group->deletePermissionsGroup($info);
            if ($delete) {
                return response()->json(['status' => 'success', 'message' => __('app.delete_success')]);
            } else {
                return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
            }
        } else {
            return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
        }
    }
}
