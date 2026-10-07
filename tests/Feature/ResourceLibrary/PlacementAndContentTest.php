<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\ResourcePlacement;
use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ContentManager;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryJournal;
use App\Services\ResourceLibrary\PlacementManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * "Upload once, assign anywhere" (placements) and the safe delete / restore / undo of content.
 */
class PlacementAndContentTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function placements(): PlacementManager
    {
        return app(PlacementManager::class);
    }

    private function content(): ContentManager
    {
        return app(ContentManager::class);
    }

    // ----------------------------------------------------------------- placements

    public function test_one_video_can_appear_in_several_lessons_without_a_second_upload(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->placements()->attach($s['actorTeacher'], $video, [$s['lesson2']]);

        $this->assertSame(2, $video->placements()->count());
        $this->assertSame(1, SubjectResource::where('subject_id', $s['subject']->id)->count());
        $this->assertSame([$video->id], $s['lesson1']->fresh()->resources->pluck('id')->all());
        $this->assertSame([$video->id], $s['lesson2']->fresh()->resources->pluck('id')->all());
    }

    public function test_placing_twice_is_idempotent_and_brings_back_a_removed_placement(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->placements()->attach($s['actorTeacher'], $video, [$s['lesson2']]);
        $second = $this->placements()->attach($s['actorTeacher'], $video, [$s['lesson2']]);
        $this->assertNull($second->undoToken);
        $this->assertSame(2, $video->placements()->count());

        $this->placements()->detach($s['actorTeacher'], $video, [$s['lesson2']]);
        $this->assertSame(1, $video->placements()->count());

        $this->placements()->attach($s['actorTeacher'], $video, [$s['lesson2']]);
        $this->assertSame(2, ResourcePlacement::withTrashed()->where('subject_resource_id', $video->id)->count(), 'the old row is restored, not duplicated');
    }

    public function test_the_last_placement_cannot_be_removed_by_accident(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        try {
            $this->placements()->detach($s['actorTeacher'], $video, [$s['lesson1']]);
            $this->fail('removing the only placement must be refused');
        } catch (LibraryException $e) {
            $this->assertSame('last_placement', $e->errorCode);
        }

        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_moving_the_last_placement_to_general_keeps_the_video_visible(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->placements()->detach($s['actorTeacher'], $video, [$s['lesson1']], moveToGeneralIfLast: true);

        $this->assertSame(['G'], $video->placements()->pluck('target_key')->all());
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertNull($video->fresh()->educational_lesson_id, 'the legacy primary lesson follows the placements');
    }

    public function test_hiding_a_video_in_one_lesson_keeps_it_in_the_others(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        $this->placements()->attach($s['actorTeacher'], $video, [$s['lesson2']]);

        $this->placements()->setActive($s['actorTeacher'], $video, $s['lesson1'], false);

        $tree = app(\App\Services\StudentContentGate::class)->tree($s['students']['A']->id, $s['subject']);
        $this->assertSame([$s['lesson2']->id], $tree['units']->first()->lessons->pluck('id')->all());
    }

    public function test_a_teacher_cannot_move_a_video_that_serves_a_colleagues_group(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']); // shared with everyone, including C

        $this->expectException(LibraryException::class);
        $this->placements()->attach($s['actorTeacher'], $shared, [$s['lesson2']]);
    }

    public function test_placements_are_ordered_inside_a_lesson(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1']);
        $b = $this->makeResource($s, $s['lesson1']);
        $c = $this->makeResource($s, $s['lesson1']);

        $this->placements()->reorder($s['actorTeacher'], $s['lesson1'], $s['subject']->id, [$c->id, $a->id, $b->id]);

        $this->assertSame([$c->id, $a->id, $b->id], $s['lesson1']->fresh()->resources->pluck('id')->all());
    }

    // ------------------------------------------------------------- delete / restore

    public function test_deleting_a_video_is_soft_and_undo_brings_everything_back(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        $this->exclude($video, $s['subject']->id, [$s['students']['A']->id]);

        $result = $this->content()->deleteResource($s['actorTeacher'], $video);

        $this->assertTrue($video->fresh()->trashed());
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertNotNull($result->undoToken);

        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);

        $this->assertFalse($video->fresh()->trashed());
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame(2, $video->groups()->count(), 'audience survived');
        $this->assertSame(1, $video->contentExclusions()->count(), 'exclusions survived');
    }

    public function test_a_teacher_deleting_a_video_shared_with_a_colleague_only_stops_it_for_their_groups(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']);

        $result = $this->content()->deleteResource($s['actorTeacher'], $shared);

        $this->assertTrue($result->data['detached_only']);
        $this->assertFalse($shared->fresh()->trashed());
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['C']));
    }

    public function test_deleting_a_lesson_trashes_what_lived_only_there_and_keeps_what_lives_elsewhere(): void
    {
        $s = $this->scenario();
        $onlyHere = $this->makeResource($s, $s['lesson1']);
        $everywhere = $this->makeResource($s, $s['lesson1']);
        $this->placements()->attach($s['actorAdmin'], $everywhere, [$s['lesson2']]);

        $result = $this->content()->deleteContainer($s['actorAdmin'], $s['lesson1']);

        $this->assertTrue($s['lesson1']->fresh() === null || EducationalLesson::withTrashed()->find($s['lesson1']->id)->trashed());
        $this->assertTrue(SubjectResource::withTrashed()->find($onlyHere->id)->trashed(), 'sole resource goes to the trash with the lesson');
        $this->assertFalse(SubjectResource::withTrashed()->find($everywhere->id)->trashed(), 'a resource shown elsewhere stays');
        $this->assertSame([$everywhere->id], $this->visibleIds($s, $s['students']['A']));

        app(LibraryJournal::class)->undo($s['actorAdmin'], $result->undoToken);

        $this->assertFalse(EducationalLesson::withTrashed()->find($s['lesson1']->id)->trashed());
        $this->assertFalse(SubjectResource::withTrashed()->find($onlyHere->id)->trashed());
        $this->assertSame([$onlyHere->id, $everywhere->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_restoring_a_deleted_unit_from_the_trash_brings_back_exactly_what_was_deleted_with_it(): void
    {
        $s = $this->scenario();
        $inLesson = $this->makeResource($s, $s['lesson1']);
        $earlier = $this->makeResource($s, $s['lesson2']);
        $this->content()->deleteResource($s['actorAdmin'], $earlier); // deleted on its own, long before

        $this->travel(5)->seconds();
        $this->content()->deleteContainer($s['actorAdmin'], $s['unit']);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));

        $this->travel(5)->seconds();
        $this->content()->restoreContainer($s['actorAdmin'], \App\Models\EducationalUnit::withTrashed()->find($s['unit']->id));

        $this->assertFalse(SubjectResource::withTrashed()->find($inLesson->id)->trashed());
        $this->assertTrue(SubjectResource::withTrashed()->find($earlier->id)->trashed(), 'a separately deleted video stays deleted');
        $this->assertSame([$inLesson->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_a_teacher_cannot_delete_a_lesson_holding_a_colleagues_content(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupC']]);

        $this->expectException(LibraryException::class);
        $this->content()->deleteContainer($s['actorTeacher'], $s['lesson1']);
    }

    public function test_deleting_the_lesson_never_turns_its_videos_into_general_resources(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->content()->deleteContainer($s['actorAdmin'], $s['lesson1']);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertFalse(ResourcePlacement::withTrashed()->where('subject_resource_id', $video->id)->where('target_key', 'G')->exists());
    }

    // ------------------------------------------------------------------- creating

    public function test_creating_a_resource_inherits_the_audience_of_the_lesson_it_is_added_to(): void
    {
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id]);

        $result = $this->content()->createResource(
            $s['actorTeacher'], $s['subject'],
            ['title' => 'درس جديد', 'type' => 'link', 'url' => 'https://youtu.be/abc123def45'],
            [$lesson],
        );

        $resource = $result->data['resource'];
        $this->assertFalse($resource->is_shared, 'must NOT silently become shared with every group');
        $this->assertSame([$s['groupA']->id], $resource->groups()->pluck('groups.id')->all());
        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame('teacher', $resource->created_by_type);
    }

    public function test_a_teacher_who_does_not_reach_every_group_never_creates_shared_content(): void
    {
        $s = $this->scenario();

        $result = $this->content()->createResource(
            $s['actorTeacher'], $s['subject'],
            ['title' => 'عام', 'type' => 'link', 'url' => 'https://example.com/x'],
            [$s['lesson1']], ['mode' => 'shared'],
        );

        $resource = $result->data['resource'];
        $this->assertFalse($resource->is_shared);
        $this->assertEqualsCanonicalizing([$s['groupA']->id, $s['groupB']->id], $resource->groups()->pluck('groups.id')->all());
        $this->assertSame([], $this->visibleIds($s, $s['students']['C']), 'the colleague\'s students must not receive it');
    }

    public function test_an_admin_default_is_shared_with_every_group(): void
    {
        $s = $this->scenario();

        $resource = $this->content()->createResource(
            $s['actorAdmin'], $s['subject'],
            ['title' => 'للجميع', 'type' => 'link', 'url' => 'https://example.com/y'],
            [$s['lesson1']],
        )->data['resource'];

        $this->assertTrue($resource->is_shared);
        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['C']));
    }

    public function test_a_resource_can_be_created_straight_into_a_group_tab(): void
    {
        $s = $this->scenario();

        $resource = $this->content()->createResource(
            $s['actorTeacher'], $s['subject'],
            ['title' => 'للمجموعة ب', 'type' => 'link', 'url' => 'https://example.com/z'],
            [$s['lesson1']], ['mode' => 'groups', 'group_ids' => [$s['groupB']->id]],
        )->data['resource'];

        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_a_teacher_cannot_create_content_for_a_colleagues_group(): void
    {
        $s = $this->scenario();

        $this->expectException(LibraryException::class);
        $this->content()->createResource(
            $s['actorTeacher'], $s['subject'],
            ['title' => 'x', 'type' => 'link', 'url' => 'https://example.com/q'],
            [$s['lesson1']], ['mode' => 'groups', 'group_ids' => [$s['groupC']->id]],
        );
    }

    public function test_a_force_delete_keeps_the_file_another_row_still_points_at(): void
    {
        Storage::fake('protected_videos');

        $s = $this->scenario();
        $path = 'resources/shared-by-two.mp4';
        Storage::disk('protected_videos')->put($path, str_repeat('x', 2048));

        $first = $this->makeResource($s, $s['lesson1'], [], ['type' => 'video', 'url' => $path]);
        $second = $this->makeResource($s, $s['lesson2'], [], ['type' => 'video', 'url' => $path]);

        $this->content()->forceDeleteResource($s['actorAdmin'], $first);
        $this->assertTrue(Storage::disk('protected_videos')->exists($path), 'the other resource still needs the file');

        $this->content()->forceDeleteResource($s['actorAdmin'], $second);
        $this->assertFalse(Storage::disk('protected_videos')->exists($path), 'the last owner takes the file with it');
    }

    public function test_a_path_that_climbs_out_of_incoming_is_rejected(): void
    {
        $s = $this->scenario();

        $this->expectException(LibraryException::class);
        $this->content()->createResource(
            $s['actorAdmin'], $s['subject'],
            ['title' => 'x', 'type' => 'video', 'uploaded_path' => 'incoming/../../.env'],
            [$s['lesson1']],
        );
    }
}
