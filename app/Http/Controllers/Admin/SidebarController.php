<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SidebarRequest;
use App\Models\PermissionsGroup;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class SidebarController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'permissions_group';
        $this->path = 'permissions_group';
    }

    protected function saveSidebar(SidebarRequest $request, $id = null)
    {
        $isUpdate = $id !== null;

        $group = $isUpdate ? PermissionsGroup::find($id) : new PermissionsGroup();

        if (!$group) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        $validatedData = $request->validated();

        $group->name = $validatedData['name'] ?? $group->name;
        $group->name_ar = $validatedData['name_ar'] ?? $group->name_ar;
        $group->name_en = $validatedData['name_en'] ?? $group->name_en;
        $group->icon = $validatedData['icon'] ?? $group->icon;
        $group->color = $validatedData['color'] ?? $group->color;
        $group->bg_color = $validatedData['bg_color'] ?? $group->bg_color;
        $group->sort = $validatedData['sort'] ?? $group->sort ?? 0;
        $group->parent_id = $validatedData['parent_id'] ?? $group->parent_id ?? 0;
        $group->status = $request->has('status') ? 1 : 0;

        if ($group->save()) {
            if ($group->parent_id != 0) {
                // Generate all 7 permissions for child
                $group->generateCrudPermissions(['view', 'add', 'edit', 'delete', 'status', 'import', 'export']);
            } else {
                // Generate only 'view' permission for parent
                $group->generateCrudPermissions(['view']);
            }

            // Sync all new permissions to Super Admin role
            $superAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'admin']);
            $superAdmin->syncPermissions(\Spatie\Permission\Models\Permission::where('guard_name', 'admin')->get());

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

    private function getKiIcons()
    {
        $path = storage_path('app/ki_icons.json');
        if (file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return [];
    }

    public function getAdd()
    {
        parent::$data['permissions'] = PermissionsGroup::where('parent_id', 0)->get();
        parent::$data['info'] = new PermissionsGroup();
        parent::$data['ki_icons'] = $this->getKiIcons();

        return view('admin.' . $this->path . '.add', parent::$data);
    }

    public function getList(Request $request)
    {
        $name = $request->get('search_value') ?? $request->get('name') ?? '';
        
        $info = PermissionsGroup::with('parent')
            ->when($name, function ($q) use ($name) {
                return $q->where(function ($q2) use ($name) {
                    $q2->where('name', 'like', "%{$name}%")
                       ->orWhere('name_ar', 'like', "%{$name}%")
                       ->orWhere('name_en', 'like', "%{$name}%");
                });
            })
            ->orderBy('parent_id')
            ->orderBy('sort')
            ->get();

        $datatable = Datatables::of($info)->setTotalRecords(sizeof($info));
        
        $datatable->addColumn('name', function ($row) {
            return e($row->name_en ?? $row->name);
        });

        $datatable->addColumn('parent', function ($row) {
            return e($row->parent->name_en ?? $row->parent->name ?? '—');
        });

        $datatable->editColumn('status', function ($row) {
            $data['id'] = $row->id;
            $data['status'] = $row->status;
            return view('admin.' . $this->path . '.parts.status', ['group' => $row] + $data)->render();
        });

        $path = $this->path;
        $datatable->addColumn('actions', function ($row) use ($path) {
            $data['active_menu'] = $path;
            $data['id'] = $row->id;
            return view('admin.' . $this->path . '.parts.actions', ['group' => $row] + $data)->render();
        });

        $datatable->escapeColumns(['*']);
        return $datatable->addIndexColumn()->make(true);
    }

    public function postAdd(SidebarRequest $request)
    {
        return $this->saveSidebar($request);
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }

        $info = PermissionsGroup::find($id);

        if ($info) {
            parent::$data['permissions'] = PermissionsGroup::where('parent_id', 0)->where('id', '!=', $info->id)->get();
            parent::$data['ki_icons'] = $this->getKiIcons();
            parent::$data['info'] = $info;

            return view('admin.' . $this->path . '.add', parent::$data);
        } else {
            $request->session()->flash('danger', __('app.not_found'));
            return redirect(route($this->path . '.view'));
        }
    }

    public function postEdit(SidebarRequest $request, $id)
    {
        try {
            $id = Crypt::decrypt($id);
        } catch (DecryptException $e) {
            return redirect()
                ->route($this->path . '.view')
                ->with('danger', __('app.not_found'));
        }

        return $this->saveSidebar($request, $id);
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

        $info = PermissionsGroup::find($id);

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

        $info = PermissionsGroup::find($id);

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

    public function postReorder(Request $request)
    {
        $items = $request->input('items', []);
        foreach ($items as $item) {
            if (isset($item['id']) && isset($item['sort'])) {
                PermissionsGroup::where('id', $item['id'])->update(['sort' => $item['sort']]);
            }
        }
        Cache::forget('spatie.permission.cache');
        return response()->json(['status' => 'success', 'message' => __('app.update_success') ?? 'نجاح']);
    }

    public function postUpdateColor(Request $request)
    {
        try {
            $id = Crypt::decrypt($request->input('id'));
        } catch (DecryptException $e) {
            return response()->json(['status' => 'error', 'message' => __('app.execution_error')]);
        }
        
        $group = PermissionsGroup::find($id);
        if ($group) {
            if ($request->has('color')) $group->color = $request->input('color');
            if ($request->has('bg_color')) $group->bg_color = $request->input('bg_color');
            $group->save();
            Cache::forget('spatie.permission.cache');
            return response()->json(['status' => 'success', 'message' => __('app.update_success') ?? 'نجاح']);
        }
        return response()->json(['status' => 'error', 'message' => __('app.not_found')]);
    }

    public function postBulkColor(Request $request)
    {
        $color = $request->input('color');
        $scope = $request->input('scope');
        
        if ($scope === 'parents') {
            $updated = PermissionsGroup::where('parent_id', 0)->update(['color' => $color]);
        } else {
            $updated = PermissionsGroup::query()->update(['color' => $color]);
        }
        
        Cache::forget('spatie.permission.cache');
        return response()->json([
            'status' => 'success', 
            'message' => __('app.update_success') ?? 'نجاح', 
            'updated' => $updated
        ]);
    }
}
