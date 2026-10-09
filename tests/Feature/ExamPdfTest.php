<?php

namespace Tests\Feature;

use App\Models\ExamAnswer;
use App\Models\Grade;
use App\Support\ExamPaper;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsExamScenario;
use Tests\TestCase;

/**
 * The teacher's blank exam paper and the student's result sheet are built by the
 * same mPDF pipeline: same letterhead (with the academy's Arabic name), fonts and layout.
 */
class ExamPdfTest extends TestCase
{
    use BuildsExamScenario, DatabaseTransactions;

    private function scene(array $examOverrides = []): array
    {
        [$subject, $teacher, $a, $b] = $this->makeSubjectWithTwoGroups();
        $student = $this->makeStudent(['full_name_ar' => 'طالب تجريبي']);
        $this->enroll($student, $b);
        $exam = $this->makeExam($subject, [$a, $b], $examOverrides + ['allow_student_review' => true, 'title' => 'اختبار الكيمياء'], 2, true);

        return [$exam, $teacher, $student, $a, $b];
    }

    private function gradeFor($exam, $student, $group): Grade
    {
        $grade = Grade::create([
            'student_id' => $student->id, 'group_id' => $group->id, 'exam_id' => $exam->id,
            'exam_name' => $exam->title, 'score' => 1, 'max_score' => 4, 'time_taken_minutes' => 12,
        ]);

        $mcq = $exam->questions[0];
        ExamAnswer::create([
            'grade_id' => $grade->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $mcq->id,
            'selected_option_id' => $mcq->options->firstWhere('is_correct', false)->id, 'is_correct' => false, 'points_earned' => 0,
        ]);
        $mcq2 = $exam->questions[1];
        ExamAnswer::create([
            'grade_id' => $grade->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $mcq2->id,
            'selected_option_id' => $mcq2->options->firstWhere('is_correct', true)->id, 'is_correct' => true, 'points_earned' => 1,
        ]);
        ExamAnswer::create([
            'grade_id' => $grade->id, 'exam_id' => $exam->id, 'student_id' => $student->id, 'question_id' => $exam->questions[2]->id,
            'essay_answer' => "إجابة مقالية\nعلى سطرين <b>وبعض</b> الوسوم", 'is_correct' => null, 'points_earned' => null,
        ]);

        return $grade->fresh(['exam', 'group', 'answers.question.options', 'answers.selectedOption', 'student']);
    }

    public function test_teacher_paper_carries_the_academys_arabic_name_and_not_the_old_one(): void
    {
        [$exam] = $this->scene();
        ExamPaper::load($exam);

        $html = view('exams.paper-pdf', ['exam' => $exam, 'logo' => null])->render();

        $this->assertStringContainsString('أكاديمية العلامة الكاملة', $html);
        $this->assertStringNotContainsString('فول مارك', $html);
        $this->assertStringContainsString('مجموعة أ، مجموعة ب', $html, 'a multi-group exam lists all its groups');
    }

    public function test_student_result_sheet_uses_the_same_letterhead_and_no_old_name(): void
    {
        [$exam, , $student, , $b] = $this->scene();
        $grade = $this->gradeFor($exam, $student, $b);

        $html = view('student.exams.result-pdf', ['grade' => $grade, 'logo' => null])->render();

        $this->assertStringContainsString('أكاديمية العلامة الكاملة', $html);
        $this->assertStringNotContainsString('فول مارك', $html);
        $this->assertStringContainsString('طالب تجريبي', $html);
        $this->assertStringContainsString('١ من ٤', $html, 'marks read unambiguously in RTL: "١ من ٤", never "1 / 4"');
        $this->assertStringContainsString('إجابة مقالية', $html);
        $this->assertStringNotContainsString('<b>', $html, 'rich text is flattened, not echoed raw');
    }

    public function test_both_pdfs_are_complete_pdf_files(): void
    {
        [$exam, , $student, , $b] = $this->scene();
        $grade = $this->gradeFor($exam, $student, $b);

        $paper = ExamPaper::blankPdfBytes($exam);
        $result = ExamPaper::resultPdfBytes($grade);

        foreach ([$paper, $result] as $bytes) {
            $this->assertStringStartsWith('%PDF-', $bytes);
            $this->assertGreaterThan(5000, strlen($bytes));
            $this->assertStringContainsString('%%EOF', $bytes);
        }
    }

    public function test_student_downloads_their_result_pdf(): void
    {
        [$exam, , $student, , $b] = $this->scene();
        $grade = $this->gradeFor($exam, $student, $b);

        $response = $this->actingAs($student, 'student')->get(route('student.results.pdf', $grade));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringContainsString('attachment; filename="exam-result-'.$grade->id.'.pdf"', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_result_pdf_respects_review_permission_and_ownership(): void
    {
        [$exam, , $student, , $b] = $this->scene(['allow_student_review' => false]);
        $grade = $this->gradeFor($exam, $student, $b);

        $this->actingAs($student, 'student')->get(route('student.results.pdf', $grade))->assertForbidden();

        $exam->update(['allow_student_review' => true]);
        $stranger = $this->makeStudent();
        $this->actingAs($stranger, 'student')->get(route('student.results.pdf', $grade))->assertForbidden();
    }

    public function test_teacher_downloads_the_blank_paper_for_a_multi_group_exam(): void
    {
        [$exam, $teacher] = $this->scene();

        $response = $this->actingAs($teacher, 'teacher')->get(route('teacher.exams.blank-pdf', $exam));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }
}
