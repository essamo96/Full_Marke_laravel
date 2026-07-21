<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use App\Services\ExamService;
use App\Jobs\NotifyStudentsOfNewExam;
use App\Http\Requests\Admin\ExamRequest;
use Illuminate\Http\Request;

class ExamController extends AdminController
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        parent::__construct();
        parent::$data['active_menu'] = 'exams';
        $this->examService = $examService;
    }

    public function index()
    {
        $exams = Exam::with('subject', 'group')->latest()->paginate(10);
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

        if ($exam->status === 'published') {
            NotifyStudentsOfNewExam::dispatch($exam);
        }

        return redirect()->route('exams.view')
            ->with('success', 'تم إنشاء الامتحان بنجاح.');
    }

    public function edit(Exam $exam)
    {
        $exam->load(['questions.options']);
        $subjects = Subject::with('groups', 'registrations.student')->get();
        return view('admin.exams.edit', self::$data + compact('exam', 'subjects'));
    }

    public function update(ExamRequest $request, Exam $exam)
    {
        $oldStatus = $exam->status;
        $exam = $this->examService->saveExam($request->validated(), $exam);

        if ($oldStatus !== 'published' && $exam->status === 'published') {
            NotifyStudentsOfNewExam::dispatch($exam);
        }

        return redirect()->route('exams.view')
            ->with('success', 'تم تحديث الامتحان بنجاح.');
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

    public function results(Request $request, Exam $exam)
    {
        $exam->load('subject', 'group');

        $studentsQuery = \App\Models\Student::whereHas('registrations', function ($q) use ($exam) {
            $q->where('group_id', $exam->group_id)
              ->whereIn('status', ['partially_paid', 'fully_paid']);
        });

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

        if (!empty($exam->excluded_student_ids)) {
            $studentsQuery->whereNotIn('id', $exam->excluded_student_ids);
        }

        $students = $studentsQuery->get();
        $grades = \App\Models\Grade::where('exam_id', $exam->id)->get()->keyBy('student_id');

        return view('admin.exams.results', self::$data + compact('exam', 'students', 'grades'));
    }

    public function approveGrade(\App\Models\Grade $grade)
    {
        $grade->update(['admin_approved_at' => now()]);

        return back()->with('success', 'تم اعتماد علامة الطالب بنجاح.');
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
