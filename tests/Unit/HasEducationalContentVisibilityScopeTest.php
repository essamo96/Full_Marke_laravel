<?php

namespace Tests\Unit;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Student;
use App\Models\StudentContentGrant;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\EducationalContentVisibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HasEducationalContentVisibilityScopeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_visible_to_student_includes_shared_pivot_and_personal_grant(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $shared = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'is_shared' => true,
            'name_ar' => 'مشتركة',
        ]);
        EducationalContentVisibility::apply($shared, true, []);

        $groupOnly = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'is_shared' => false,
            'name_ar' => 'مجموعة أ',
        ]);
        EducationalContentVisibility::apply($groupOnly, false, [$groupA->id]);

        $granted = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'is_shared' => false,
            'name_ar' => 'منح فردي',
        ]);
        EducationalContentVisibility::apply($granted, false, [$groupA->id]);
        StudentContentGrant::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'grantable_type' => EducationalUnit::class,
            'grantable_id' => $granted->id,
            'source' => StudentContentGrant::SOURCE_MANUAL,
            'from_group_id' => $groupA->id,
        ]);

        $foreign = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'is_shared' => false,
            'name_ar' => 'مجموعة ب فقط',
        ]);
        EducationalContentVisibility::apply($foreign, false, [$groupB->id]);

        // Student currently in group B: sees shared + personal grant, not A-only without grant, not... wait groupOnly is A-only without grant so NOT visible in B
        $visibleInB = EducationalUnit::query()
            ->where('educational_stage_id', $stage->id)
            ->visibleToStudent($student->id, $groupB->id)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertContains((int) $shared->id, $visibleInB);
        $this->assertContains((int) $granted->id, $visibleInB);
        $this->assertNotContains((int) $groupOnly->id, $visibleInB);
        $this->assertContains((int) $foreign->id, $visibleInB); // group B pivot

        // No group assignment: shared only (+ grants)
        $visibleNoGroup = EducationalUnit::query()
            ->where('educational_stage_id', $stage->id)
            ->visibleToStudent($student->id, null)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $this->assertContains((int) $shared->id, $visibleNoGroup);
        $this->assertContains((int) $granted->id, $visibleNoGroup);
        $this->assertNotContains((int) $foreign->id, $visibleNoGroup);
    }

    public function test_resource_visibility_matches_authorize_helper(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);
        $unit = EducationalUnit::factory()->create(['educational_stage_id' => $stage->id, 'is_shared' => false]);
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $unit->id, 'is_shared' => false]);
        $resource = SubjectResource::factory()->forLesson($lesson)->create(['is_shared' => false]);
        EducationalContentVisibility::apply($resource, false, [$groupA->id]);

        $student = Student::factory()->create();

        $this->assertTrue($resource->isVisibleToStudent($student->id, $groupA->id));
        $this->assertFalse($resource->isVisibleToStudent($student->id, $groupB->id));

        StudentContentGrant::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'grantable_type' => SubjectResource::class,
            'grantable_id' => $resource->id,
            'source' => StudentContentGrant::SOURCE_TRANSFER,
            'from_group_id' => $groupA->id,
        ]);

        $this->assertTrue($resource->fresh()->isVisibleToStudent($student->id, $groupB->id));
    }
}
