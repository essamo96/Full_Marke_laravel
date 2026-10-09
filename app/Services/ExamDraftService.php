<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Server-side half of the "never lose / flip an answer" guarantee for online exams.
 *
 * The browser keeps every answer as { question_id: { v: value, t: epoch ms } } and
 * pushes the whole map whenever it can (and again after every reconnect). The
 * server merges per question, newest timestamp wins, so a request that was
 * delayed by a dropped connection and arrives late can never overwrite a newer
 * choice, and a student's earlier work is never wiped by a reload.
 */
class ExamDraftService
{
    private const ESSAY_MAX_LENGTH = 20000;

    /** Largest amount a client clock may run ahead of the server before it is clamped. */
    private const MAX_CLOCK_SKEW_MS = 300000;

    /**
     * The attempt row for this student and exam (created on first visit). A
     * timed attempt only starts counting once the student presses "start",
     * see begin().
     */
    public function attemptFor(Exam $exam, Student $student): ExamAttempt
    {
        return ExamAttempt::firstOrCreate(
            ['exam_id' => $exam->id, 'student_id' => $student->id],
            ['answers' => []]
        );
    }

    /** A fresh attempt row (new id) replacing a finished one, e.g. when a grade was deleted to allow a retake. */
    public function restart(ExamAttempt $attempt): ExamAttempt
    {
        $examId = $attempt->exam_id;
        $studentId = $attempt->student_id;
        $attempt->delete();

        return ExamAttempt::create(['exam_id' => $examId, 'student_id' => $studentId, 'answers' => []]);
    }

    /** Starts the countdown (idempotent); the server clock is the only authority. */
    public function begin(ExamAttempt $attempt): ExamAttempt
    {
        if (! $attempt->started_at) {
            // Attempts that were already running before this feature existed keep their original start.
            $legacy = Cache::get('exam_start_'.$attempt->student_id.'_'.$attempt->exam_id);
            $attempt->started_at = $legacy ? Carbon::parse($legacy) : now();
            $attempt->save();
        }

        return $attempt;
    }

    /** Seconds left, or null for an untimed exam. A not-yet-started attempt has its full duration. */
    public function remainingSeconds(Exam $exam, ExamAttempt $attempt): ?int
    {
        if (! $exam->duration_minutes) {
            return null;
        }

        $total = (int) $exam->duration_minutes * 60;

        if (! $attempt->started_at) {
            return $total;
        }

        return max(0, $total - (int) abs(now()->diffInSeconds($attempt->started_at)));
    }

    /**
     * Merges client answers into the stored draft (newest timestamp wins per
     * question) and returns the stored map.
     *
     * @param  array<int|string, mixed>  $incoming  { question_id: { v, t } }
     * @return array<int, array{v: int|string|null, t: int}>
     */
    public function save(Exam $exam, ExamAttempt $attempt, array $incoming): array
    {
        $clean = $this->sanitize($exam, $incoming);

        return DB::transaction(function () use ($attempt, $clean) {
            $locked = ExamAttempt::whereKey($attempt->id)->lockForUpdate()->first() ?? $attempt;
            $merged = self::merge($locked->answers ?? [], $clean);

            $locked->answers = $merged;
            $locked->saved_at = now();
            $locked->save();

            $attempt->setRawAttributes($locked->getAttributes(), true);

            return $merged;
        });
    }

    /**
     * The answers to grade on submit: the stored draft, topped up with what the
     * submitted form/state carries (newest per question wins).
     *
     * @param  array<int|string, mixed>  $state  timestamped client state ({ question_id: { v, t } }) if the client sent one
     * @param  array<int|string, mixed>  $formAnswers  plain answers[question_id] from the HTML form
     * @return array<int, int|string|null> question_id => value
     */
    public function finalAnswers(Exam $exam, ExamAttempt $attempt, array $state, array $formAnswers): array
    {
        $merged = $attempt->answers ?? [];

        if ($state !== []) {
            $merged = self::merge($merged, $this->sanitize($exam, $state));
        } else {
            // Legacy / script-less submit: the form is the student's final word, so it beats the draft.
            $now = (int) (microtime(true) * 1000);
            $merged = self::merge($merged, $this->sanitize($exam, $this->wrap($formAnswers, $now)));
        }

        // Anything still missing but present in the form is still better than nothing.
        $merged = self::merge($this->sanitize($exam, $this->wrap($formAnswers, 0)), $merged);

        return collect($merged)->map(fn ($entry) => $entry['v'] ?? null)->all();
    }

    /**
     * Per-question merge, newest timestamp wins (the incoming side wins ties).
     *
     * @param  array<int|string, array{v: mixed, t: int}>  $current
     * @param  array<int|string, array{v: mixed, t: int}>  $incoming
     * @return array<int, array{v: mixed, t: int}>
     */
    public static function merge(array $current, array $incoming): array
    {
        foreach ($incoming as $questionId => $entry) {
            $existing = $current[$questionId] ?? null;

            if ($existing === null || (int) $entry['t'] >= (int) ($existing['t'] ?? 0)) {
                $current[$questionId] = $entry;
            }
        }

        $result = [];
        foreach ($current as $questionId => $entry) {
            $result[(int) $questionId] = $entry;
        }

        return $result;
    }

    /**
     * Keeps only well-formed entries for real questions of this exam whose
     * value is valid for the question type (a real option / a bounded string).
     *
     * @return array<int, array{v: int|string|null, t: int}>
     */
    public function sanitize(Exam $exam, array $incoming): array
    {
        $questions = $exam->questions()->with('options')->get()->keyBy('id');

        $nowMs = (int) (microtime(true) * 1000);
        $clean = [];

        foreach ($incoming as $questionId => $entry) {
            /** @var Question|null $question */
            $question = $questions->get((int) $questionId);

            if (! $question || ! is_array($entry) || ! array_key_exists('v', $entry)) {
                continue;
            }

            $t = is_numeric($entry['t'] ?? null) ? (int) $entry['t'] : 0;
            $t = max(0, min($t, $nowMs + self::MAX_CLOCK_SKEW_MS));

            $clean[$question->id] = ['v' => $this->normalizeValue($question, $entry['v']), 't' => $t];
        }

        return $clean;
    }

    private function normalizeValue(Question $question, mixed $value): int|string|null
    {
        if ($question->type === 'essay') {
            $text = is_scalar($value) ? trim((string) $value) : '';

            return $text === '' ? null : mb_substr($text, 0, self::ESSAY_MAX_LENGTH);
        }

        if (! is_scalar($value) || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return $question->options->contains('id', (int) $value) ? (int) $value : null;
    }

    /** @return array<int|string, array{v: mixed, t: int}> */
    private function wrap(array $answers, int $timestamp): array
    {
        return collect($answers)->map(fn ($value) => ['v' => $value, 't' => $timestamp])->all();
    }
}
