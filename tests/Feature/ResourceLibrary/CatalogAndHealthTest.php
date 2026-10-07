<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\ResourcePlacement;
use App\Models\SubjectResource;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryHealth;
use App\Services\ResourceLibrary\LibraryJournal;
use App\Services\ResourceLibrary\ResourceCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * The admin and the teacher read the SAME tree, and the health scan explains what is wrong.
 */
class CatalogAndHealthTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function catalog(): ResourceCatalog
    {
        return app(ResourceCatalog::class);
    }

    private function health(): LibraryHealth
    {
        return app(LibraryHealth::class);
    }

    /** flat list of every resource id shown anywhere in a manage tree */
    private function idsIn(array $tree): array
    {
        $ids = collect($tree['general'])->pluck('id')->merge(collect($tree['unplaced'])->pluck('id'));
        foreach ($tree['units'] as $unit) {
            $ids = $ids->merge(collect($unit['resources'])->pluck('id'));
            foreach ($unit['lessons'] as $lesson) {
                $ids = $ids->merge(collect($lesson['resources'])->pluck('id'));
            }
        }

        return $ids->unique()->sort()->values()->all();
    }

    // -------------------------------------------------------------- one tree for both

    public function test_admin_and_teacher_see_exactly_the_same_videos_when_the_teacher_owns_every_group(): void
    {
        $s = $this->scenario();
        // make the teacher own every group of the subject
        $s['groupC']->update(['teacher_id' => $s['teacher']->id]);
        $actorTeacher = \App\Services\ResourceLibrary\LibraryActor::teacher($s['teacher']->fresh());

        $this->makeResource($s, $s['lesson1']);
        $this->makeResource($s, $s['lesson2'], [$s['groupA']]);
        $this->makeResource($s, null);                                   // general
        SubjectResource::factory()->forUnit($s['unit'])->create(['subject_id' => $s['subject']->id, 'is_shared' => true]);

        $admin = $this->idsIn($this->catalog()->manage($s['actorAdmin'], $s['subject']));
        $teacher = $this->idsIn($this->catalog()->manage($actorTeacher, $s['subject']));

        $this->assertCount(4, $admin, 'lesson, lesson, general AND unit-level resources are all listed');
        $this->assertSame($admin, $teacher);
    }

    public function test_a_group_tab_lists_exactly_what_that_groups_students_see(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $b = $this->makeResource($s, $s['lesson1'], [$s['groupB']]);
        $everyone = $this->makeResource($s, $s['lesson2']);
        $general = $this->makeResource($s, null, [$s['groupA']]);

        foreach (['A' => $s['groupA'], 'B' => $s['groupB'], 'C' => $s['groupC']] as $key => $group) {
            $tab = $this->idsIn($this->catalog()->manage($s['actorAdmin'], $s['subject'], $group->id));

            $this->assertSame($this->visibleIds($s, $s['students'][$key]), $tab, "tab {$key}");
        }
    }

    public function test_a_teacher_never_sees_a_colleagues_content_or_group_names(): void
    {
        $s = $this->scenario();
        $mine = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $colleagues = $this->makeResource($s, $s['lesson1'], [$s['groupC']]);
        $shared = $this->makeResource($s, $s['lesson2']);

        $tree = $this->catalog()->manage($s['actorTeacher'], $s['subject']);

        $this->assertSame([$mine->id, $shared->id], $this->idsIn($tree));
        $this->assertEqualsCanonicalizing(['A', 'B'], collect($tree['groups'])->pluck('name')->all(), 'a colleague\'s group is not even named');

        $sharedState = collect($tree['units'][0]['lessons'])->flatMap(fn ($l) => $l['resources'])->firstWhere('id', $shared->id);
        $this->assertSame(1, $sharedState['other_groups'], 'shared content tells the teacher it also serves someone else\'s group, without naming it');
    }

    public function test_resource_states_tell_the_whole_story(): void
    {
        $s = $this->scenario();
        $ok = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $killed = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['is_active' => false]);
        $draft = $this->makeResource($s, $s['lesson1'], [], ['is_shared' => false]);
        $unplaced = SubjectResource::factory()->unplaced()->create(['subject_id' => $s['subject']->id, 'is_shared' => true]);
        $blocked = $this->makeResource($s, $s['lesson2'], [$s['groupA']]);
        $s['lesson2']->update(['is_active' => false]);

        $tree = $this->catalog()->manage($s['actorAdmin'], $s['subject']);
        $byId = collect($tree['units'][0]['lessons'])->flatMap(fn ($l) => $l['resources'])->keyBy('id');

        $this->assertSame('visible', $byId[$ok->id]['state']);
        $this->assertSame('hidden', $byId[$killed->id]['state']);
        $this->assertSame('draft', $byId[$draft->id]['state']);
        $this->assertSame('blocked', $byId[$blocked->id]['state']);
        $this->assertSame('unplaced', $tree['unplaced'][0]['state']);
        $this->assertSame($unplaced->id, $tree['unplaced'][0]['id']);
    }

    public function test_a_paused_group_makes_the_state_partial(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        \App\Models\ResourceGroupLink::where('subject_resource_id', $resource->id)->where('group_id', $s['groupA']->id)->update(['is_active' => false]);

        $state = $this->catalog()->manage($s['actorAdmin'], $s['subject'])['units'][0]['lessons'][0]['resources'][0];

        $this->assertSame('partial', $state['state']);
        $this->assertTrue(collect($state['groups'])->firstWhere('id', $s['groupA']->id)['paused']);
    }

    public function test_filters_narrow_the_tree(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [], ['title' => 'شرح الميكانيك الكمي']);
        $doc = $this->makeResource($s, $s['lesson2'], [], ['title' => 'واجب', 'type' => 'document', 'url' => 'resources/a.pdf']);

        $byType = $this->catalog()->manage($s['actorAdmin'], $s['subject'], null, ['type' => 'document']);
        $this->assertSame([$doc->id], $this->idsIn($byType));
        $this->assertCount(1, $byType['units'][0]['lessons'], 'empty lessons are dropped while filtering');

        $bySearch = $this->catalog()->manage($s['actorAdmin'], $s['subject'], null, ['q' => 'الميكانيك']);
        $this->assertSame([$video->id], $this->idsIn($bySearch));

        $this->assertSame([], $this->idsIn($this->catalog()->manage($s['actorAdmin'], $s['subject'], null, ['q' => 'غير موجود'])));
    }

    public function test_the_library_lists_every_subject_the_actor_reaches(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1']);
        $otherSubject = \App\Models\Subject::factory()->create();
        SubjectResource::factory()->create(['subject_id' => $otherSubject->id, 'is_shared' => true]);

        $admin = $this->catalog()->library($s['actorAdmin']);
        $teacher = $this->catalog()->library($s['actorTeacher']);

        $this->assertGreaterThanOrEqual(2, count($admin['rows']));
        $this->assertSame([$s['subject']->id], collect($teacher['rows'])->pluck('subject_id')->unique()->values()->all());
    }

    public function test_the_scope_drawer_state_for_a_resource(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $scope = $this->catalog()->scopeState($s['actorTeacher'], $resource);

        $this->assertSame('resource', $scope['kind']);
        $this->assertSame([true, false], collect($scope['groups'])->pluck('sees')->all(), 'A sees it, B does not');
        $this->assertCount(2, $scope['destinations']['units'][0]['lessons']);
        $this->assertTrue($scope['destinations']['units'][0]['lessons'][0]['placed']);

        $this->expectException(LibraryException::class);
        $this->catalog()->scopeState($s['actorTeacher'], $this->makeResource($s, $s['lesson1'], [$s['groupC']]));
    }

    // -------------------------------------------------------------------- health

    private function issue(array $report, string $code): ?array
    {
        return collect($report['issues'])->firstWhere('code', $code);
    }

    public function test_health_finds_unplaced_drafts_and_blocked_content(): void
    {
        $s = $this->scenario();
        SubjectResource::factory()->unplaced()->create(['subject_id' => $s['subject']->id, 'is_shared' => true, 'title' => 'ضائع']);
        $this->makeResource($s, $s['lesson1'], [], ['is_shared' => false, 'title' => 'مسودة']);
        $this->makeResource($s, $s['lesson2'], [$s['groupA']], ['title' => 'محجوب']);
        $s['lesson2']->update(['is_active' => false]);

        $report = $this->health()->scan($s['actorAdmin'], $s['subject']);

        $this->assertSame('ضائع', $this->issue($report, 'unplaced')['items'][0]['label']);
        $this->assertSame('مسودة', $this->issue($report, 'draft')['items'][0]['label']);
        $this->assertSame('محجوب', $this->issue($report, 'blocked')['items'][0]['label']);
        $this->assertGreaterThan(0, $report['summary']['error']);
    }

    public function test_health_flags_a_lesson_declared_for_a_group_whose_students_see_nothing_and_fixes_it(): void
    {
        // the real-data symptom: "I shared the lesson with group B and nothing happens"
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id, $s['groupB']->id]);
        $video = $this->makeResource($s, $lesson, [$s['groupA']]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));

        $report = $this->health()->scan($s['actorTeacher'], $s['subject']);
        $issue = $this->issue($report, 'declared_empty');
        $this->assertNotNull($issue);
        $this->assertSame($s['groupB']->id, $issue['items'][0]['group_id']);

        $result = $this->health()->fix($s['actorTeacher'], $s['subject'], 'share_container_with_group');

        $this->assertSame(1, $result->data['fixed']);
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertNull($this->issue($this->health()->scan($s['actorTeacher'], $s['subject']), 'declared_empty'));

        // one Undo takes the whole fix back
        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_health_spots_duplicates_and_merges_them_keeping_every_audience_and_placement(): void
    {
        $s = $this->scenario();
        $one = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['title' => 'نسخة 1', 'content_hash' => str_repeat('a', 40)]);
        $two = $this->makeResource($s, $s['lesson2'], [$s['groupB']], ['title' => 'نسخة 2', 'content_hash' => str_repeat('a', 40)]);
        $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['title' => 'مختلف', 'content_hash' => str_repeat('b', 40)]);

        $report = $this->health()->scan($s['actorAdmin'], $s['subject']);
        $dup = $this->issue($report, 'duplicates');
        $this->assertCount(1, $dup['items']);
        $this->assertCount(2, $dup['items'][0]['resources']);

        $result = $this->health()->fix($s['actorAdmin'], $s['subject'], 'merge_duplicates');
        $this->assertSame(1, $result->data['fixed']);

        $keeper = SubjectResource::find($one->id);
        $this->assertNull(SubjectResource::find($two->id), 'the copy went to the trash');
        $this->assertEqualsCanonicalizing([$s['groupA']->id, $s['groupB']->id], $keeper->groups()->pluck('groups.id')->all());
        $this->assertEqualsCanonicalizing(['L'.$s['lesson1']->id, 'L'.$s['lesson2']->id], $keeper->placements()->pluck('target_key')->all());
        $this->assertContains($keeper->id, $this->visibleIds($s, $s['students']['B']), 'group B kept access through the merged resource');
        $this->assertNotContains($two->id, $this->visibleIds($s, $s['students']['B']));

        app(LibraryJournal::class)->undo($s['actorAdmin'], $result->undoToken);
        $this->assertNotNull(SubjectResource::find($two->id), 'undo brings the copy back');
        $this->assertCount(1, SubjectResource::find($one->id)->groups);
    }

    public function test_a_teacher_cannot_merge_duplicates(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['content_hash' => str_repeat('a', 40)]);
        $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['content_hash' => str_repeat('a', 40)]);

        $this->expectException(LibraryException::class);
        $this->health()->fix($s['actorTeacher'], $s['subject'], 'merge_duplicates');
    }

    public function test_activating_blocked_containers_brings_the_content_back(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson2'], [$s['groupA']]);
        $s['lesson2']->update(['is_active' => false]);
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));

        $this->health()->fix($s['actorAdmin'], $s['subject'], 'activate_containers');

        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
    }
}
