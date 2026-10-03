<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\Grade;

class ExamReviewAccess
{
    /**
     * Student may see correct/incorrect answer details when the exam allows it.
     * Score is always visible on their own grade.
     */
    public static function studentCanReviewAnswers(Grade $grade): bool
    {
        $exam = $grade->exam;
        if (! $exam) {
            return false;
        }

        return (bool) $exam->allow_student_review;
    }

    public static function studentCanDownloadPdf(Grade $grade): bool
    {
        return self::studentCanReviewAnswers($grade);
    }
}
