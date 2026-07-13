<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\GroupRequest;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Yajra\DataTables\Facades\DataTables;

class GroupsController extends AdminController
{
    protected $path;

    public function __construct()
    {
        parent::__construct();
        self::$data['active_menu'] = 'groups';
        $this->path = 'groups';
    }

    public function getIndex($subjectId = null)
    {
        $subject = $subjectId ? Subject::with('program')->findOrFail(Crypt::decrypt($subjectId)) : null;
        $subjects = Subject::active()->orderBy('name_ar')->get();
        $teachers = Teacher::active()->orderBy('name')->get();
        return view('admin.groups.view', self::$data + [
            'subject' => $subject,
            'program' => $subject ? $subject->program : null,
            'subjects' => $subjects,
            'teachers' => $teachers
        ]);
    }

    public function getList(Request $request, $subjectId = null)
    {
        $subject = $subjectId ? Subject::findOrFail(Crypt::decrypt($subjectId)) : null;
        $search = $request->get('search_value');

        $groups = Group::when($subject, fn($q) => $q->where('subject_id', $subject->id))
            ->when(!$subject && $request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->filled('teacher_id'), fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->is_active))
            ->with(['teacher', 'subject'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest();

        return DataTables::of($groups)
            ->addColumn('teacher', fn ($group) => $group->teacher ? $group->teacher->name : '-')
            ->addColumn('capacity', fn ($group) => $group->registrations()->count() . ' / ' . $group->max_capacity)
            ->addColumn('status', fn ($group) => view('admin.groups.parts.status', ['group' => $group])->render())
            ->addColumn('actions', fn ($group) => view('admin.groups.parts.actions', ['group' => $group, 'subject' => $subject ?? $group->subject])->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }

    public function getAdd($subjectId = null)
    {
        $subject = $subjectId ? Subject::with('program')->findOrFail(Crypt::decrypt($subjectId)) : null;
        $subjects = $subject ? collect([$subject]) : Subject::active()->orderBy('name_ar')->get();
        $teachers = $subject ? $subject->teachers()->active()->orderBy('name')->get() : Teacher::active()->orderBy('name')->get();

        return view('admin.groups.add', self::$data + [
            'info' => null,
            'subject' => $subject,
            'subjects' => $subjects,
            'program' => $subject ? $subject->program : null,
            'teachers' => $teachers]);
    }

    public function postAdd(GroupRequest $request, $subjectId = null)
    {
        $subId = $subjectId ? Crypt::decrypt($subjectId) : $request->input('subject_id');
        $subject = Subject::findOrFail($subId);
        
        Group::create($request->validated() + [
            'subject_id' => $subject->id,
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('subjects.groups.view', Crypt::encrypt($subject->id))->with('success', __('app.insert_success'));
    }

    public function getEdit(Request $request, $subjectId, $id)
    {
        try {
            $subject = Subject::with('program')->findOrFail(Crypt::decrypt($subjectId));
            $group = Group::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subjects.groups.view', Crypt::encrypt($subjectId))->with('danger', __('app.not_found'));
        }

        return view('admin.groups.add', self::$data + [
            'info' => $group,
            'subject' => $subject,
            'program' => $subject->program,
            'teachers' => $subject->teachers()->active()->orderBy('name')->get()]);
    }

    public function postEdit(GroupRequest $request, $subjectId, $id)
    {
        try {
            $subject = Subject::findOrFail(Crypt::decrypt($subjectId));
            $group = Group::findOrFail(Crypt::decrypt($id));
        } catch (\Exception $e) {
            return redirect()->route('subjects.groups.view', Crypt::encrypt($subject->id))->with('danger', __('app.not_found'));
        }

        $group->update($request->validated() + [
            'is_active' => $request->boolean('is_active', true)]);

        return redirect()->route('subjects.groups.view', Crypt::encrypt($subject->id))->with('success', __('app.update_success'));
    }

    public function postStatus(Request $request)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($request->id));
            $group->update(['is_active' => ! $group->is_active]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }

    public function postDelete(Request $request)
    {
        try {
            $group = Group::findOrFail(Crypt::decrypt($request->id));

            if ($group->registrations()->exists()) {
                return response()->json(['success' => false, 'message' => __('app.execution_error')], 422);
            }

            $group->delete();

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 422);
        }
    }
}
