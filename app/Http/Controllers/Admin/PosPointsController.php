<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PosPointRequest;
use App\Models\PosPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class PosPointsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'pos_points';
        $this->path = 'pos_points';
    }

    public function getIndex()
    {
        return view('admin.pos_points.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('name') ?? $request->get('generalSearch') ?? $request->get('search_value');

        $points = PosPoint::when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active))
            ->orderBy('sort_order');

        return DataTables::of($points)
            ->editColumn('image', fn ($point) => view('admin.pos_points.parts.image', ['point' => $point])->render())
            ->addColumn('name', fn ($point) => view('admin.pos_points.parts.name', ['point' => $point])->render())
            ->addColumn('phone', fn ($point) => $point->phone)
            ->addColumn('booklet_price', fn ($point) => $point->booklet_price !== null ? number_format($point->booklet_price, 2) : '-')
            ->addColumn('status', fn ($point) => view('admin.pos_points.parts.status', ['point' => $point])->render())
            ->addColumn('actions', fn ($point) => view('admin.pos_points.parts.actions', ['point' => $point])->render())
            ->rawColumns(['image', 'name', 'status', 'actions'])
            ->make(true);
    }

    public function getAdd()
    {
        return view('admin.pos_points.add', self::$data + ['info' => null]);
    }

    public function postAdd(PosPointRequest $request)
    {
        PosPoint::create($request->safe()->all() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('pos_points.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $point = PosPoint::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('pos_points.view')->with('danger', __('app.not_found'));
        }

        return view('admin.pos_points.add', self::$data + ['info' => $point]);
    }

    public function postEdit(PosPointRequest $request, $id)
    {
        try {
            $point = PosPoint::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('pos_points.view')->with('danger', __('app.not_found'));
        }

        $point->update($request->safe()->all() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('pos_points.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $point = PosPoint::findOrFail(Crypt::decrypt($request->id));
            $point->update(['is_active' => ! $point->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $point = PosPoint::findOrFail(Crypt::decrypt($request->id));
            $point->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
