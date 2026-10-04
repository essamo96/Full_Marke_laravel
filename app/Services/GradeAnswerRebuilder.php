<?php

namespace App\Services;

use App\Models\ExamAnswer;
use App\Models\Grade;

/**
 * Restores the per-question answers of a grade whose exam_answers rows are
 * missing (grades stored before the answers table existed, or answers wiped
 * when an exam's questions were edited).
 *
 * Only a FULL-MARK grade on an auto-graded exam can be rebuilt exactly: every
 * question must then have been answered with its correct option. Anything
 * else (partial scores, essay questions) cannot be inferred and is left alone.
 */
class GradeAnswerRebuilder
{
    public const NOTE = 'تم استرجاع تفاصيل الإجابات تلقائياً (علامة كاملة: كل الإجابات صحيحة).';

    public static function canRebuild(Grade $grade): bool
    {
        if (! $grade->exam_id || $grade->answers()->exists() || ! $grade->exam) {
            return false;
        }

        $questions = $grade->exam->questions()->with('options')->get();
        if ($questions->isEmpty()) {
            return false;
        }

        if ((float) $grade->score <= 0 || (float) $grade->score !== (float) $grade->max_score) {
            return false;
        }

        if ((float) $questions->sum('points') !== (float) $grade->max_score) {
            return false;
        }

        return $questions->every(fn ($q) => in_array($q->type, ['multiple_choice', 'true_false'], true)
            && $q->options->where('is_correct', true)->count() === 1);
    }

    /** Rebuilds when possible; returns true if answers were created. */
    public static function ensure(Grade $grade): bool
    {
        if (! self::canRebuild($grade)) {
            return false;
        }

        foreach ($grade->exam->questions()->with('options')->get() as $q) {
            ExamAnswer::create([
                'grade_id' => $grade->id,
                'exam_id' => $grade->exam_id,
                'student_id' => $grade->student_id,
                'question_id' => $q->id,
                'selected_option_id' => $q->options->firstWhere('is_correct', true)->id,
                'is_correct' => true,
                'points_earned' => $q->points,
            ]);
        }

        if (! str_contains((string) $grade->notes, self::NOTE)) {
            $grade->update(['notes' => trim($grade->notes.' '.self::NOTE)]);
        }

        $grade->unsetRelation('answers');

        return true;
    }
}
