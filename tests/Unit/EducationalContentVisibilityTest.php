<?php

namespace Tests\Unit;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\EducationalContentVisibility;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EducationalContentVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    private function makeTree(): array
    {
        $subject = Subject::factory()->create();
        $groupA = Group::factory()->create(['subject_id' => $subject->id, 'name' => 'Group A']);
        $groupB = Group::factory()->create(['subject_id' => $subject->id, 'name' => 'Group B']);
        $stage = EducationalStage::factory()->create(['subject_id' => $subject->id]);
        $unit = EducationalUnit::factory()->create([
            'educational_stage_id' => $stage->id,
            'is_shared' => false,
        ]);
        $lesson = EducationalLesson::factory()->create([
            'educational_unit_id' => $unit->id,
            'is_shared' => false,
        ]);
        $resource = SubjectResource::factory()->forLesson($lesson)->create(['is_shared' => false]);

        EducationalContentVisibility::apply($unit, false, [$groupA->id]);
        EducationalContentVisibility::apply($lesson, false, [$groupA->id]);
        EducationalContentVisibility::apply($resource, false, [$groupA->id]);

        return compact('subject', 'groupA', 'groupB', 'stage', 'unit', 'lesson', 'resource');
    }

    public function test_apply_shared_clears_group_pivots(): void
    {
        ['unit' => $unit, 'groupA' => $groupA] = $this->makeTree();

        $this->assertTrue($unit->groups()->where('groups.id', $groupA->id)->exists());

        EducationalContentVisibility::apply($unit, true, [$groupA->id]);

        $unit->refresh();
        $this->assertTrue((bool) $unit->is_shared);
        $this->assertSame(0, $unit->groups()->count());
    }

    public function test_apply_non_shared_syncs_exact_groups(): void
    {
        ['unit' => $unit, 'groupA' => $groupA, 'groupB' => $groupB] = $this->makeTree();

        EducationalContentVisibility::apply($unit, false, [$groupB->id]);

        $ids = $unit->groups()->pluck('groups.id')->all();
        $this->assertSame([(int) $groupB->id], array_map('intval', $ids));
        $this->assertFalse((bool) $unit->fresh()->is_shared);
    }

    public function test_share_with_groups_attaches_without_removing_existing(): void
    {
        ['resource' => $resource, 'groupA' => $groupA, 'groupB' => $groupB] = $this->makeTree();

        EducationalContentVisibility::shareWithGroups($resource, [$groupB->id], withAncestors: false, withDescendants: false);

        $ids = $resource->fresh()->groups()->pluck('groups.id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $this->assertSame([(int) $groupA->id, (int) $groupB->id], $ids);
    }

    public function test_share_resource_cascades_ancestors_so_tree_is_visible(): void
    {
        ['resource' => $resource, 'lesson' => $lesson, 'unit' => $unit, 'groupB' => $groupB] = $this->makeTree();

        EducationalContentVisibility::shareWithGroups($resource->fresh()->load('lesson.unit'), [$groupB->id]);

        $this->assertTrue($resource->fresh()->groups()->where('groups.id', $groupB->id)->exists());
        $this->assertTrue($lesson->fresh()->groups()->where('groups.id', $groupB->id)->exists());
        $this->assertTrue($unit->fresh()->groups()->where('groups.id', $groupB->id)->exists());
    }

    public function test_share_unit_cascades_to_lessons_and_resources(): void
    {
        ['unit' => $unit, 'lesson' => $lesson, 'resource' => $resource, 'groupB' => $groupB] = $this->makeTree();

        EducationalContentVisibility::shareWithGroups($unit->fresh(), [$groupB->id]);

        $this->assertTrue($unit->fresh()->groups()->where('groups.id', $groupB->id)->exists());
        $this->assertTrue($lesson->fresh()->groups()->where('groups.id', $groupB->id)->exists());
        $this->assertTrue($resource->fresh()->groups()->where('groups.id', $groupB->id)->exists());
    }

    public function test_share_is_noop_on_already_shared_row_but_still_cascades_children(): void
    {
        ['unit' => $unit, 'lesson' => $lesson, 'groupB' => $groupB] = $this->makeTree();
        EducationalContentVisibility::apply($unit, true, []);

        EducationalContentVisibility::shareWithGroups($unit->fresh(), [$groupB->id]);

        $this->assertTrue((bool) $unit->fresh()->is_shared);
        $this->assertSame(0, $unit->groups()->count());
        $this->assertTrue($lesson->fresh()->groups()->where('groups.id', $groupB->id)->exists());
    }

    public function test_destroy_for_group_detaches_or_deletes(): void
    {
        ['resource' => $resource, 'groupA' => $groupA, 'groupB' => $groupB] = $this->makeTree();
        EducationalContentVisibility::shareWithGroups($resource, [$groupB->id], false, false);

        $result = EducationalContentVisibility::destroyForGroup($resource->fresh(), $groupA->id);
        $this->assertSame('detached', $result);
        $this->assertFalse($resource->fresh()->groups()->where('groups.id', $groupA->id)->exists());

        $result2 = EducationalContentVisibility::destroyForGroup($resource->fresh(), $groupB->id);
        $this->assertContains($result2, ['deleted', 'soft_deleted']);
    }

    public function test_destroy_blocks_shared_content_without_confirm_path(): void
    {
        ['resource' => $resource] = $this->makeTree();
        EducationalContentVisibility::apply($resource, true, []);

        $result = EducationalContentVisibility::destroyForGroup($resource->fresh(), 1);
        $this->assertSame('blocked_shared', $result);
        $this->assertFalse($resource->fresh()->trashed());
    }
}
