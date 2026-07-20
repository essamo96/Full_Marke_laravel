<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GradingController extends Controller
{
    private const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function index()
    {
        $teacher = Auth::guard('teacher')->user();
        $groupIds = Group::where('teacher_id', $teacher->id)->pluck('id');

        $exams = Exam::whereIn('group_id', $groupIds)
            ->with('subject', 'group')
            ->latest()
            ->get();

        $submissionCounts = Grade::whereIn('exam_id', $exams->pluck('id'))
            ->selectRaw('exam_id, count(*) as total')
            ->groupBy('exam_id')
            ->pluck('total', 'exam_id');

        return view('teacher.grading.index', compact('exams', 'submissionCounts'));
    }

    public function exam(Exam $exam)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($exam->group && $exam->group->teacher_id === $teacher->id, 403);

        $exam->load('subject', 'group');

        $students = Student::whereHas('registrations', function ($q) use ($exam) {
            $q->where('group_id', $exam->group_id)->whereIn('status', self::ACTIVE_STATUSES);
        })->get();

        $excludedIds = $exam->excluded_student_ids ?? [];
        $students = $students->filter(fn ($s) => ! in_array($s->id, $excludedIds));

        $grades = Grade::where('exam_id', $exam->id)->get()->keyBy('student_id');

        return view('teacher.grading.exam', compact('exam', 'students', 'grades'));
    }

    public function show(Grade $grade)
    {
        $teacher = Auth::guard('teacher')->user();
        abort_unless($grade->group && $grade->group->teacher_id === $teacher->id, 403);

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
}
