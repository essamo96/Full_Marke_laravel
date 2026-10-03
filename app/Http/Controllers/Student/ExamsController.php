<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Services\StudentExamGrantService;
use Illuminate\Http\Request;

class ExamsController extends Controller
{
    public function index(StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();

        $groupIds = $student->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid', 'pending'])
            ->pluck('group_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $grantedExamIds = $examGrants->grantedExamIdsForStudent((int) $student->id);

        $exams = Exam::query()
            ->where('status', 'published')
            ->where(function ($q) use ($groupIds, $grantedExamIds) {
                $q->whereIn('group_id', $groupIds);
                if ($grantedExamIds->isNotEmpty()) {
                    $q->orWhereIn('id', $grantedExamIds);
                }
            })
            ->where(function ($query) use ($student) {
                $query->whereNull('excluded_student_ids')
                    ->orWhereJsonDoesntContain('excluded_student_ids', $student->id);
            })
            ->where(function ($q) {
                $q->whereNull('audience')
                    ->orWhereIn('audience', ['students', 'both']);
            })
            ->with(['grades' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->latest()
            ->get();

        return view('student.exams.index', compact('exams'));
    }

    public function take(Exam $exam, StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();

        $this->authorizeExamAccess($exam, $student, $examGrants);

        $existingGrade = \App\Models\Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingGrade) {
            return redirect()->route('student.results.show', $existingGrade->id)
                ->with('error', 'لقد قمت بتقديم هذا الامتحان مسبقاً.');
        }

        $exam->load('questions.options');

        $cacheKey = 'exam_start_'.$student->id.'_'.$exam->id;

        if (! \Illuminate\Support\Facades\Cache::has($cacheKey)) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, now(), now()->addDay());
        }

        return view('student.exams.take', compact('exam'));
    }

    public function recordViolation(Request $request, Exam $exam, StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();
        $this->authorizeExamAccess($exam, $student, $examGrants);

        $type = $request->input('type') === 'fullscreen_exit' ? 'fullscreen' : 'tab';
        $cacheKey = "exam_violation_{$type}_{$student->id}_{$exam->id}";

        $count = \Illuminate\Support\Facades\Cache::get($cacheKey, 0) + 1;
        \Illuminate\Support\Facades\Cache::put($cacheKey, $count, now()->addDay());

        $tabCount = \Illuminate\Support\Facades\Cache::get("exam_violation_tab_{$student->id}_{$exam->id}", 0);
        $fullscreenCount = \Illuminate\Support\Facades\Cache::get("exam_violation_fullscreen_{$student->id}_{$exam->id}", 0);

        return response()->json([
            'count' => $count,
            'total' => $tabCount + $fullscreenCount,
        ]);
    }

    public function submit(Request $request, Exam $exam, StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();

        $this->authorizeExamAccess($exam, $student, $examGrants);

        $existingGrade = \App\Models\Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingGrade) {
            return redirect()->route('student.results.show', $existingGrade->id)
                ->with('error', 'لا يمكنك تسليم الامتحان أكثر من مرة. تم احتساب نتيجتك السابقة.');
        }

        $exam->load('questions.options');

        $totalPoints = 0;
        $earnedPoints = 0;
        $answerRows = [];

        foreach ($exam->questions as $question) {
            $totalPoints += $question->points;

            $answerId = $request->input('answers.'.$question->id);

            if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                $correctOption = $question->options->where('is_correct', true)->first();
                $isCorrect = $correctOption && $answerId == $correctOption->id;
                $pointsEarned = $isCorrect ? $question->points : 0;
                if ($isCorrect) {
                    $earnedPoints += $question->points;
                }

                $answerRows[] = [
                    'question_id' => $question->id,
                    'selected_option_id' => $answerId ?: null,
                    'essay_answer' => null,
                    'is_correct' => $isCorrect,
                    'points_earned' => $pointsEarned,
                ];
            } elseif ($question->type === 'essay') {
                $answerRows[] = [
                    'question_id' => $question->id,
                    'selected_option_id' => null,
                    'essay_answer' => $request->input('answers.'.$question->id),
                    'is_correct' => null,
                    'points_earned' => null,
                ];
            }
        }

        $cacheKey = 'exam_start_'.$student->id.'_'.$exam->id;
        $startTime = \Illuminate\Support\Facades\Cache::get($cacheKey);
        $timeTaken = $startTime ? abs(now()->diffInMinutes($startTime)) : null;

        $tabViolationKey = "exam_violation_tab_{$student->id}_{$exam->id}";
        $fullscreenViolationKey = "exam_violation_fullscreen_{$student->id}_{$exam->id}";
        $tabSwitchCount = \Illuminate\Support\Facades\Cache::get($tabViolationKey, 0);
        $fullscreenExitCount = \Illuminate\Support\Facades\Cache::get($fullscreenViolationKey, 0);
        $autoSubmitted = $request->boolean('auto_submitted');

        $notes = 'تم التصحيح الآلي (باستثناء الأسئلة المقالية إن وجدت)';
        if ($autoSubmitted) {
            $notes = 'تم إنهاء الامتحان تلقائياً بسبب تجاوز عدد مرات الخروج المسموح بها من صفحة الامتحان';
        }

        $grade = \App\Models\Grade::create([
            'student_id' => $student->id,
            'group_id' => $exam->group_id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->title,
            'score' => $earnedPoints,
            'max_score' => $totalPoints,
            'notes' => $notes,
            'started_at' => $startTime,
            'time_taken_minutes' => $timeTaken,
            'tab_switch_count' => $tabSwitchCount,
            'fullscreen_exit_count' => $fullscreenExitCount,
            'auto_submitted' => $autoSubmitted,
        ]);

        foreach ($answerRows as $row) {
            \App\Models\ExamAnswer::create($row + [
                'grade_id' => $grade->id,
                'exam_id' => $exam->id,
                'student_id' => $student->id,
            ]);
        }

        \Illuminate\Support\Facades\Cache::forget($cacheKey);
        \Illuminate\Support\Facades\Cache::forget($tabViolationKey);
        \Illuminate\Support\Facades\Cache::forget($fullscreenViolationKey);

        return redirect()->route('student.results.show', $grade)
            ->with('success', "تم استلام امتحانك بنجاح. نتيجتك المبدئية: {$earnedPoints} من {$totalPoints}.");
    }

    protected function authorizeExamAccess(Exam $exam, $student, StudentExamGrantService $examGrants): void
    {
        $groupIds = $student->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid', 'pending'])
            ->pluck('group_id');

        abort_unless(
            $examGrants->studentCanAccessExam($exam, (int) $student->id, $groupIds),
            403,
            'ليس لديك صلاحية للوصول إلى هذا الامتحان.'
        );
    }
}
