<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use App\Services\ExamDraftService;
use App\Services\StudentExamGrantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExamsController extends Controller
{
    public function index(StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();

        $groupIds = $student->registrations()
            ->whereIn('status', Exam::ACCESS_REGISTRATION_STATUSES)
            ->pluck('group_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $grantedExamIds = $examGrants->grantedExamIdsForStudent((int) $student->id);

        $exams = Exam::query()
            ->where('status', 'published')
            ->where(function ($q) use ($groupIds, $grantedExamIds) {
                $q->forGroups($groupIds);
                if ($grantedExamIds->isNotEmpty()) {
                    $q->orWhereIn('exams.id', $grantedExamIds);
                }
            })
            ->where(function ($q) {
                $q->whereNull('audience')
                    ->orWhereIn('audience', ['students', 'both']);
            })
            ->with(['subject', 'group', 'groups', 'grades' => function ($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->latest()
            ->get()
            // excluded_student_ids can hold ints or numeric strings, so compare in PHP instead of via JSON containment
            ->reject(fn (Exam $exam) => $exam->isStudentExcluded((int) $student->id))
            ->values();

        return view('student.exams.index', compact('exams', 'groupIds'));
    }

    public function take(Exam $exam, StudentExamGrantService $examGrants, ExamDraftService $drafts)
    {
        $student = auth('student')->user();

        $this->authorizeExamAccess($exam, $student, $examGrants);

        $existingGrade = Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingGrade) {
            return redirect()->route('student.results.show', $existingGrade->id)
                ->with('error', 'لقد قمت بتقديم هذا الامتحان مسبقاً.');
        }

        $exam->load('questions.options');

        // The countdown and the autosaved answers live on the server, so a reload,
        // a dropped connection or a second device can never reset or lose them.
        $attempt = $drafts->attemptFor($exam, $student);
        if ($attempt->submitted_at) {
            // Already handed in, yet no grade exists any more (it was deleted so the student can retake):
            // start a clean attempt instead of replaying the old answers / expired clock.
            $attempt = $drafts->restart($attempt);
        }

        $examState = [
            'examId' => $exam->id,
            'attemptId' => $attempt->id,
            'studentId' => $student->id,
            'draftUrl' => route('student.exams.draft', $exam),
            'submitUrl' => route('student.exams.submit', $exam),
            'violationUrl' => route('student.exams.violation', $exam),
            'questionIds' => $exam->questions->pluck('id')->values(),
            'answers' => (object) ($attempt->answers ?? []),
            'started' => (bool) $attempt->started_at,
            'remainingSeconds' => $drafts->remainingSeconds($exam, $attempt),
            'serverTime' => (int) (microtime(true) * 1000),
        ];

        return view('student.exams.take', compact('exam', 'examState'));
    }

    /**
     * Autosave endpoint hit by the exam page: stores the answers, starts the
     * countdown on first use, and returns the authoritative state back.
     */
    public function saveDraft(Request $request, Exam $exam, StudentExamGrantService $examGrants, ExamDraftService $drafts): JsonResponse
    {
        $student = auth('student')->user();
        $this->authorizeExamAccess($exam, $student, $examGrants);

        $grade = Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->first();
        if ($grade) {
            return response()->json([
                'submitted' => true,
                'redirect' => route('student.results.show', $grade),
            ], 409);
        }

        $data = $request->validate([
            'answers' => 'nullable|array',
            'begin' => 'nullable|boolean',
        ]);

        $attempt = $drafts->attemptFor($exam, $student);

        if ($request->boolean('begin')) {
            $drafts->begin($attempt);
        }

        $stored = $attempt->answers ?? [];
        if (! empty($data['answers'])) {
            $stored = $drafts->save($exam, $attempt, $data['answers']);
        }

        return response()->json([
            'saved' => true,
            'answers' => (object) $stored,
            'started' => (bool) $attempt->started_at,
            'remainingSeconds' => $drafts->remainingSeconds($exam, $attempt),
            'serverTime' => (int) (microtime(true) * 1000),
            'csrf' => csrf_token(),
        ]);
    }

    public function recordViolation(Request $request, Exam $exam, StudentExamGrantService $examGrants)
    {
        $student = auth('student')->user();
        $this->authorizeExamAccess($exam, $student, $examGrants);

        $type = $request->input('type') === 'fullscreen_exit' ? 'fullscreen' : 'tab';
        $cacheKey = "exam_violation_{$type}_{$student->id}_{$exam->id}";

        $count = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, now()->addDay());

        $tabCount = Cache::get("exam_violation_tab_{$student->id}_{$exam->id}", 0);
        $fullscreenCount = Cache::get("exam_violation_fullscreen_{$student->id}_{$exam->id}", 0);

        return response()->json([
            'count' => $count,
            'total' => $tabCount + $fullscreenCount,
        ]);
    }

    public function submit(Request $request, Exam $exam, StudentExamGrantService $examGrants, ExamDraftService $drafts)
    {
        $student = auth('student')->user();

        $this->authorizeExamAccess($exam, $student, $examGrants);

        $existingGrade = Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();

        if ($existingGrade) {
            return $this->submitted(
                $request,
                $existingGrade,
                'error',
                'لا يمكنك تسليم الامتحان أكثر من مرة. تم احتساب نتيجتك السابقة.'
            );
        }

        $exam->load('questions.options');

        $state = $this->decodeState($request->input('answers_state'));
        $formAnswers = (array) $request->input('answers', []);
        $autoSubmitted = $request->boolean('auto_submitted');

        $grade = DB::transaction(function () use ($exam, $student, $drafts, $state, $formAnswers, $autoSubmitted) {
            // The attempt row doubles as a lock: a retried / double-fired submit (very common
            // right after a connection comes back) waits here and then finds the grade already
            // created, instead of producing a second grade.
            $attempt = $drafts->attemptFor($exam, $student);
            $attempt = ExamAttempt::whereKey($attempt->id)->lockForUpdate()->first();

            $already = Grade::where('student_id', $student->id)->where('exam_id', $exam->id)->first();
            if ($already) {
                return $already;
            }

            $answers = $drafts->finalAnswers($exam, $attempt, $state, $formAnswers);

            $totalPoints = 0;
            $earnedPoints = 0;
            $answerRows = [];

            foreach ($exam->questions as $question) {
                $totalPoints += $question->points;
                $answer = $answers[$question->id] ?? null;

                if ($question->type === 'multiple_choice' || $question->type === 'true_false') {
                    $correctOption = $question->options->where('is_correct', true)->first();
                    $isCorrect = $correctOption && $answer !== null && (int) $answer === (int) $correctOption->id;
                    $pointsEarned = $isCorrect ? $question->points : 0;
                    if ($isCorrect) {
                        $earnedPoints += $question->points;
                    }

                    $answerRows[] = [
                        'question_id' => $question->id,
                        'selected_option_id' => $answer ?: null,
                        'essay_answer' => null,
                        'is_correct' => $isCorrect,
                        'points_earned' => $pointsEarned,
                    ];
                } elseif ($question->type === 'essay') {
                    $answerRows[] = [
                        'question_id' => $question->id,
                        'selected_option_id' => null,
                        'essay_answer' => $answer,
                        'is_correct' => null,
                        'points_earned' => null,
                    ];
                }
            }

            $startTime = $attempt->started_at ?? Cache::get('exam_start_'.$student->id.'_'.$exam->id);
            $timeTaken = $startTime ? abs(now()->diffInMinutes($startTime)) : null;

            $tabViolationKey = "exam_violation_tab_{$student->id}_{$exam->id}";
            $fullscreenViolationKey = "exam_violation_fullscreen_{$student->id}_{$exam->id}";
            $tabSwitchCount = Cache::get($tabViolationKey, 0);
            $fullscreenExitCount = Cache::get($fullscreenViolationKey, 0);

            $notes = 'تم التصحيح الآلي (باستثناء الأسئلة المقالية إن وجدت)';
            if ($autoSubmitted) {
                $notes = 'تم إنهاء الامتحان تلقائياً بسبب تجاوز عدد مرات الخروج المسموح بها من صفحة الامتحان';
            }

            // A timed exam handed in well after its time ran out can only mean the student's
            // connection was down when the timer ended; keep that visible to the teacher.
            $remaining = $drafts->remainingSeconds($exam, $attempt);
            if ($remaining === 0 && $attempt->started_at
                && abs(now()->diffInSeconds($attempt->started_at)) > ($exam->duration_minutes * 60) + 120) {
                $notes .= ' — سُلّم الامتحان بعد انتهاء وقته المحدد (غالباً بسبب انقطاع الاتصال)';
            }

            $grade = Grade::create([
                'student_id' => $student->id,
                'group_id' => $this->gradeGroupId($exam, $student),
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
                ExamAnswer::create($row + [
                    'grade_id' => $grade->id,
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                ]);
            }

            $attempt->forceFill(['submitted_at' => now()])->save();

            Cache::forget('exam_start_'.$student->id.'_'.$exam->id);
            Cache::forget($tabViolationKey);
            Cache::forget($fullscreenViolationKey);

            return $grade;
        });

        $earned = $grade->score;
        $total = $grade->max_score;

        return $this->submitted($request, $grade, 'success', "تم استلام امتحانك بنجاح. نتيجتك المبدئية: {$earned} من {$total}.");
    }

    /** Redirect for plain form posts, JSON (with the same flash message) for the autosaving page. */
    private function submitted(Request $request, Grade $grade, string $flashKey, string $message)
    {
        $url = route('student.results.show', $grade);

        if ($request->expectsJson()) {
            session()->flash($flashKey, $message);

            return response()->json(['submitted' => true, 'redirect' => $url]);
        }

        return redirect($url)->with($flashKey, $message);
    }

    /** @return array<string|int, mixed> */
    private function decodeState(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * An exam can target several groups; the grade belongs to the one the
     * student is actually registered in (so group-level reports stay right).
     */
    private function gradeGroupId(Exam $exam, $student): ?int
    {
        $examGroupIds = $exam->allGroupIds();

        $registeredGroupId = $student->registrations()
            ->whereIn('group_id', $examGroupIds)
            ->whereIn('status', Exam::ACCESS_REGISTRATION_STATUSES)
            ->orderByRaw("case status when 'fully_paid' then 0 when 'partially_paid' then 1 else 2 end")
            ->value('group_id');

        return $registeredGroupId ?: $exam->group_id;
    }

    protected function authorizeExamAccess(Exam $exam, $student, StudentExamGrantService $examGrants): void
    {
        $groupIds = $student->registrations()
            ->whereIn('status', Exam::ACCESS_REGISTRATION_STATUSES)
            ->pluck('group_id');

        abort_unless(
            $examGrants->studentCanAccessExam($exam, (int) $student->id, $groupIds),
            403,
            'ليس لديك صلاحية للوصول إلى هذا الامتحان.'
        );
    }
}
