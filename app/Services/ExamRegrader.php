<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamAttempt;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;

/**
 * Re-marks already submitted grades against the exam's CURRENT answer key, without deleting anything.
 *
 * Typical cases it repairs:
 *  - the exam was published with no correct option marked (or the wrong one), so everybody got 0,
 *    and the teacher fixed the key afterwards;
 *  - a submission whose per-question rows lost the chosen option, while the autosaved draft
 *    (exam_attempts.answers) still holds it.
 *
 * What it never does:
 *  - delete a grade or an answer row;
 *  - touch essay marks given by the teacher;
 *  - guess: a question whose chosen option is unknown keeps the points it already had, and a grade
 *    with no recorded answers at all (neither rows nor draft) is left exactly as it is.
 */
class ExamRegrader
{
    public const NOTE = 'أُعيد احتساب العلامة وفق مفتاح الإجابة الحالي.';

    /**
     * Regrades every grade of the exam.
     *
     * @return array{checked: int, changed: int, skipped: int, details: array<int, array{grade_id: int, student_id: int, old: float, new: float, max: float, recovered: int}>}
     */
    public function regradeExam(Exam $exam, bool $dryRun = false): array
    {
        $exam->load('questions.options');

        $report = ['checked' => 0, 'changed' => 0, 'skipped' => 0, 'details' => []];

        Grade::where('exam_id', $exam->id)->with('answers')->orderBy('id')->each(function (Grade $grade) use ($exam, $dryRun, &$report) {
            $report['checked']++;
            $result = $this->regrade($grade, $exam, $dryRun);

            if ($result === null) {
                $report['skipped']++;

                return;
            }

            if ($result['old'] !== $result['new'] || $result['old_max'] !== $result['max'] || $result['recovered'] > 0) {
                $report['changed']++;
                $report['details'][] = [
                    'grade_id' => $grade->id,
                    'student_id' => (int) $grade->student_id,
                    'old' => $result['old'],
                    'new' => $result['new'],
                    'max' => $result['max'],
                    'recovered' => $result['recovered'],
                ];
            }
        });

        return $report;
    }

    /**
     * Regrades one grade. Returns null when there is nothing to grade from (no answer data at all).
     *
     * @return array{old: float, new: float, old_max: float, max: float, recovered: int}|null
     */
    public function regrade(Grade $grade, ?Exam $exam = null, bool $dryRun = false): ?array
    {
        $exam ??= $grade->exam;
        if (! $exam) {
            return null;
        }
        $exam->loadMissing('questions.options');
        if ($exam->questions->isEmpty()) {
            return null;
        }

        $rows = $grade->relationLoaded('answers') ? $grade->answers : $grade->answers()->get();
        $rows = $rows->keyBy('question_id');
        $draft = $this->draftFor($grade);

        if ($rows->isEmpty() && $draft === []) {
            return null;
        }

        $plan = [];      // [ExamAnswer|null existing, array attributes] per question that needs a write
        $earned = 0.0;
        $max = 0.0;
        $recovered = 0;

        // A submission stores a row for EVERY question it was given, so a question without a row (and
        // without a draft entry) was added after this student submitted: it is not part of their paper.
        $rowsComplete = $rows->isNotEmpty();

        foreach ($exam->questions as $question) {
            /** @var ExamAnswer|null $row */
            $row = $rows->get($question->id);
            $draftValue = $draft[$question->id] ?? null;

            if ($rowsComplete && ! $row && $draftValue === null) {
                continue;
            }
            $max += (float) $question->points;

            if ($question->type === 'essay') {
                if (! $row && is_string($draftValue) && trim($draftValue) !== '') {
                    $plan[] = [null, ['question_id' => $question->id, 'selected_option_id' => null, 'essay_answer' => $draftValue, 'is_correct' => null, 'points_earned' => null]];
                    $recovered++;
                }
                $earned += (float) ($row?->points_earned ?? 0);

                continue;
            }

            $optionIds = $question->options->pluck('id')->map(fn ($id) => (int) $id);
            $selected = $row?->selected_option_id !== null && $optionIds->contains((int) $row->selected_option_id)
                ? (int) $row->selected_option_id
                : null;

            if ($selected === null && is_numeric($draftValue) && $optionIds->contains((int) $draftValue)) {
                $selected = (int) $draftValue;
                $recovered++;
            }

            if ($selected === null) {
                // the student's choice is unknown (unanswered, or the option was deleted): keep what was there
                $earned += (float) ($row?->points_earned ?? 0);
                if (! $row) {
                    $plan[] = [null, ['question_id' => $question->id, 'selected_option_id' => null, 'essay_answer' => null, 'is_correct' => false, 'points_earned' => 0]];
                }

                continue;
            }

            $correct = $question->options->firstWhere('is_correct', true);
            $isCorrect = $correct && (int) $correct->id === $selected;
            $points = $isCorrect ? (float) $question->points : 0.0;
            $earned += $points;

            if (! $row
                || (int) $row->selected_option_id !== $selected
                || (bool) $row->is_correct !== $isCorrect
                || (float) $row->points_earned !== $points) {
                $plan[] = [$row, ['question_id' => $question->id, 'selected_option_id' => $selected, 'essay_answer' => null, 'is_correct' => $isCorrect, 'points_earned' => $points]];
            }
        }

        $old = (float) $grade->score;
        $oldMax = (float) $grade->max_score;
        $result = ['old' => $old, 'new' => $earned, 'old_max' => $oldMax, 'max' => $max, 'recovered' => $recovered];

        if ($dryRun || ($plan === [] && $old === $earned && $oldMax === $max)) {
            return $result;
        }

        DB::transaction(function () use ($grade, $exam, $plan, $old, $earned, $max) {
            foreach ($plan as [$row, $attributes]) {
                if ($row) {
                    $row->update($attributes);
                } else {
                    ExamAnswer::create($attributes + [
                        'grade_id' => $grade->id,
                        'exam_id' => $exam->id,
                        'student_id' => $grade->student_id,
                    ]);
                }
            }

            $updates = ['score' => $earned, 'max_score' => $max];
            if ($old !== $earned && ! str_contains((string) $grade->notes, self::NOTE)) {
                $updates['notes'] = filled($grade->notes) ? $grade->notes.' — '.self::NOTE : self::NOTE;
            }
            $grade->update($updates);
        });

        $grade->unsetRelation('answers');

        return $result;
    }

    /**
     * The autosaved answers of the sitting that produced this grade, as question_id => value.
     *
     * @return array<int, int|string|null>
     */
    private function draftFor(Grade $grade): array
    {
        $attempt = ExamAttempt::where('exam_id', $grade->exam_id)->where('student_id', $grade->student_id)->first();

        // an attempt that was never handed in belongs to a newer sitting (a retake), not to this grade
        if (! $attempt || ! $attempt->submitted_at || ! is_array($attempt->answers)) {
            return [];
        }

        return collect($attempt->answers)
            ->mapWithKeys(fn ($entry, $questionId) => [(int) $questionId => is_array($entry) ? ($entry['v'] ?? null) : $entry])
            ->all();
    }
}
