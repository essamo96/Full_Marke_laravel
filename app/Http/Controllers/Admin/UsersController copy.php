<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use App\Http\Requests\Admin\UserRequest;
use App\Models\Company;

class UsersController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'users';
        $this->path = 'users';
    }

    protected function saveUser(UserRequest $request, $id = null)
    {
        $isUpdate = $id !== null;

        $user = $isUpdate ? User::find($id) : new User();

        if (!$user) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        $validatedData = $request->validated();

        $user->name = $validatedData['name'];
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->role_id = $validatedData['role_id'];
        $user->company_id = $validatedData['company_id'];
        $user->status = $request->has('status') ? 1 : 0;
        $user->created_by = Auth::guard('admin')->id();

        if (isset($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        if ($user->save()) {
            $role = Role::find($validatedData['role_id']);
            if ($role) {
                $user->syncRoles([$role->name]);
            }

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
         parent::$data['companies'] = Company::where('status', 1)->get();

        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd()
    {
        $roles = new Role();
        parent::$data['roles'] = $roles->getAllRolesActive();
        parent::$data['info'] = new User();
        parent::$data['users'] = User::where('status', 1)->get();
        parent::$data['companies'] = Company::where('status', 1)->get();

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('name') ?? '';
        $companies = $request->get('companies') ?? '';
        $emp_id = Auth::guard('admin')->user()->role->is_user != 1 ? 0 : Auth::guard('admin')->user()->id;
        $role = new User();
        $info = $role->getSearchUsers($name, $companies, $emp_id);
        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', $data)->render();
        });
        $datatable->editColumn('updated_at', function ($row) {
            return '<div class="badge badge-info fw-bold">' . $row->updated_at->diffForHumans() . '</div>';
        });
        $datatable->editColumn('created_by', function ($row) {
            $x = $row->admin ? $row->creator->username : __('app.system');
            return '<div class="badge badge-warning fw-bold">' . $x . '</div>';
        });
        $datatable->editColumn('role_id', function ($row) {
            $x = $row->role ? $row->role->name : '--';
            $countpermissions = $row->role ? $row->role->permissions->count() : 0;
            return '<div class="badge badge-warning fw-bold">' . $x . ' (' . $countpermissions . ')</div>';
        });
        $datatable->addColumn('company_id', function ($row) {
            $data['active_menu'] = $this->path;
            $data['id'] = $row->id;
            $data['x'] = 3;
            $locale = app()->getLocale();
            $column = 'name_' . $locale;
            $data['name'] = $row->company ? $row->company->$column : '';
            return '<div class="badge badge-warning fw-bold">' . $data['name'] . '</div>';
        });
        $path = $this->path;
        $datatable->editColumn('name', function ($row) {
            $data['x'] = 3;
            $data['name'] = $row->name;
            return '<a href="javascript:void(0)" class="btn btn-light-primary mt-0 fs-5 btn-xs">' . $data['name'] . '</a>';
        });
        $datatable->editColumn('username', function ($row) {
            $data['x'] = 3;
            $data['name'] = $row->username ?? '---';
            return '<a href="javascript:void(0)" class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary fs-5 btn-xs">' . $data['name'] . '</a>';
        });
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', $data)->render();
        });
        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postAdd(UserRequest $request)
    {
        return $this->saveUser($request);
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $user = new User();
        $roles = new Role();
        $info = $user->getUser($id);

        if ($info) {
            parent::$data['users'] = User::where('status', 1)->get();
            parent::$data['roles'] = $roles->getAllRolesActive();
            parent::$data['info'] = $info;
            parent::$data['companies'] = Company::where('status', 1)->get();


            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(UserRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        return $this->saveUser($request, $id);
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

        $users = new User();
        $info = $users->getUser($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $newStatus = $info->status == 0 ? 1 : 0;
        $update = $users->updateStatus($id, $newStatus);

        if ($update) {
            return response()->json([
                'status' => 'success',
                'message' => $newStatus == 1
                    ? __('app.activation_success')
                    : __('app.disable_success'),
                'type' => $newStatus == 1 ? 'yes' : 'no'
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }

    public function getPassword(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $user = new User();
        $info = $user->getUser($id);

        if (!$info) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        parent::$data['info'] = $info;
        return view('admin.' . $this->path . '.password', parent::$data);
    }

    public function postPassword(Request $request, $id)
    {
        try {
            $encrypted_id = $id;
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        $user = new User();
        $info = $user->getUser($id);

        if (!$info) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
        $validator = Validator::make($request->all(), [
            'password' => 'required|between:6,16|confirmed',
            'password_confirmation' => 'required|between:6,16'
        ]);
        if ($validator->fails()) {
            $request->session()->flash('danger', $validator->messages());
            return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
        }

        $update = $user->updatePassword($id, Hash::make($request->get('password')));

        if ($update) {
            $request->session()->flash('success', __('app.password_success'));
            return redirect(route($this->path . '.view'));
        } else {
            $request->session()->flash('danger', __('app.execution_error'));
            return redirect(route($this->path . '.password', ['id' => $encrypted_id]))->withInput();
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

        $users = new User();
        $info = $users->getUser($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $delete = $info->deleteUser($info);

        if ($delete) {
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
