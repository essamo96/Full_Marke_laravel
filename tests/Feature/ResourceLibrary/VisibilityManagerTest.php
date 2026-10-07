<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\ResourceAuditLog;
use App\Models\ResourceGroupLink;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryJournal;
use App\Services\ResourceLibrary\VisibilityManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Sharing a resource with more groups, pausing a group, and the kill switch - for admin and
 * teacher through the same calls, with the teacher fenced into their own groups.
 */
class VisibilityManagerTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function visibility(): VisibilityManager
    {
        return app(VisibilityManager::class);
    }

    // ------------------------------------------------------------- group to group

    public function test_teacher_shares_a_video_from_one_group_to_another_without_reuploading(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));

        $result = $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);

        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']), 'group B sees it at once');
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']), 'group A is untouched');
        $this->assertSame(1, \App\Models\SubjectResource::where('subject_id', $s['subject']->id)->count(), 'sharing never copies the resource');
        $this->assertNotNull($result->undoToken);
    }

    public function test_attaching_the_same_group_twice_never_duplicates_the_link(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);
        $second = $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id, $s['groupA']->id]);

        $this->assertSame(2, ResourceGroupLink::where('subject_resource_id', $video->id)->count());
        $this->assertNull($second->undoToken, 'nothing changed, nothing to undo');
    }

    public function test_sharing_content_that_was_detached_restores_the_old_link_instead_of_inserting_a_second_one(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->visibility()->detachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame(1, ResourceGroupLink::where('subject_resource_id', $video->id)->count());
        $this->assertSame(1, ResourceGroupLink::onlyTrashed()->where('subject_resource_id', $video->id)->count());

        $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);

        $this->assertSame(2, ResourceGroupLink::withTrashed()->where('subject_resource_id', $video->id)->count());
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_teacher_cannot_share_with_a_group_of_a_colleague(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->expectException(LibraryException::class);
        $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupC']->id]);
    }

    public function test_admin_can_share_with_any_group(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->visibility()->attachGroups($s['actorAdmin'], $video, [$s['groupC']->id]);

        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['C']));
    }

    public function test_a_teacher_cannot_touch_a_resource_they_cannot_see(): void
    {
        $s = $this->scenario();
        $colleaguesVideo = $this->makeResource($s, $s['lesson1'], [$s['groupC']]);

        $this->expectException(LibraryException::class);
        $this->visibility()->attachGroups($s['actorTeacher'], $colleaguesVideo, [$s['groupA']->id]);
    }

    // ------------------------------------------------------------------ pause

    public function test_removing_a_group_from_shared_content_pauses_it_and_resuming_brings_it_back(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']);

        $this->visibility()->detachGroups($s['actorAdmin'], $shared, [$s['groupA']->id]);
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['B']));

        $this->visibility()->setGroupPaused($s['actorAdmin'], $shared, $s['groupA']->id, false);
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame(0, ResourceGroupLink::where('subject_resource_id', $shared->id)->count(), 'a deny row is removed, not kept as noise');
    }

    public function test_pausing_an_explicit_link_keeps_the_link(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $this->visibility()->setGroupPaused($s['actorTeacher'], $video, $s['groupA']->id, true);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame(2, $video->groups()->count());
        $this->assertSame(1, $video->pausedGroups()->count());
    }

    // ------------------------------------------------------------- kill switch

    public function test_admin_kill_switch_hides_the_video_from_everyone(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        $result = $this->visibility()->setResourceActive($s['actorAdmin'], $video, false);

        $this->assertFalse($video->fresh()->is_active);
        $this->assertSame('global', $result->data['mode']);
        $this->assertNotNull($video->fresh()->deactivated_at);
        foreach ($s['students'] as $student) {
            $this->assertSame([], $this->visibleIds($s, $student));
        }

        $this->visibility()->setResourceActive($s['actorAdmin'], $video, true);
        $this->assertTrue($video->fresh()->is_active);
        $this->assertNull($video->fresh()->deactivated_at);
    }

    public function test_teacher_kill_switch_on_own_content_is_global(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $result = $this->visibility()->setResourceActive($s['actorTeacher'], $video, false);

        $this->assertSame('global', $result->data['mode']);
        $this->assertFalse($video->fresh()->is_active);
    }

    public function test_teacher_kill_switch_on_content_shared_with_a_colleague_only_stops_their_own_groups(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']); // visible to A, B and the colleague's C

        $result = $this->visibility()->setResourceActive($s['actorTeacher'], $shared, false);

        $this->assertSame('groups', $result->data['mode']);
        $this->assertTrue($shared->fresh()->is_active, 'the global switch must stay on');
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['C']), 'the colleague\'s students are untouched');

        $this->visibility()->setResourceActive($s['actorTeacher'], $shared, true);
        $this->assertSame([$shared->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_teacher_cannot_switch_back_on_what_the_admin_switched_off(): void
    {
        $s = $this->scenario();
        $shared = $this->makeResource($s, $s['lesson1']);
        $this->visibility()->setResourceActive($s['actorAdmin'], $shared, false);

        $this->expectException(LibraryException::class);
        $this->visibility()->setResourceActive($s['actorTeacher'], $shared, true);
    }

    public function test_container_kill_switch_hides_everything_inside_without_deleting_it(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        $this->visibility()->setContainerActive($s['actorAdmin'], $s['lesson1'], false);

        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertTrue($video->fresh()->is_active, 'the resource itself is not touched');

        $this->visibility()->setContainerActive($s['actorAdmin'], $s['lesson1'], true);
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_stop_every_video_in_one_action_and_undo_it(): void
    {
        $s = $this->scenario();
        $v1 = $this->makeResource($s, $s['lesson1']);
        $v2 = $this->makeResource($s, $s['lesson2'], [$s['groupA']]);
        $doc = $this->makeResource($s, $s['lesson1'], [], ['type' => 'document', 'url' => 'resources/x.pdf']);

        $videos = \App\Models\SubjectResource::where('subject_id', $s['subject']->id)->where('type', 'video')->get();
        $result = $this->visibility()->bulkSetActive($s['actorTeacher'], $s['subject']->id, $videos, false);

        $this->assertSame(2, $result->data['changed']);
        $this->assertTrue($v1->fresh()->is_active, 'v1 also serves the colleague\'s group: only the teacher\'s groups are paused');
        $this->assertFalse($v2->fresh()->is_active, 'v2 is the teacher\'s alone: the real switch is flipped');
        $this->assertTrue($doc->fresh()->is_active, 'documents are not videos');
        $this->assertSame([$doc->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$doc->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([$v1->id, $doc->id], $this->visibleIds($s, $s['students']['C']), 'the colleague\'s students keep v1');

        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);

        $this->assertTrue($v2->fresh()->is_active);
        $this->assertSame([$v1->id, $v2->id, $doc->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([$v1->id, $doc->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame(0, ResourceGroupLink::where('subject_resource_id', $v1->id)->count(), 'the temporary pause rows are gone');
    }

    // -------------------------------------------------------------------- undo

    public function test_undo_restores_the_exact_previous_audience(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $result = $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));

        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);

        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame(1, ResourceGroupLink::withTrashed()->where('subject_resource_id', $video->id)->count(), 'no leftover rows');
    }

    public function test_an_action_can_only_be_undone_once_and_only_by_its_author_or_an_admin(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $result = $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);

        try {
            app(LibraryJournal::class)->undo($s['actorColleague'], $result->undoToken);
            $this->fail('a colleague must not undo someone else\'s action');
        } catch (LibraryException $e) {
            $this->assertSame(403, $e->status);
        }

        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);

        $this->expectException(LibraryException::class);
        app(LibraryJournal::class)->undo($s['actorAdmin'], $result->undoToken);
    }

    public function test_undo_expires_after_the_configured_window(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $result = $this->visibility()->attachGroups($s['actorTeacher'], $video, [$s['groupB']->id]);

        ResourceAuditLog::where('uuid', $result->undoToken)->update(['created_at' => now()->subHour()]);

        $this->expectException(LibraryException::class);
        app(LibraryJournal::class)->undo($s['actorTeacher'], $result->undoToken);
    }

    // ------------------------------------------------------------------ shared

    public function test_only_someone_who_reaches_every_group_can_share_with_all_groups(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->visibility()->setShared($s['actorAdmin'], $video, true);
        $this->assertTrue($video->fresh()->is_shared);
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['C']));

        // back to a list: the audience is preserved (every group), not silently emptied
        $this->visibility()->setShared($s['actorAdmin'], $video->fresh(), false);
        $this->assertFalse($video->fresh()->is_shared);
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['C']));
        $this->assertSame(3, $video->groups()->count());

        $teacherVideo = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $this->expectException(LibraryException::class);
        $this->visibility()->setShared($s['actorTeacher'], $teacherVideo, true); // the colleague has group C
    }

    public function test_sharing_a_whole_lesson_with_a_group_reaches_every_resource_in_it(): void
    {
        $s = $this->scenario();
        $one = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $two = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $other = $this->makeResource($s, $s['lesson2'], [$s['groupA']]);

        $this->visibility()->attachGroupsToContainer($s['actorTeacher'], $s['lesson1'], [$s['groupB']->id]);

        $this->assertSame([$one->id, $two->id], $this->visibleIds($s, $s['students']['B']));
        $this->assertNotContains($other->id, $this->visibleIds($s, $s['students']['B']), 'the other lesson stays private');
    }
}
