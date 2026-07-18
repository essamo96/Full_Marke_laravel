<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\BranchRequest;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class BranchesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'branches';
        $this->path = 'branches';
    }

    public function getIndex()
    {
        return view('admin.branches.view', self::$data);
    }

    public function getList(Request $request)
    {
        $search = $request->get('generalSearch') ?? $request->get('search_value') ?? $request->input('search.value');

        $branches = Branch::withCount('applications')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->orderBy('id', 'desc');

        return DataTables::of($branches)
            ->addIndexColumn()
            ->addColumn('name', fn ($branch) => view('admin.branches.parts.name', ['branch' => $branch])->render())
            ->addColumn('status', fn ($branch) => view('admin.branches.parts.status', ['branch' => $branch])->render())
            ->addColumn('actions', fn ($branch) => view('admin.branches.parts.actions', ['branch' => $branch])->render())
            ->rawColumns(['name', 'status', 'actions'])
            ->toJson();
    }

    public function getAdd()
    {
        return view('admin.branches.add', self::$data + ['info' => null]);
    }

    public function postAdd(BranchRequest $request)
    {
        Branch::create($request->safe()->all() + [
            'status' => $request->boolean('status', true)]);

        return redirect()->route('branches.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $branch = Branch::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('branches.view')->with('danger', __('app.not_found'));
        }

        return view('admin.branches.add', self::$data + ['info' => $branch]);
    }

    public function postEdit(BranchRequest $request, $id)
    {
        try {
            $branch = Branch::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('branches.view')->with('danger', __('app.not_found'));
        }

        $branch->update($request->safe()->all() + [
            'status' => $request->boolean('status', true)]);

        return redirect()->route('branches.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $branch = Branch::findOrFail(Crypt::decrypt($request->id));
            $branch->update(['status' => ! $branch->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $branch = Branch::findOrFail(Crypt::decrypt($request->id));

            if ($branch->applications()->exists() || $branch->students()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $branch->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
