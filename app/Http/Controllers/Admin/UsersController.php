<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UserRequest;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class UsersController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'users';
        $this->path = 'users';
    }

    public function getIndex()
    {
        return view('admin.users.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('search_value');

        $users = Admin::with('roles')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")))
            ->latest();

        return DataTables::of($users)
            ->addColumn('photo', fn ($user) => view('admin.users.parts.photo', ['user' => $user])->render())
            ->addColumn('role', fn ($user) => $user->roles->pluck('name')->implode(', '))
            ->addColumn('status', fn ($user) => view('admin.users.parts.status', ['user' => $user])->render())
            ->addColumn('actions', fn ($user) => view('admin.users.parts.actions', ['user' => $user])->render())
            ->rawColumns(['photo', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        $roles = Role::where('guard_name', 'admin')->get();

        return view('admin.users.add', self::$data + ['roles' => $roles, 'info' => null]);
    }

    public function postAdd(UserRequest $request)
    {
        $user = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'status' => $request->boolean('status', true),
        ]);

        if ($request->hasFile('photo')) {
            $user->update(['photo' => $request->file('photo')->store('admins', 'public')]);
        }

        $user->syncRoles([$request->role]);

        Cache::forget('spatie.permission.cache');

        return redirect()->route('users.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $user = Admin::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('users.view')->with('danger', __('app.not_found'));
        }

        $roles = Role::where('guard_name', 'admin')->get();

        return view('admin.users.add', self::$data + ['roles' => $roles, 'info' => $user]);
    }

    public function postEdit(UserRequest $request, $id)
    {
        try {
            $user = Admin::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('users.view')->with('danger', __('app.not_found'));
        }

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'status' => $request->boolean('status', true),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('admins', 'public');
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        Cache::forget('spatie.permission.cache');

        return redirect()->route('users.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $user = Admin::findOrFail(Crypt::decrypt($request->id));
            $user->update(['status' => ! $user->status]);

            Cache::forget('spatie.permission.cache');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $user = Admin::findOrFail(Crypt::decrypt($request->id));
            $user->delete();

            Cache::forget('spatie.permission.cache');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
