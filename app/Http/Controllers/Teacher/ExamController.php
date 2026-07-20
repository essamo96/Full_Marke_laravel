<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamRequest;
use App\Jobs\NotifyStudentsOfNewExam;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    private function teacherSubjectIds()
    {
        return Auth::guard('teacher')->user()->subjects()->pluck('subjects.id');
    }

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $groupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $exams = Exam::whereIn('group_id', $groupIds)->with('subject', 'group')->latest()->paginate(10);

        return view('teacher.exams.index', compact('exams'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::whereIn('id', $this->teacherSubjectIds())->with('groups')->get();

        return view('teacher.exams.create', compact('subjects'));
    }

    private function authorizeExamGroup($groupId): void
    {
        $teacher = Auth::guard('teacher')->user();
        $group = Group::findOrFail($groupId);
        abort_unless($group->teacher_id === $teacher->id, 403);
    }

    public function store(ExamRequest $request)
    {
        $this->authorizeExamGroup($request->validated()['group_id']);

        $exam = $this->examService->saveExam($request->validated());

        if ($exam->status === 'published') {
            NotifyStudentsOfNewExam::dispatch($exam);
        }

        return redirect()->route('teacher.exams.index')->with('success', 'تم إنشاء الامتحان بنجاح.');
    }

    public function edit(Exam $exam)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($exam->group && $exam->group->teacher_id === $teacher->id, 403);

        $exam->load(['questions.options', 'group']);
        $subjects = Subject::whereIn('id', $this->teacherSubjectIds())->with('groups', 'registrations.student')->get();

        return view('teacher.exams.edit', compact('exam', 'subjects'));
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($exam->group && $exam->group->teacher_id === $teacher->id, 403);
        $this->authorizeExamGroup($request->validated()['group_id']);

        $oldStatus = $exam->status;
        $exam = $this->examService->saveExam($request->validated(), $exam);

        if ($oldStatus !== 'published' && $exam->status === 'published') {
            NotifyStudentsOfNewExam::dispatch($exam);
        }

        return redirect()->route('teacher.exams.index')->with('success', 'تم تحديث الامتحان بنجاح.');
    }

    public function reorderQuestions(Request $request, Exam $exam)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($exam->group && $exam->group->teacher_id === $teacher->id, 403);

        $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'exists:questions,id',
        ]);

        foreach ($request->ordered_ids as $index => $id) {
            Question::where('id', $id)->where('exam_id', $exam->id)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    public function getSubjectGroups(Subject $subject)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($this->teacherSubjectIds()->contains($subject->id), 403);

        $groups = $subject->groups()->where('teacher_id', $teacher->id)->select('id', 'name')->get();

        return response()->json($groups);
    }

    public function getGroupStudents($groupId)
    {
        // Note: $groupId is the raw `groups.id` FK value submitted by the group_id select
        // on this same form (not Group's encrypted route key), so this endpoint intentionally
        // takes a plain id instead of route-model-binding Group.
        $teacher = Auth::guard('teacher')->user();
        $group = Group::findOrFail($groupId);
        abort_unless($group->teacher_id === $teacher->id, 403);

        $students = Student::whereHas('registrations', function ($query) use ($group) {
            $query->where('group_id', $group->id)->whereIn('status', ['pending', 'partially_paid', 'fully_paid']);
        })->select('id', 'full_name_ar', 'full_name_en')->get();

        return response()->json($students);
    }
}
