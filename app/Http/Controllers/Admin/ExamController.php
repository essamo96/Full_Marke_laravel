<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use App\Services\ExamService;
use App\Services\ExamAudience;
use App\Services\ExamNotifier;
use App\Http\Requests\Admin\ExamRequest;
use Illuminate\Http\Request;

class ExamController extends AdminController
{
    protected $examService;

    protected ExamNotifier $examNotifier;

    public function __construct(ExamService $examService, ExamNotifier $examNotifier)
    {
        parent::__construct();
        parent::$data['active_menu'] = 'exams';
        $this->examService = $examService;
        $this->examNotifier = $examNotifier;
    }

    public function index()
    {
        $exams = Exam::with('subject', 'group', 'groups')->latest()->paginate(10);
        return view('admin.exams.index', self::$data + compact('exams'));
    }

    public function create()
    {
        $subjects = Subject::with('groups')->get();
        return view('admin.exams.create', self::$data + compact('subjects'));
    }

    public function store(ExamRequest $request)
    {
        $exam = $this->examService->saveExam($request->validated());

        $this->examNotifier->afterSave($exam, null, [], true);

        return redirect()->route('exams.view')
            ->with('success', 'تم إنشاء الامتحان بنجاح.');
    }

    public function edit(Exam $exam)
    {
        $exam->load(['questions.options', 'group', 'groups']);
        $subjects = Subject::with('groups', 'registrations.student')->get();
        return view('admin.exams.edit', self::$data + compact('exam', 'subjects'));
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $oldStatus = $exam->status;
        $oldGroupIds = $exam->allGroupIds();
        $exam = $this->examService->saveExam($request->validated(), $exam);

        $this->examNotifier->afterSave($exam, $oldStatus, $oldGroupIds, true);

        return redirect()->route('exams.view')
            ->with('success', 'تم تحديث الامتحان بنجاح.');
    }

    public function preview(Exam $exam)
    {
        \App\Support\ExamPaper::load($exam);
        $stats = \App\Support\ExamPaper::optionStats($exam);
        $pdfRoute = route('exams.blank-pdf', $exam);

        return view('admin.exams.preview', self::$data + compact('exam', 'stats', 'pdfRoute'));
    }

    public function blankPdf(Exam $exam)
    {
        return \App\Support\ExamPaper::download($exam, $error)
            ?? redirect()->route('exams.view')->with('error', $error)->with('danger', $error);
    }

    public function destroy(Exam $exam)
    {
        $exam->delete();
        return redirect()->route('exams.view')
            ->with('success', 'تم حذف الامتحان بنجاح.');
    }

    public function reorderQuestions(Request $request, Exam $exam)
    {
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
        $groups = $subject->groups()->select('id', 'name')->get();
        return response()->json($groups);
    }

    public function getGroupStudents($groupId)
    {
        // Note: $groupId here is the raw `groups.id` FK value submitted by the group_id
        // select on this same form (not Group's encrypted route key), so this endpoint
        // intentionally takes a plain id instead of route-model-binding Group.
        $students = \App\Models\Student::whereHas('registrations', function ($query) use ($groupId) {
            $query->where('group_id', $groupId)->whereIn('status', ['pending', 'partially_paid', 'fully_paid']);
        })->select('id', 'full_name_ar', 'full_name_en')->get();

        return response()->json($students);
    }

    /** Students of several groups at once (the exclude-students picker of a multi-group exam). */
    public function getGroupsStudents(Request $request, ExamAudience $audience)
    {
        $groupIds = collect((array) $request->query('group_ids'))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        return response()->json($audience->pickerRows($groupIds));
    }

    public function results(Request $request, Exam $exam)
    {
        $exam->load('subject', 'group', 'groups');

        // Students of every group the exam targets (one exam can span several groups).
        $studentsQuery = \App\Models\Student::whereHas('registrations', function ($q) use ($exam) {
            $q->whereIn('group_id', $exam->allGroupIds())
              ->whereIn('status', ['partially_paid', 'fully_paid']);
        })->with(['registrations' => fn ($q) => $q->whereIn('group_id', $exam->allGroupIds())->with('group:id,name')]);

        if ($request->filled('student_name')) {
            $studentsQuery->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%')
                  ->orWhere('full_name_ar', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->filled('status')) {
            if ($request->status == 'submitted') {
                $studentsQuery->whereIn('id', function($q) use ($exam) {
                    $q->select('student_id')->from('grades')->where('exam_id', $exam->id);
                });
            } else if ($request->status == 'not_submitted') {
                $studentsQuery->whereNotIn('id', function($q) use ($exam) {
                    $q->select('student_id')->from('grades')->where('exam_id', $exam->id);
                });
            }
        }

        if ($excluded = $exam->excludedStudentIds()) {
            $studentsQuery->whereNotIn('id', $excluded);
        }

        $students = $studentsQuery->get();
        $grades = \App\Models\Grade::where('exam_id', $exam->id)->get()->keyBy('student_id');

        $guestSubmissions = $exam->allowsGuests()
            ? \App\Models\ExamGuestSubmission::where('exam_id', $exam->id)->latest()->get()
            : collect();

        return view('admin.exams.results', self::$data + compact('exam', 'students', 'grades', 'guestSubmissions'));
    }

    /**
     * Re-marks the exam's submitted grades against the current answer key (e.g. after the correct
     * option was fixed). Grades and answers are only corrected, never deleted.
     */
    public function regrade(Exam $exam, \App\Services\ExamRegrader $regrader)
    {
        $report = $regrader->regradeExam($exam);

        $message = $report['changed'] > 0
            ? "تمت إعادة التصحيح: تم تحديث علامات {$report['changed']} من أصل {$report['checked']} طالب وفق مفتاح الإجابة الحالي."
            : "تمت إعادة التصحيح: جميع العلامات ({$report['checked']}) مطابقة لمفتاح الإجابة الحالي، لا يوجد تغيير.";

        if ($report['skipped'] > 0) {
            $message .= " ({$report['skipped']} تسليم بدون إجابات مسجلة لم يتم تعديله.)";
        }

        return back()->with('success', $message);
    }

    public function approveGrade(\App\Models\Grade $grade)
    {
        $grade->update(['admin_approved_at' => now()]);

        return back()->with('success', 'تم اعتماد علامة الطالب بنجاح.');
    }

    public function gradeAnswers(\App\Models\Grade $grade)
    {
        \App\Services\GradeAnswerRebuilder::ensure($grade);

        $grade->load([
            'student:id,full_name_ar,full_name_en,phone',
            'exam:id,title,allow_student_review',
            'group:id,name',
            'answers.question.options',
            'answers.selectedOption',
        ]);

        $html = view('admin.exams.partials.grade-answers-modal-body', compact('grade'))->render();

        return response()->json([
            'success' => true,
            'student' => $grade->student?->full_name_ar ?? $grade->student?->full_name_en,
            'exam' => $grade->exam?->title ?? $grade->exam_name,
            'score' => $grade->score,
            'max_score' => $grade->max_score,
            'html' => $html,
        ]);
    }

    public function allResults(Request $request)
    {
        $query = \App\Models\Grade::with(['student', 'exam', 'group']);

        if ($request->filled('student_name')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->student_name . '%')
                  ->orWhere('full_name_ar', 'like', '%' . $request->student_name . '%');
            });
        }

        if ($request->filled('exam_name')) {
            $query->whereHas('exam', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->exam_name . '%');
            });
        }
        
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $grades = $query->latest()->paginate(20)->appends($request->all());
        $groups = \App\Models\Group::all();
        
        return view('admin.exams.all_results', self::$data + compact('grades', 'groups'));
    }
}
