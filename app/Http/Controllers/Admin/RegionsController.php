<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RegionRequest;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class RegionsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'regions';
        $this->path = 'regions';
    }

    public function getIndex()
    {
        return view('admin.regions.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('generalSearch') ?? $request->get('search_value') ?? $request->input('search.value');

        $regions = Region::withCount('applications')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderBy('id', 'desc');

        return DataTables::of($regions)
            ->addIndexColumn()
            ->addColumn('name', fn ($region) => view('admin.regions.parts.name', ['region' => $region])->render())
            ->addColumn('status', fn ($region) => view('admin.regions.parts.status', ['region' => $region])->render())
            ->addColumn('actions', fn ($region) => view('admin.regions.parts.actions', ['region' => $region])->render())
            ->rawColumns(['name', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.regions.add', self::$data + ['info' => null]);
    }

    public function postAdd(RegionRequest $request)
    {
        Region::create($request->safe()->all() + [
            'status' => $request->boolean('status', true)]);

        return redirect()->route('regions.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $region = Region::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('regions.view')->with('danger', __('app.not_found'));
        }

        return view('admin.regions.add', self::$data + ['info' => $region]);
    }

    public function postEdit(RegionRequest $request, $id)
    {
        try {
            $region = Region::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('regions.view')->with('danger', __('app.not_found'));
        }

        $region->update($request->safe()->all() + [
            'status' => $request->boolean('status', true)]);

        return redirect()->route('regions.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $region = Region::findOrFail(Crypt::decrypt($request->id));
            $region->update(['status' => ! $region->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $region = Region::findOrFail(Crypt::decrypt($request->id));

            if ($region->applications()->exists() || $region->students()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $region->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
