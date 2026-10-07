<?php

namespace Tests\Unit;

use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentContentGrant;
use App\Models\SubjectResource;
use App\Services\StudentContentGrantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Moving a student to another group must not take away what they could already watch.
 * Grants are resource-level: units and lessons are shown whenever one of their resources is.
 */
class StudentContentGrantServiceTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function service(): StudentContentGrantService
    {
        return app(StudentContentGrantService::class);
    }

    public function test_transfer_grants_what_the_old_group_saw_and_the_new_one_does_not(): void
    {
        $s = $this->scenario();
        $onlyA = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $shared = $this->makeResource($s, $s['lesson1']);
        $onlyB = $this->makeResource($s, $s['lesson2'], [$s['groupB']]);
        $student = $s['students']['A'];

        $count = $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);

        $this->assertGreaterThanOrEqual(1, $count);
        $this->assertDatabaseHas('student_content_grants', [
            'student_id' => $student->id,
            'grantable_type' => SubjectResource::class,
            'grantable_id' => $onlyA->id,
            'source' => StudentContentGrant::SOURCE_TRANSFER,
            'from_group_id' => $s['groupA']->id,
        ]);
        $this->assertDatabaseMissing('student_content_grants', ['student_id' => $student->id, 'grantable_id' => $shared->id]);
        $this->assertDatabaseMissing('student_content_grants', ['student_id' => $student->id, 'grantable_id' => $onlyB->id]);
    }

    public function test_after_the_move_the_student_sees_the_old_content_plus_the_new_groups(): void
    {
        $s = $this->scenario();
        $onlyA = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $onlyB = $this->makeResource($s, $s['lesson2'], [$s['groupB']]);
        $student = $s['students']['A'];

        $registration = Registration::where('student_id', $student->id)->where('subject_id', $s['subject']->id)->first();
        $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);
        $registration->update(['group_id' => $s['groupB']->id]);

        $this->assertSame([$onlyA->id, $onlyB->id], $this->visibleIds($s, $student));

        $other = Student::factory()->create();
        Registration::factory()->create(['student_id' => $other->id, 'subject_id' => $s['subject']->id, 'group_id' => $s['groupB']->id, 'status' => 'fully_paid']);
        $this->assertSame([$onlyB->id], $this->visibleIds($s, $other), 'a student who was never in group A does not get its content');
    }

    public function test_transfer_is_a_noop_without_an_old_group_or_for_the_same_group(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->assertSame(0, $this->service()->grantPreviousGroupContentOnTransfer($s['students']['A'], $s['subject']->id, null, $s['groupB']->id));
        $this->assertSame(0, $this->service()->grantPreviousGroupContentOnTransfer($s['students']['A'], $s['subject']->id, $s['groupA']->id, $s['groupA']->id));
    }

    public function test_the_transfer_grant_is_idempotent(): void
    {
        $s = $this->scenario();
        $onlyA = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $student = $s['students']['A'];

        $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);
        $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);

        $this->assertSame(1, StudentContentGrant::where('student_id', $student->id)->where('grantable_id', $onlyA->id)->count());
    }

    public function test_an_exclusion_is_never_turned_into_a_grant(): void
    {
        $s = $this->scenario();
        $hidden = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $student = $s['students']['A'];
        $this->exclude($hidden, $s['subject']->id, [$student->id]);

        $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);

        $this->assertDatabaseMissing('student_content_grants', ['student_id' => $student->id, 'grantable_id' => $hidden->id]);
    }

    public function test_a_switched_off_resource_is_not_granted_and_a_grant_does_not_resurrect_it(): void
    {
        $s = $this->scenario();
        $off = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['is_active' => false]);
        $student = $s['students']['A'];

        $this->service()->grantPreviousGroupContentOnTransfer($student, $s['subject']->id, $s['groupA']->id, $s['groupB']->id);
        $this->assertDatabaseMissing('student_content_grants', ['student_id' => $student->id, 'grantable_id' => $off->id]);

        StudentContentGrant::create([
            'student_id' => $student->id, 'subject_id' => $s['subject']->id,
            'grantable_type' => SubjectResource::class, 'grantable_id' => $off->id, 'source' => 'manual',
        ]);
        $this->assertSame([], $this->visibleIds($s, $student), 'the kill switch beats every grant');
    }
}
