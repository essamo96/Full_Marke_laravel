<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Grade;
use App\Support\ArabicPdf;
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

        $pdf = ArabicPdf::loadView('student.exams.result-pdf', compact('grade'));

        $filename = 'exam-result-'.$grade->id.'.pdf';

        return $pdf->download($filename);
    }
}
