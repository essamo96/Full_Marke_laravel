<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ExamRequest;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Question;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ExamAudience;
use App\Services\ExamNotifier;
use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class ExamController extends Controller
{
    protected $examService;

    protected ExamNotifier $examNotifier;

    public function __construct(ExamService $examService, ExamNotifier $examNotifier)
    {
        $this->examService = $examService;
        $this->examNotifier = $examNotifier;
    }

    private function teacherSubjectIds()
    {
        return Auth::guard('teacher')->user()->subjects()->pluck('subjects.id');
    }

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $groupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $exams = Exam::forGroups($groupIds)
            ->with('subject', 'group', 'groups')
            ->withCount(['questions', 'grades'])
            ->latest()
            ->paginate(12);

        return view('teacher.exams.index', compact('exams'));
    }

    public function create(Request $request)
    {
        $subjects = Subject::whereIn('id', $this->teacherSubjectIds())->with('groups')->get();

        // group_id arrives here as an encrypted string (set by the "New Exam"
        // link on the group page) rather than a raw id, so a plain URL never
        // exposes/leaks a real database id — decrypt it just to preselect the
        // matching subject/group in the form; ownership is still verified
        // before it's trusted for anything beyond that.
        $preselectedGroupId = null;
        $preselectedSubjectId = null;

        if ($request->filled('group_id')) {
            try {
                $group = Group::findOrFail(Crypt::decryptString($request->query('group_id')));
                if ($group->teacher_id === Auth::guard('teacher')->id()) {
                    $preselectedGroupId = $group->id;
                    $preselectedSubjectId = $group->subject_id;
                }
            } catch (DecryptException $e) {
                // Ignore an invalid/stale value — the form just opens blank.
            }
        }

        return view('teacher.exams.create', compact('subjects', 'preselectedGroupId', 'preselectedSubjectId'));
    }

    /** Every group the exam is being published to must belong to this teacher. */
    private function authorizeExamGroups(array $groupIds): void
    {
        $teacher = Auth::guard('teacher')->user();
        $groupIds = array_values(array_unique(array_map('intval', $groupIds)));

        abort_if($groupIds === [], 403);
        abort_unless(
            Group::whereIn('id', $groupIds)->where('teacher_id', $teacher->id)->count() === count($groupIds),
            403
        );
    }

    /** Teacher may open the exam (preview / PDF) if they teach at least one of its groups. */
    private function authorizeExamAccess(Exam $exam): void
    {
        abort_unless($exam->isTaughtBy(Auth::guard('teacher')->id()), 403);
    }

    /** Teacher may change the exam only if every group it targets is theirs. */
    private function authorizeExamOwner(Exam $exam): void
    {
        abort_unless($exam->isOwnedBy(Auth::guard('teacher')->id()), 403);
    }

    public function store(ExamRequest $request)
    {
        $this->authorizeExamGroups($request->validated()['group_ids']);

        $exam = $this->examService->saveExam($request->validated());

        $this->examNotifier->afterSave($exam, null);

        return redirect()->route('teacher.exams.index')->with('success', 'تم إنشاء الامتحان بنجاح.');
    }

    public function edit(Exam $exam)
    {
        $this->authorizeExamOwner($exam);

        $exam->load(['questions.options', 'group', 'groups']);
        $subjects = Subject::whereIn('id', $this->teacherSubjectIds())->with('groups', 'registrations.student')->get();

        return view('teacher.exams.edit', compact('exam', 'subjects'));
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $this->authorizeExamOwner($exam);
        $this->authorizeExamGroups($request->validated()['group_ids']);

        $oldStatus = $exam->status;
        $oldGroupIds = $exam->allGroupIds();
        $exam = $this->examService->saveExam($request->validated(), $exam);

        $this->examNotifier->afterSave($exam, $oldStatus, $oldGroupIds);

        return redirect()->route('teacher.exams.index')->with('success', 'تم تحديث الامتحان بنجاح.');
    }

    public function preview(Exam $exam)
    {
        $this->authorizeExamAccess($exam);

        \App\Support\ExamPaper::load($exam);
        $stats = \App\Support\ExamPaper::optionStats($exam);

        return view('teacher.exams.preview', compact('exam', 'stats'));
    }

    public function blankPdf(Exam $exam)
    {
        $this->authorizeExamAccess($exam);

        return \App\Support\ExamPaper::download($exam, $error)
            ?? redirect()->route('teacher.exams.index')->with('error', $error);
    }

    public function reorderQuestions(Request $request, Exam $exam)
    {
        $this->authorizeExamOwner($exam);

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

    /**
     * Students of several groups at once (the "exclude students" picker of a
     * multi-group exam). Raw group ids, same as getGroupStudents().
     */
    public function getGroupsStudents(Request $request, ExamAudience $audience)
    {
        $groupIds = collect((array) $request->query('group_ids'))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $this->authorizeExamGroups($groupIds);

        return response()->json($audience->pickerRows($groupIds));
    }
}
