<?php

namespace Tests\Unit;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentContentGrant;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Services\StudentContentGrantService;
use App\Support\EducationalContentVisibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentContentGrantServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function makeScenario(): array
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id, 'name' => 'A']);
        $groupB = Group::factory()->create(['subject_id' => $subject->id, 'name' => 'B']);
        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);

        $unitA = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'name_ar' => 'وحدة أ فقط',
            'is_shared' => false,
        ]);
        $lessonA = EducationalLesson::factory()->create([
            'educational_unit_id' => $unitA->id,
            'is_shared' => false,
        ]);
        $resourceA = SubjectResource::factory()->forLesson($lessonA)->create([
            'title' => 'فيديو أ',
            'is_shared' => false,
        ]);

        EducationalContentVisibility::apply($unitA, false, [$groupA->id]);
        EducationalContentVisibility::apply($lessonA, false, [$groupA->id]);
        EducationalContentVisibility::apply($resourceA, false, [$groupA->id]);

        $unitShared = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'name_ar' => 'وحدة مشتركة',
            'is_shared' => true,
        ]);
        EducationalContentVisibility::apply($unitShared, true, []);

        $unitB = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'name_ar' => 'وحدة ب فقط',
            'is_shared' => false,
        ]);
        EducationalContentVisibility::apply($unitB, false, [$groupB->id]);

        $student = Student::factory()->create();
        Registration::factory()->create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'group_id' => $groupA->id,
            'status' => 'fully_paid',
        ]);

        return compact(
            'subject', 'groupA', 'groupB', 'stage',
            'unitA', 'lessonA', 'resourceA', 'unitShared', 'unitB', 'student'
        );
    }

    public function test_transfer_grants_old_group_only_content_not_shared_or_new_group(): void
    {
        $s = $this->makeScenario();
        $service = app(StudentContentGrantService::class);

        $count = $service->grantPreviousGroupContentOnTransfer(
            $s['student'],
            $s['subject']->id,
            $s['groupA']->id,
            $s['groupB']->id
        );

        $this->assertGreaterThanOrEqual(3, $count);

        $this->assertDatabaseHas('student_content_grants', [
            'student_id' => $s['student']->id,
            'grantable_type' => SubjectResource::class,
            'grantable_id' => $s['resourceA']->id,
            'source' => StudentContentGrant::SOURCE_TRANSFER,
            'from_group_id' => $s['groupA']->id,
        ]);

        $this->assertDatabaseHas('student_content_grants', [
            'student_id' => $s['student']->id,
            'grantable_type' => EducationalUnit::class,
            'grantable_id' => $s['unitA']->id,
        ]);

        $this->assertDatabaseMissing('student_content_grants', [
            'student_id' => $s['student']->id,
            'grantable_type' => EducationalUnit::class,
            'grantable_id' => $s['unitShared']->id,
        ]);

        $this->assertDatabaseMissing('student_content_grants', [
            'student_id' => $s['student']->id,
            'grantable_type' => EducationalUnit::class,
            'grantable_id' => $s['unitB']->id,
        ]);
    }

    public function test_transfer_is_noop_without_old_group_or_same_group(): void
    {
        $s = $this->makeScenario();
        $service = app(StudentContentGrantService::class);

        $this->assertSame(0, $service->grantPreviousGroupContentOnTransfer(
            $s['student'], $s['subject']->id, null, $s['groupB']->id
        ));

        $this->assertSame(0, $service->grantPreviousGroupContentOnTransfer(
            $s['student'], $s['subject']->id, $s['groupA']->id, $s['groupA']->id
        ));
    }

    public function test_transfer_grant_is_idempotent(): void
    {
        $s = $this->makeScenario();
        $service = app(StudentContentGrantService::class);

        $service->grantPreviousGroupContentOnTransfer(
            $s['student'], $s['subject']->id, $s['groupA']->id, $s['groupB']->id
        );
        $service->grantPreviousGroupContentOnTransfer(
            $s['student'], $s['subject']->id, $s['groupA']->id, $s['groupB']->id
        );

        $rows = StudentContentGrant::where('student_id', $s['student']->id)
            ->where('grantable_type', SubjectResource::class)
            ->where('grantable_id', $s['resourceA']->id)
            ->count();

        $this->assertSame(1, $rows);
    }

    public function test_granted_resource_is_visible_to_student_in_new_group(): void
    {
        $s = $this->makeScenario();
        app(StudentContentGrantService::class)->grantPreviousGroupContentOnTransfer(
            $s['student'], $s['subject']->id, $s['groupA']->id, $s['groupB']->id
        );

        $this->assertFalse(
            SubjectResource::whereKey($s['resourceA']->id)->forGroup($s['groupB']->id)->exists()
        );

        $this->assertTrue(
            $s['resourceA']->fresh()->isVisibleToStudent($s['student']->id, $s['groupB']->id)
        );

        $other = Student::factory()->create();
        $this->assertFalse(
            $s['resourceA']->fresh()->isVisibleToStudent($other->id, $s['groupB']->id)
        );
    }
}
