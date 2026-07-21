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
        $search = $request->get('name') ?? $request->get('generalSearch') ?? $request->get('search_value');

        $groups = Group::when($subject, fn($q) => $q->where('subject_id', $subject->id))
            ->when(!$subject && $request->filled('subject_id'), fn($q) => $q->where('subject_id', $request->subject_id))
            ->when($request->filled('teacher_id'), fn($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->filled('is_active'), fn($q) => $q->where('is_active', $request->is_active))
            ->with(['teacher', 'subject'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->latest();

        return DataTables::of($groups)
            ->addColumn('name', fn ($group) => view('admin.groups.parts.name', ['group' => $group])->render())
            ->addColumn('teacher', fn ($group) => view('admin.groups.parts.teacher', ['group' => $group])->render())
            ->addColumn('capacity', fn ($group) => view('admin.groups.parts.capacity', ['group' => $group])->render())
            ->addColumn('status', fn ($group) => view('admin.groups.parts.status', ['group' => $group])->render())
            ->addColumn('actions', fn ($group) => view('admin.groups.parts.actions', ['group' => $group, 'subject' => $subject ?? $group->subject])->render())
            ->rawColumns(['name', 'teacher', 'capacity', 'status', 'actions'])
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

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('groups', 'public');
        }

        Group::create($data + [
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

        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('groups', 'public');
        }

        $group->update($data + [
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

    public function getStudents($id)
    {
        $group = Group::with('subject')->findOrFail(Crypt::decrypt($id));
        return view('admin.groups.students', self::$data + ['group' => $group]);
    }

    public function getStudentsList(Request $request, $id)
    {
        $group = Group::findOrFail(Crypt::decrypt($id));
        $search = $request->get('name') ?? $request->get('generalSearch') ?? $request->get('search_value');

        $registrations = \App\Models\Registration::with('student')
            ->where('group_id', $group->id)
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->when($search, fn($q) => $q->whereHas('student', fn($sq) => $sq
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest();

        return DataTables::of($registrations)
            ->addColumn('student', fn ($reg) => view('admin.students.parts.name', ['student' => $reg->student])->render())
            ->addColumn('phone', fn ($reg) => $reg->student->phone)
            ->addColumn('status', fn ($reg) => '<span class="badge badge-light-'.($reg->status=='active'?'success':'warning').'">'.__('app.status_'.$reg->status).'</span>')
            ->addColumn('actions', fn ($reg) => view('admin.groups.parts.students_actions', ['registration' => $reg])->render())
            ->rawColumns(['student', 'status', 'actions'])
            ->toJson();
    }

    public function getDetails($id)
    {
        $group = Group::with(['teacher', 'subject.program'])->findOrFail(Crypt::decrypt($id));
        return view('admin.groups.parts.details', ['group' => $group]);
    }
    public function postGenerateCode(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'max_uses' => 'required|integer|min:1',
            'expires_at' => 'required|date',
        ]);

        try {
            $groupId = Crypt::decrypt($request->group_id);
            $group = Group::findOrFail($groupId);

            // Generate a random unique code
            do {
                $code = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 8));
            } while (\App\Models\GroupJoinCode::where('code', $code)->exists());

            $group->joinCodes()->create([
                'code' => $code,
                'max_uses' => $request->max_uses,
                'expires_at' => $request->expires_at,
            ]);

            return response()->json([
                'success' => true,
                'code' => $code,
                'message' => 'تم إنشاء الكود بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء المعالجة'
            ], 500);
        }
    }
}
