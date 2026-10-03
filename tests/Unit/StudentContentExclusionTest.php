<?php

namespace Tests\Unit;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\EducationalContentVisibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StudentContentExclusionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_exclusion_hides_shared_resource_from_specific_student(): void
    {
        $subject = Subject::factory()->create();
        $group = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();
        $other = Student::factory()->create();

        $resource = SubjectResource::factory()->create([
            'subject_id' => $subject->id,
            'educational_lesson_id' => null,
            'is_shared' => true,
            'is_active' => true,
        ]);
        EducationalContentVisibility::apply($resource, true, []);
        EducationalContentVisibility::syncExclusions($resource, $subject->id, [$student->id]);

        $this->assertFalse($resource->fresh()->isVisibleToStudent($student->id, $group->id));
        $this->assertTrue($resource->fresh()->isVisibleToStudent($other->id, $group->id));
        $this->assertTrue($resource->fresh()->isExcludedForStudent($student->id));
    }

    public function test_exclusion_overrides_personal_grant(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $resource = SubjectResource::factory()->create([
            'subject_id' => $subject->id,
            'educational_lesson_id' => null,
            'is_shared' => false,
            'is_active' => true,
        ]);
        EducationalContentVisibility::apply($resource, false, [$groupA->id]);

        // Grant for new group then exclude — exclusion wins
        \App\Models\StudentContentGrant::create([
            'student_id' => $student->id,
            'subject_id' => $subject->id,
            'grantable_type' => SubjectResource::class,
            'grantable_id' => $resource->id,
            'source' => 'manual',
            'from_group_id' => $groupA->id,
        ]);
        EducationalContentVisibility::syncExclusions($resource, $subject->id, [$student->id]);

        $this->assertFalse($resource->fresh()->isVisibleToStudent($student->id, $groupB->id));
    }

    public function test_sync_exclusions_replaces_previous_set(): void
    {
        $subject = Subject::factory()->create();
        $a = Student::factory()->create();
        $b = Student::factory()->create();
        $resource = SubjectResource::factory()->create([
            'subject_id' => $subject->id,
            'educational_lesson_id' => null,
            'is_shared' => true,
        ]);
        EducationalContentVisibility::apply($resource, true, []);

        EducationalContentVisibility::syncExclusions($resource, $subject->id, [$a->id]);
        $this->assertSame(1, $resource->contentExclusions()->count());

        EducationalContentVisibility::syncExclusions($resource, $subject->id, [$b->id]);
        $ids = $resource->contentExclusions()->pluck('student_id')->map(fn ($id) => (int) $id)->all();
        $this->assertSame([(int) $b->id], $ids);
    }

    public function test_general_resource_without_lesson_respects_group_scope(): void
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id]);
        $groupB = Group::factory()->create(['subject_id' => $subject->id]);
        $student = Student::factory()->create();

        $resource = SubjectResource::factory()->create([
            'subject_id' => $subject->id,
            'educational_lesson_id' => null,
            'is_shared' => false,
            'is_active' => true,
            'title' => 'مرفق عام',
        ]);
        EducationalContentVisibility::apply($resource, false, [$groupA->id]);

        $this->assertTrue($resource->isVisibleToStudent($student->id, $groupA->id));
        $this->assertFalse($resource->isVisibleToStudent($student->id, $groupB->id));
        $this->assertNull($resource->educational_lesson_id);
    }

    public function test_lesson_resource_exclusion_does_not_hide_siblings(): void
    {
        $subject = Subject::factory()->create();
        $group = Group::factory()->create(['subject_id' => $subject->id]);
        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);
        $unit = EducationalUnit::factory()->create(['educational_stage_id' => $stage->id, 'is_shared' => true]);
        EducationalContentVisibility::apply($unit, true, []);
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $unit->id, 'is_shared' => true]);
        EducationalContentVisibility::apply($lesson, true, []);

        $hidden = SubjectResource::factory()->forLesson($lesson)->create(['is_shared' => true, 'title' => 'مخفي']);
        $visible = SubjectResource::factory()->forLesson($lesson)->create(['is_shared' => true, 'title' => 'ظاهر']);
        EducationalContentVisibility::apply($hidden, true, []);
        EducationalContentVisibility::apply($visible, true, []);

        $student = Student::factory()->create();
        EducationalContentVisibility::syncExclusions($hidden, $subject->id, [$student->id]);

        $this->assertFalse($hidden->fresh()->isVisibleToStudent($student->id, $group->id));
        $this->assertTrue($visible->fresh()->isVisibleToStudent($student->id, $group->id));
    }
}
