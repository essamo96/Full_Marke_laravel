<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Support\ExamReviewAccess;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExamReviewAccessTest extends TestCase
{
    use DatabaseTransactions;

    private function makeGrade(bool $allowReview): Grade
    {
        $subject = Subject::factory()->create();
        $group = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $exam = Exam::create([
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'title' => 'امتحان مراجعة',
            'status' => 'published',
            'audience' => 'students',
            'allow_student_review' => $allowReview,
            'start_time' => now()->subHour(),
            'end_time' => now()->addDay(),
            'duration_minutes' => 30,
        ]);

        return Grade::create([
            'student_id' => $student->id,
            'group_id' => $group->id,
            'exam_id' => $exam->id,
            'exam_name' => $exam->title,
            'score' => 8,
            'max_score' => 10,
        ]);
    }

    public function test_student_can_review_when_exam_allows_it(): void
    {
        $grade = $this->makeGrade(true);
        $this->assertTrue(ExamReviewAccess::studentCanReviewAnswers($grade));
        $this->assertTrue(ExamReviewAccess::studentCanDownloadPdf($grade));
    }

    public function test_student_cannot_review_when_exam_disallows_it(): void
    {
        $grade = $this->makeGrade(false);
        $this->assertFalse(ExamReviewAccess::studentCanReviewAnswers($grade));
        $this->assertFalse(ExamReviewAccess::studentCanDownloadPdf($grade));
    }

    public function test_grade_survives_as_reference_after_group_change_on_registration(): void
    {
        $grade = $this->makeGrade(true);
        $studentId = $grade->student_id;

        // Simulate transfer: registration group changes, grade remains for student
        $otherGroup = Group::factory()->create(['subject_id' => $grade->exam->subject_id]);
        \App\Models\Registration::factory()->create([
            'student_id' => $studentId,
            'subject_id' => $grade->exam->subject_id,
            'group_id' => $otherGroup->id,
            'status' => 'fully_paid',
        ]);

        $stillVisible = Grade::where('student_id', $studentId)->whereKey($grade->id)->exists();
        $this->assertTrue($stillVisible);
        $this->assertTrue(ExamReviewAccess::studentCanReviewAnswers($grade->fresh()->load('exam')));
    }
}
