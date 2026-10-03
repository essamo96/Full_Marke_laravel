<?php

namespace Tests\Unit;

use App\Models\Exam;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentExamGrant;
use App\Models\Subject;
use App\Services\StudentContentGrantService;
use App\Services\StudentExamGrantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentExamGrantServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeExam(Subject $subject, Group $group, array $overrides = []): Exam
    {
        return Exam::create(array_merge([
            'subject_id' => $subject->id,
            'group_id' => $group->id,
            'title' => 'امتحان تجريبي',
            'description' => null,
            'start_time' => now()->subHour(),
            'end_time' => now()->addDay(),
            'duration_minutes' => 60,
            'status' => 'published',
            'excluded_student_ids' => null,
            'audience' => 'students',
        ], $overrides));
    }

    public function test_transfer_grants_old_group_published_exams(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $examA = $this->makeExam($subject, $groupA, ['title' => 'امتحان أ']);
        $draft = $this->makeExam($subject, $groupA, ['title' => 'مسودة', 'status' => 'draft']);
        $examB = $this->makeExam($subject, $groupB, ['title' => 'امتحان ب']);

        $service = app(StudentExamGrantService::class);
        $count = $service->grantPreviousGroupExamsOnTransfer(
            $student,
            $subject->id,
            $groupA->id,
            $groupB->id
        );

        $this->assertSame(1, $count);
        $this->assertDatabaseHas('student_exam_grants', [
            'student_id' => $student->id,
            'exam_id' => $examA->id,
            'from_group_id' => $groupA->id,
            'source' => StudentExamGrant::SOURCE_TRANSFER,
        ]);
        $this->assertDatabaseMissing('student_exam_grants', [
            'student_id' => $student->id,
            'exam_id' => $draft->id,
        ]);
        $this->assertDatabaseMissing('student_exam_grants', [
            'student_id' => $student->id,
            'exam_id' => $examB->id,
        ]);
    }

    public function test_excluded_student_is_not_granted_exam_on_transfer(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $exam = $this->makeExam($subject, $groupA, [
            'excluded_student_ids' => [$student->id],
        ]);

        $count = app(StudentExamGrantService::class)->grantPreviousGroupExamsOnTransfer(
            $student,
            $subject->id,
            $groupA->id,
            $groupB->id
        );

        $this->assertSame(0, $count);
        $this->assertDatabaseMissing('student_exam_grants', [
            'student_id' => $student->id,
            'exam_id' => $exam->id,
        ]);
    }

    public function test_student_can_access_granted_exam_in_new_group_context(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();
        $exam = $this->makeExam($subject, $groupA);

        $service = app(StudentExamGrantService::class);
        $this->assertFalse($service->studentCanAccessExam($exam, $student->id, [$groupB->id]));

        $service->grantPreviousGroupExamsOnTransfer($student, $subject->id, $groupA->id, $groupB->id);

        $this->assertTrue($service->studentCanAccessExam($exam->fresh(), $student->id, [$groupB->id]));
    }

    public function test_content_transfer_also_grants_exams(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();
        $exam = $this->makeExam($subject, $groupA, ['title' => 'مع المحتوى']);

        app(StudentContentGrantService::class)->grantPreviousGroupContentOnTransfer(
            $student,
            $subject->id,
            $groupA->id,
            $groupB->id
        );

        $this->assertDatabaseHas('student_exam_grants', [
            'student_id' => $student->id,
            'exam_id' => $exam->id,
        ]);
    }

    public function test_grant_is_idempotent(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();
        $this->makeExam($subject, $groupA);

        $service = app(StudentExamGrantService::class);
        $service->grantPreviousGroupExamsOnTransfer($student, $subject->id, $groupA->id, $groupB->id);
        $service->grantPreviousGroupExamsOnTransfer($student, $subject->id, $groupA->id, $groupB->id);

        $this->assertSame(1, StudentExamGrant::where('student_id', $student->id)->count());
    }
}
