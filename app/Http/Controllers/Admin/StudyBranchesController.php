<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StudyBranchRequest;
use App\Models\StudyBranch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class StudyBranchesController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'study_branches';
        $this->path = 'study_branches';
    }

    public function getIndex(Request $request)
    {
        $search = $request->get('search');

        $studyBranches = StudyBranch::withCount('applications')
            ->when($search, fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name_ar', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%")))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.study_branches.view', self::$data + ['studyBranches' => $studyBranches]);
    }

    public function getAdd()
    {
        return view('admin.study_branches.add', self::$data + ['info' => null]);
    }

    public function postAdd(StudyBranchRequest $request)
    {
        StudyBranch::create($request->safe()->all() + [
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('study_branches.view')->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $id)
    {
        try {
            $studyBranch = StudyBranch::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('study_branches.view')->with('danger', __('app.not_found'));
        }

        return view('admin.study_branches.add', self::$data + ['info' => $studyBranch]);
    }

    public function postEdit(StudyBranchRequest $request, $id)
    {
        try {
            $studyBranch = StudyBranch::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('study_branches.view')->with('danger', __('app.not_found'));
        }

        $studyBranch->update($request->safe()->all() + [
            'status' => $request->boolean('status', true),
        ]);

        return redirect()->route('study_branches.view')->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $studyBranch = StudyBranch::findOrFail(Crypt::decrypt($request->id));
            $studyBranch->update(['status' => ! $studyBranch->status]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $studyBranch = StudyBranch::findOrFail(Crypt::decrypt($request->id));

            if ($studyBranch->applications()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $studyBranch->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
