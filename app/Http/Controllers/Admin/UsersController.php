<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;
use Illuminate\Contracts\Encryption\DecryptException;
use Spatie\Permission\Models\Role;

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

        $user = $isUpdate ? Admin::find($id) : new Admin();

        if (!$user) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        $validatedData = $request->validated();

        $user->name = $validatedData['name'] ?? $user->name;
        $user->email = $validatedData['email'] ?? $user->email;
        $user->status = $request->has('status') ? 1 : 0;

        if (isset($validatedData['password']) && !empty($validatedData['password'])) {
            $user->password = bcrypt($validatedData['password']);
        }

        if ($request->hasFile('photo')) {
            $user->photo = $request->file('photo')->store('admins', 'public');
        }

        if (!$isUpdate && Auth::guard('admin')->check()) {
            $user->created_by = Auth::guard('admin')->id();
        }

        if ($user->save()) {
            if ($request->has('role')) {
                $role = Role::where('guard_name', 'admin')->find($request->get('role'));
                if ($role) {
                    $user->syncRoles([$role->name]);
                    $user->role = $role->name;
                    $user->save();
                }
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
        return view('admin.' . $this->path . '.view', parent::$data);
    }

    public function getAdd()
    {
        parent::$data['roles'] = Role::where('guard_name', 'admin')->get();
        parent::$data['info'] = new Admin();
        parent::$data['users'] = Admin::where('status', 1)->get();

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $filters = [
            'search_value' => $request->get('generalSearch') ?? $request->get('search_value'),
            'status' => $request->get('status'),
            'role' => $request->get('role'),
        ];
        
        $query = Admin::with(['roles', 'creator'])->latest();
        $query = Admin::applyFilters($query, $filters);

        $datatable = Datatables::of($query);
        
        $datatable->addColumn('photo', function ($row) {
            return view('admin.' . $this->path . '.parts.photo', ['user' => $row])->render();
        });

        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            // The view might expect $user instead of $id depending on parts.status
            return view('admin.' . $this->path . '.parts.status', ['user' => $row] + $data)->render();
        });

        $datatable->addColumn('role', function ($row) {
            $x = $row->roles->pluck('name')->implode(', ');
            $countpermissions = $row->roles->first() ? $row->roles->first()->permissions->count() : 0;
            return '<div class="badge badge-warning fw-bold">' . $x . ' (' . $countpermissions . ')</div>';
        });

        $datatable->addColumn('created_by', function ($row) {
            return $row->creator ? '<span class="badge badge-light-info fw-bold">' . $row->creator->name . '</span>' : '<span class="badge badge-light-secondary fw-bold">-</span>';
        });

        $path = $this->path;
        $datatable->editColumn('name', function ($row) {
            return '<a href="javascript:void(0)" class="btn btn-sm btn-light-primary">' . $row->name . '</a>';
        });

        $datatable->editColumn('email', function ($row) {
            return '<a href="javascript:void(0)" class="btn btn-sm btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary">' . $row->email . '</a>';
        });

        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', ['user' => $row] + $data)->render();
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

        $info = Admin::find($id);

        if ($info) {
            parent::$data['users'] = Admin::where('status', 1)->get();
            parent::$data['roles'] = Role::where('guard_name', 'admin')->get();
            parent::$data['info'] = $info;

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

        $info = Admin::find($id);

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

        $info = Admin::find($id);

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

    public function postPassword(Request $request)
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

        if ($id != Auth::guard('admin')->id()) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }

        $validator = Validator::make($request->all(), [
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first()
            ]);
        }

        $info = Admin::find($id);

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => __('app.not_found')
            ]);
        }

        $info->password = bcrypt($request->password);

        if ($info->save()) {
            return response()->json([
                'status' => 'success',
                'message' => __('app.update_success')
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => __('app.execution_error')
            ]);
        }
    }
}
