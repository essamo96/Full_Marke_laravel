<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Support\ExamPaper;
use App\Support\ExamReviewAccess;

class ResultsController extends Controller
{
    public function index()
    {
        $student = auth('student')->user();

        // All grades for this student remain visible after group transfer
        // (grades are keyed by student_id, not current registration group).
        $results = Grade::where('student_id', $student->id)
            ->with(['exam', 'group'])
            ->latest()
            ->paginate(10);

        return view('student.exams.results', compact('results'));
    }

    public function show(Grade $grade)
    {
        $student = auth('student')->user();
        abort_unless($grade->student_id === $student->id, 403);

        \App\Services\GradeAnswerRebuilder::ensure($grade);

        $grade->load(['exam', 'group', 'answers.question.options', 'answers.selectedOption', 'student']);

        $canReview = ExamReviewAccess::studentCanReviewAnswers($grade);

        return view('student.exams.review', compact('grade', 'canReview'));
    }

    public function downloadPdf(Grade $grade)
    {
        $student = auth('student')->user();
        abort_unless($grade->student_id === $student->id, 403);
        abort_unless(ExamReviewAccess::studentCanDownloadPdf($grade), 403, 'مراجعة الإجابات غير مفعّلة لهذا الامتحان.');

        \App\Services\GradeAnswerRebuilder::ensure($grade);

        $grade->load(['exam', 'group', 'answers.question.options', 'answers.selectedOption', 'student']);

        // Built with the same mPDF pipeline as the teacher's exam paper (native RTL), so the
        // student's PDF has the same fonts, spacing and letterhead instead of overlapping glyphs.
        try {
            $bytes = ExamPaper::resultPdfBytes($grade);
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('student.results.show', $grade)
                ->with('error', 'تعذر إنشاء ملف PDF حالياً، حاول مرة أخرى بعد قليل.');
        }

        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="exam-result-'.$grade->id.'.pdf"',
            'Content-Length' => (string) strlen($bytes),
        ]);
    }
}
