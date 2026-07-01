<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SidebarRequest;
use App\Models\PermissionsGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class SidebarController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'sidebar';
        $this->path = 'sidebar';
    }

    public function getIndex()
    {
        return view('admin.sidebar.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('search_value');

        $groups = PermissionsGroup::with('parent')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$search}%")
                ->orWhere('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")))
            ->orderBy('parent_id')
            ->orderBy('sort');

        return DataTables::of($groups)
            ->addColumn('name', fn ($group) => e($group->name_en ?? $group->name))
            ->addColumn('parent', fn ($group) => e($group->parent->name_en ?? $group->parent->name ?? '—'))
            ->addColumn('status', fn ($group) => view('admin.sidebar.parts.status', ['group' => $group])->render())
            ->addColumn('actions', fn ($group) => view('admin.sidebar.parts.actions', ['group' => $group])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        $parents = PermissionsGroup::where('parent_id', 0)->get();

        return view('admin.sidebar.add', self::$data + ['parents' => $parents, 'info' => null]);
    }

    public function postAdd(SidebarRequest $request)
    {
        $group = PermissionsGroup::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort' => $request->sort ?? 0,
            'parent_id' => $request->parent_id ?? 0,
            'status' => $request->boolean('status', true),
        ]);

        if ($group->parent_id != 0) {
            $group->generateCrudPermissions();
        }

        Cache::forget('spatie.permission.cache');

        return redirect()->route('sidebar.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $group = PermissionsGroup::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('sidebar.view')->with('danger', __('app.not_found'));
        }

        $parents = PermissionsGroup::where('parent_id', 0)->where('id', '!=', $group->id)->get();

        return view('admin.sidebar.add', self::$data + ['parents' => $parents, 'info' => $group]);
    }

    public function postEdit(SidebarRequest $request, $id)
    {
        try {
            $group = PermissionsGroup::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('sidebar.view')->with('danger', __('app.not_found'));
        }

        $group->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'name_en' => $request->name_en,
            'icon' => $request->icon,
            'color' => $request->color,
            'sort' => $request->sort ?? 0,
            'parent_id' => $request->parent_id ?? 0,
            'status' => $request->boolean('status', true),
        ]);

        if ($group->parent_id != 0) {
            $group->generateCrudPermissions();
        }

        Cache::forget('spatie.permission.cache');

        return redirect()->route('sidebar.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $group = PermissionsGroup::findOrFail(Crypt::decrypt($request->id));
            $group->update(['status' => ! $group->status]);

            Cache::forget('spatie.permission.cache');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $group = PermissionsGroup::findOrFail(Crypt::decrypt($request->id));
            $group->delete();

            Cache::forget('spatie.permission.cache');

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
