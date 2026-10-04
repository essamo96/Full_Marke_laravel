<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamGuestAnswer;
use Barryvdh\DomPDF\PDF as DomPdf;

class ExamPaper
{
    /** Loads everything the preview / blank-paper views need. */
    public static function load(Exam $exam): Exam
    {
        return $exam->load(['subject', 'group', 'questions.options']);
    }

    /**
     * How many times each option was picked (students + guests), keyed
     * question_id => option_id => count. Works for exams of any age.
     */
    public static function optionStats(Exam $exam): array
    {
        $stats = [];

        foreach ([ExamAnswer::class, ExamGuestAnswer::class] as $model) {
            $rows = $model::where('exam_id', $exam->id)
                ->whereNotNull('selected_option_id')
                ->selectRaw('question_id, selected_option_id, count(*) as total')
                ->groupBy('question_id', 'selected_option_id')
                ->get();

            foreach ($rows as $row) {
                $stats[$row->question_id][$row->selected_option_id] =
                    ($stats[$row->question_id][$row->selected_option_id] ?? 0) + (int) $row->total;
            }
        }

        return $stats;
    }

    /** Empty (answer-key-free) exam paper as a PDF. */
    public static function blankPdf(Exam $exam): DomPdf
    {
        self::load($exam);

        return ArabicPdf::loadView('exams.paper-pdf', compact('exam'));
    }

    public static function pdfFilename(Exam $exam): string
    {
        return 'exam-'.$exam->id.'-blank.pdf';
    }
}
