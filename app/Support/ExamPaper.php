<?php

namespace App\Support;

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamGuestAnswer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Mpdf\Mpdf;

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

    /**
     * Rich-text question/option HTML -> safe markup for the printed paper.
     * Keeps line breaks and sub/superscripts (chemistry / maths), drops every
     * other tag, and decodes entities (&nbsp; etc.) so they never leak as
     * literal text into the PDF.
     */
    public static function plain(?string $html): string
    {
        $t = (string) $html;
        $t = preg_replace('#<\s*br\s*/?>#i', "\n", $t);
        $t = preg_replace('#</\s*(p|div|li|tr|h[1-6])\s*>#i', "\n", $t);
        $t = preg_replace('#<\s*(/?)\s*(sub|sup)\b[^>]*>#i', '[[$1$2]]', $t);
        $t = strip_tags($t);
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $t = str_replace("\u{00A0}", ' ', $t);
        $t = preg_replace("/[ \t\x{200B}]+/u", ' ', $t);
        $t = preg_replace("/\s*\n\s*/", "\n", trim($t));
        $t = e($t);
        $t = preg_replace('#\[\[(/?)(sub|sup)\]\]#', '<$1$2>', $t);

        return nl2br($t, false);
    }

    /** Empty (answer-key-free) exam paper as PDF bytes (mPDF: native RTL + Arabic shaping). */
    public static function blankPdfBytes(Exam $exam): string
    {
        self::load($exam);

        $logo = public_path('site/images/logo_v2_blue.png');
        $html = view('exams.paper-pdf', [
            'exam' => $exam,
            'logo' => is_file($logo) ? $logo : null,
        ])->render();

        $tmp = storage_path('app/mpdf');
        File::ensureDirectoryExists($tmp);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'orientation' => 'P',
            'tempDir' => $tmp,
            'default_font' => 'dejavusans',
            'margin_left' => 16,
            'margin_right' => 16,
            'margin_top' => 14,
            'margin_bottom' => 18,
            'margin_footer' => 8,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'useSubstitutions' => true,
        ]);
        $mpdf->SetDirectionality('rtl');
        $mpdf->SetTitle($exam->title);
        $mpdf->SetAuthor(config('app.name', 'Full Mark Academy'));
        $mpdf->WriteHTML($html);

        return $mpdf->Output('', 'S');
    }

    /**
     * Download response, or null after logging when the PDF could not be built.
     * $error receives a short, user-safe reason so the failure can be diagnosed
     * from the page instead of a bare HTTP 500.
     */
    public static function download(Exam $exam, ?string &$error = null)
    {
        try {
            $bytes = self::blankPdfBytes($exam);

            return response($bytes, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.self::pdfFilename($exam).'"',
                'Content-Length' => (string) strlen($bytes),
            ]);
        } catch (\Throwable $e) {
            report($e);
            $error = 'تعذر إنشاء ملف PDF ('.class_basename($e).': '.Str::limit($e->getMessage(), 140).')';

            return null;
        }
    }

    public static function pdfFilename(Exam $exam): string
    {
        return 'exam-'.$exam->id.'-blank.pdf';
    }
}
