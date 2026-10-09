<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\Grade;
use App\Models\Group;
use App\Services\ExamAudience;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $groupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $exams = Exam::forGroups($groupIds)
            ->with('subject', 'group', 'groups')
            ->latest()
            ->get();

        $submissionCounts = Grade::whereIn('exam_id', $exams->pluck('id'))
            ->selectRaw('exam_id, count(*) as total')
            ->groupBy('exam_id')
            ->pluck('total', 'exam_id');

        return view('teacher.grading.index', compact('exams', 'submissionCounts'));
    }

    public function exam(Exam $exam, ExamAudience $audience)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($exam->isTaughtBy($teacher->id), 403);

        $exam->load('subject', 'group', 'groups');

        // One exam can span several groups: list the students of every group this
        // teacher teaches (excluded students are already left out by the audience).
        $examGroupIds = $exam->allGroupIds();
        $teacherGroupIds = Group::where('teacher_id', $teacher->id)
            ->whereIn('id', $examGroupIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $students = $audience->query($exam, $teacherGroupIds)
            ->with(['registrations' => fn ($q) => $q
                ->whereIn('group_id', $teacherGroupIds)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->with('group:id,name')])
            ->orderBy('full_name_ar')
            ->get();

        $grades = Grade::where('exam_id', $exam->id)->get()->keyBy('student_id');

        return view('teacher.grading.exam', compact('exam', 'students', 'grades'));
    }

    public function show(Grade $grade)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($grade->group && $grade->group->teacher_id === $teacher->id, 403);

        \App\Services\GradeAnswerRebuilder::ensure($grade);

        $grade->load(['student', 'exam', 'answers.question.options', 'answers.selectedOption']);

        return view('teacher.grading.show', compact('grade'));
    }

    public function gradeEssay(Request $request, ExamAnswer $answer)
    {
        $teacher = Auth::guard('teacher')->user();
        $grade = $answer->grade;
        abort_unless($grade && $grade->group && $grade->group->teacher_id === $teacher->id, 403);

        $answer->load('question');

        $data = $request->validate([
            'points_earned' => 'required|numeric|min:0|max:' . $answer->question->points,
        ]);

        $answer->update([
            'points_earned' => $data['points_earned'],
            'is_correct' => $data['points_earned'] >= $answer->question->points,
        ]);

        $newScore = $grade->answers()->sum('points_earned');
        $grade->update([
            'score' => $newScore,
            'notes' => 'تم مراجعة الأسئلة المقالية من قبل المدرّس',
        ]);

        return back()->with('success', 'تم حفظ العلامة بنجاح.');
    }

    public function approve(Grade $grade)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($grade->group && $grade->group->teacher_id === $teacher->id, 403);

        $grade->update(['teacher_reviewed_at' => now()]);

        return back()->with('success', 'تم اعتماد العلامة وإرسالها للإدارة.');
    }
}
