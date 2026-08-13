<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamGuestAnswer;
use App\Models\ExamGuestSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GuestExamController extends Controller
{
    /**
     * Public entry point shared for the exam link. Logged-in students are sent
     * straight into the normal student flow; unauthenticated visitors land
     * here and, if the exam allows guests, register before taking it.
     */
    public function enter(Exam $exam)
    {
        if (auth('student')->check()) {
            return redirect()->route('student.exams.take', $exam);
        }

        abort_unless($exam->status === 'published', 404);
        abort_unless($exam->allowsGuests(), 403, 'هذا الامتحان مخصص لطلاب المنصة المسجلين فقط. الرجاء تسجيل الدخول.');

        $sessionKey = $this->sessionKey($exam);
        if (session()->has($sessionKey)) {
            return redirect()->route('guest.exam.take', $exam);
        }

        return view('guest.exams.register', compact('exam'));
    }

    public function register(Request $request, Exam $exam)
    {
        abort_unless($exam->status === 'published', 404);
        abort_unless($exam->allowsGuests(), 403);

        $data = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_phone' => 'required|string|max:30',
            'guest_email' => 'nullable|email|max:255',
        ], [], [
            'guest_name' => 'الاسم',
            'guest_phone' => 'رقم الجوال',
            'guest_email' => 'البريد الإلكتروني',
        ]);

        // A guest identified by phone can only submit an exam once, same as
        // a registered student — checked here rather than relying only on
        // the session so re-registering from a new tab can't bypass it.
        $alreadySubmitted = ExamGuestSubmission::where('exam_id', $exam->id)
            ->where('guest_phone', $data['guest_phone'])
            ->exists();

        if ($alreadySubmitted) {
            return back()->withErrors(['guest_phone' => 'تم تسجيل نتيجة سابقة لهذا الرقم على هذا الامتحان بالفعل.']);
        }

        session([$this->sessionKey($exam) => $data]);

        return redirect()->route('guest.exam.take', $exam);
    }

    public function take(Exam $exam)
    {
        abort_unless($exam->status === 'published', 404);
        abort_unless($exam->allowsGuests(), 403);

        $guest = session($this->sessionKey($exam));
        abort_unless($guest, 403);

        $alreadySubmitted = ExamGuestSubmission::where('exam_id', $exam->id)
            ->where('guest_phone', $guest['guest_phone'])
            ->first();

        if ($alreadySubmitted) {
            return redirect()->route('guest.exam.result', $alreadySubmitted);
        }

        $exam->load('questions.options');

        $cacheKey = $this->startCacheKey($exam, $guest['guest_phone']);
        if (! Cache::has($cacheKey)) {
            Cache::put($cacheKey, now(), now()->addDay());
        }

        return view('guest.exams.take', compact('exam', 'guest'));
    }

    public function recordViolation(Request $request, Exam $exam)
    {
        $guest = session($this->sessionKey($exam));
        abort_unless($guest, 403);

        $type = $request->input('type') === 'fullscreen_exit' ? 'fullscreen' : 'tab';
        $cacheKey = "guest_exam_violation_{$type}_{$guest['guest_phone']}_{$exam->id}";

        $count = Cache::get($cacheKey, 0) + 1;
        Cache::put($cacheKey, $count, now()->addDay());

        $tabCount = Cache::get("guest_exam_violation_tab_{$guest['guest_phone']}_{$exam->id}", 0);
        $fullscreenCount = Cache::get("guest_exam_violation_fullscreen_{$guest['guest_phone']}_{$exam->id}", 0);

        return response()->json([
            'count' => $count,
            'total' => $tabCount + $fullscreenCount,
        ]);
    }

    public function submit(Request $request, Exam $exam)
    {
        $guest = session($this->sessionKey($exam));
        abort_unless($guest, 403);

        $existing = ExamGuestSubmission::where('exam_id', $exam->id)
            ->where('guest_phone', $guest['guest_phone'])
            ->first();

        if ($existing) {
            return redirect()->route('guest.exam.result', $existing)
                ->with('error', 'لا يمكنك تسليم الامتحان أكثر من مرة. تم احتساب نتيجتك السابقة.');
        }

        $exam->load('questions.options');

        $totalPoints = 0;
        $earnedPoints = 0;
        $answerRows = [];

        foreach ($exam->questions as $question) {
            $totalPoints += $question->points;

            $answerId = $request->input('answers.' . $question->id);

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
                    'essay_answer' => $request->input('answers.' . $question->id),
                    'is_correct' => null,
                    'points_earned' => null,
                ];
            }
        }

        $cacheKey = $this->startCacheKey($exam, $guest['guest_phone']);
        $startTime = Cache::get($cacheKey);
        $timeTaken = $startTime ? abs(now()->diffInMinutes($startTime)) : null;

        $tabViolationKey = "guest_exam_violation_tab_{$guest['guest_phone']}_{$exam->id}";
        $fullscreenViolationKey = "guest_exam_violation_fullscreen_{$guest['guest_phone']}_{$exam->id}";
        $tabSwitchCount = Cache::get($tabViolationKey, 0);
        $fullscreenExitCount = Cache::get($fullscreenViolationKey, 0);
        $autoSubmitted = $request->boolean('auto_submitted');

        $notes = 'تم التصحيح الآلي (باستثناء الأسئلة المقالية إن وجدت) - طالب ضيف غير مسجل في المنصة';
        if ($autoSubmitted) {
            $notes = 'تم إنهاء الامتحان تلقائياً بسبب تجاوز عدد مرات الخروج المسموح بها من صفحة الامتحان - طالب ضيف';
        }

        $submission = ExamGuestSubmission::create([
            'exam_id' => $exam->id,
            'guest_name' => $guest['guest_name'],
            'guest_phone' => $guest['guest_phone'],
            'guest_email' => $guest['guest_email'] ?? null,
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
            ExamGuestAnswer::create($row + [
                'exam_guest_submission_id' => $submission->id,
                'exam_id' => $exam->id,
            ]);
        }

        Cache::forget($cacheKey);
        Cache::forget($tabViolationKey);
        Cache::forget($fullscreenViolationKey);
        session()->forget($this->sessionKey($exam));

        return redirect()->route('guest.exam.result', $submission)
            ->with('success', "تم استلام امتحانك بنجاح. نتيجتك المبدئية: {$earnedPoints} من {$totalPoints}.");
    }

    public function result(ExamGuestSubmission $submission)
    {
        $submission->load('exam');

        return view('guest.exams.result', compact('submission'));
    }

    private function sessionKey(Exam $exam): string
    {
        return "guest_exam_registration_{$exam->id}";
    }

    private function startCacheKey(Exam $exam, string $phone): string
    {
        return "guest_exam_start_{$phone}_{$exam->id}";
    }
}
