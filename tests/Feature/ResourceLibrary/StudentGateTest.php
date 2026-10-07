<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\ResourceGroupLink;
use App\Models\ResourcePlacement;
use App\Models\StudentContentGrant;
use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ContentManager;
use App\Services\ResourceLibrary\VisibilityManager;
use App\Services\StudentContentGate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * The student-side rules: kill switch, audience (shared / linked / paused), exclusions at every
 * level of the tree, placements, and the fact that a listing and a direct URL agree.
 */
class StudentGateTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function gate(): StudentContentGate
    {
        return app(StudentContentGate::class);
    }

    public function test_shared_resource_is_visible_to_every_group_and_linked_one_only_to_its_group(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']);
        $onlyA = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->assertSame([$shared->id, $onlyA->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['C']));
    }

    public function test_kill_switch_hides_from_every_student_without_touching_anything_else(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $resource->update(['is_active' => false]);

        foreach (['A', 'B', 'C'] as $key) {
            $this->assertSame([], $this->visibleIds($s, $s['students'][$key]));
        }

        $this->assertSame(2, $resource->groups()->count(), 'the links must survive the kill switch');
        $this->assertSame(1, $resource->placements()->count());
    }

    public function test_paused_group_overrides_sharing_for_that_group_only(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']);
        ResourceGroupLink::create(['subject_resource_id' => $shared->id, 'group_id' => $s['groupA']->id, 'is_active' => false]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_paused_link_hides_an_explicit_grant(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        ResourceGroupLink::where('subject_resource_id', $resource->id)->where('group_id', $s['groupA']->id)->update(['is_active' => false]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_exclusion_on_a_resource_hides_only_that_resource_for_only_that_student(): void
    {
        $s = $this->scenario();
        $hidden = $this->makeResource($s, $s['lesson1']);
        $sibling = $this->makeResource($s, $s['lesson1']);
        $student = $s['students']['A'];

        $this->exclude($hidden, $s["subject"]->id, [$student->id]);

        $this->assertSame([$sibling->id], $this->visibleIds($s, $student));
        $this->assertSame([$hidden->id, $sibling->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_excluding_a_student_from_a_unit_hides_everything_inside_it(): void
    {
        $s = $this->scenario();
        $inLesson1 = $this->makeResource($s, $s['lesson1']);
        $inLesson2 = $this->makeResource($s, $s['lesson2']);
        $student = $s['students']['A'];

        $this->exclude($s['unit'], $s["subject"]->id, [$student->id]);

        $this->assertSame([], $this->visibleIds($s, $student));
        $this->assertSame([], $this->gate()->tree($student->id, $s['subject'])['units']->all());
        $this->assertSame([$inLesson1->id, $inLesson2->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_excluding_a_student_from_a_lesson_hides_only_that_lesson(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1']);
        $b = $this->makeResource($s, $s['lesson2']);
        $student = $s['students']['A'];

        $this->exclude($s['lesson1'], $s["subject"]->id, [$student->id]);

        $this->assertSame([$b->id], $this->visibleIds($s, $student));
    }

    public function test_direct_access_follows_the_same_rules_as_the_listing(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1']);
        $student = $s['students']['A'];

        $this->assertTrue($this->gate()->canAccess($resource, $student->id));

        // excluded from the UNIT, not from the video: the old code still let the URL through
        $this->exclude($s['unit'], $s["subject"]->id, [$student->id]);

        $this->assertFalse($this->gate()->canAccess($resource->fresh(), $student->id));
        $this->assertTrue($this->gate()->canAccess($resource->fresh(), $s['students']['B']->id));
    }

    public function test_inactive_unit_or_lesson_hides_its_resources(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1']);

        $s['lesson1']->update(['is_active' => false]);
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));

        $s['lesson1']->update(['is_active' => true]);
        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['A']));

        $s['unit']->update(['is_active' => false]);
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_a_resource_with_several_placements_stays_visible_through_the_open_one(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1']);
        ResourcePlacement::create(['subject_resource_id' => $resource->id, 'educational_lesson_id' => $s['lesson2']->id, 'sort_order' => 1]);
        $student = $s['students']['A'];

        $s['lesson1']->update(['is_active' => false]);
        $this->assertSame([$resource->id], $this->visibleIds($s, $student), 'still open through lesson 2');

        $tree = $this->gate()->tree($student->id, $s['subject']);
        $lessons = $tree['units']->first()->lessons;
        $this->assertSame([$s['lesson2']->id], $lessons->pluck('id')->all());

        $s['lesson2']->update(['is_active' => false]);
        $this->assertSame([], $this->visibleIds($s, $student));
    }

    public function test_an_unplaced_resource_is_visible_to_nobody(): void
    {
        $s = $this->scenario();
        $resource = SubjectResource::factory()->unplaced()->create(['subject_id' => $s['subject']->id, 'is_shared' => true]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertFalse($this->gate()->canAccess($resource, $s['students']['A']->id));
    }

    public function test_a_resource_without_audience_is_a_draft_nobody_sees(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [], ['is_shared' => false]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_a_deleted_lesson_hides_its_resources_instead_of_turning_them_into_general_ones(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1']);

        $s['lesson1']->delete();

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertNotNull(ResourcePlacement::where('subject_resource_id', $resource->id)->first(), 'the placement must stay (the lesson can be restored)');
    }

    public function test_general_resources_and_unit_level_resources_are_listed(): void
    {
        $s = $this->scenario();
        $general = $this->makeResource($s, null);
        $direct = SubjectResource::factory()->forUnit($s['unit'])->create(['subject_id' => $s['subject']->id, 'is_shared' => true]);

        $tree = $this->gate()->tree($s['students']['A']->id, $s['subject']);

        $this->assertSame([$general->id], $tree['general']->pluck('id')->all());
        $this->assertSame([$direct->id], $tree['units']->first()->directResources->pluck('id')->all());
    }

    public function test_the_tree_and_the_query_always_agree(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $b = $this->makeResource($s, $s['lesson2']);
        $c = $this->makeResource($s, null, [$s['groupB']]);
        $d = $this->makeResource($s, $s['lesson1']);
        $d->update(['is_active' => false]);
        $this->exclude($b, $s["subject"]->id, [$s['students']['B']->id]);

        foreach ($s['students'] as $student) {
            $tree = $this->gate()->tree($student->id, $s['subject']);
            $fromTree = $tree['general']->pluck('id')
                ->merge($tree['units']->flatMap(fn ($u) => $u->directResources->pluck('id')->merge($u->lessons->flatMap(fn ($l) => $l->resources->pluck('id')))))
                ->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

            $this->assertSame($this->visibleIds($s, $student), $fromTree, "student {$student->id}");
        }
    }

    public function test_personal_grant_keeps_content_after_a_group_transfer_but_an_exclusion_still_wins(): void
    {
        $s = $this->scenario();
        $onlyA = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $studentB = $s['students']['B'];

        $this->assertSame([], $this->visibleIds($s, $studentB));

        StudentContentGrant::create([
            'student_id' => $studentB->id, 'subject_id' => $s['subject']->id,
            'grantable_type' => SubjectResource::class, 'grantable_id' => $onlyA->id, 'source' => 'manual',
        ]);
        $this->assertSame([$onlyA->id], $this->visibleIds($s, $studentB));

        $this->exclude($onlyA, $s["subject"]->id, [$studentB->id]);
        $this->assertSame([], $this->visibleIds($s, $studentB));
    }

    public function test_a_student_not_enrolled_in_the_subject_gets_nothing(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1']);
        $stranger = \App\Models\Student::factory()->create();

        $this->assertFalse($this->gate()->canAccess($resource, $stranger->id));
    }

    public function test_the_audience_lives_on_the_resource_and_sharing_a_lesson_is_what_reaches_its_content(): void
    {
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id]);
        $inside = $this->makeResource($s, $lesson, [$s['groupA']]);
        $studentB = $s['students']['B'];

        // declaring a group on the lesson alone does not reach what is already inside
        $lesson->groups()->syncWithoutDetaching([$s['groupB']->id]);
        $this->assertSame([], $this->visibleIds($s, $studentB));
        $lesson->groups()->detach($s['groupB']->id);

        // "show everything to group B": the existing resources get the group, and the lesson remembers it
        app(VisibilityManager::class)->attachGroupsToContainer($s['actorTeacher'], $lesson, [$s['groupB']->id]);
        $this->assertSame([$inside->id], $this->visibleIds($s, $studentB));
        $this->assertContains($s['groupB']->id, $lesson->groups()->pluck('groups.id')->all());

        // so a resource added later "by place" (inherit) starts with the same audience
        $later = app(ContentManager::class)->createResource(
            $s['actorTeacher'], $s['subject'],
            ['title' => 'لاحق', 'type' => 'link', 'url' => 'https://youtu.be/abc123def45'],
            [$lesson], ['mode' => 'inherit'],
        )->data['resource'];

        $this->assertSame([$inside->id, $later->id], $this->visibleIds($s, $studentB));
        $this->assertSame([$inside->id, $later->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([], $this->visibleIds($s, $s['students']['C']), 'the colleague\'s group never got it');
    }
}
