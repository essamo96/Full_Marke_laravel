<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\StudentContentExclusion;
use App\Models\SubjectResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * The library API over HTTP, for both roles: routes, guards, permissions, JSON shapes, and the
 * fence that keeps a teacher inside their own groups.
 */
class LibraryApiTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function asTeacher(array $s)
    {
        return $this->actingAs($s['teacher'], 'teacher');
    }

    private function asAdmin(array $s)
    {
        foreach (['admin.resource-library.view', 'admin.subject_content.edit'] as $name) {
            $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']));
        }

        return $this->actingAs($s['admin'], 'admin');
    }

    private function url(string $role, string $name, array $params = []): string
    {
        return route(($role === 'teacher' ? 'teacher.library.' : 'library.').$name, $params);
    }

    // ------------------------------------------------------------------ guards

    public function test_guests_and_students_are_turned_away(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        $this->getJson($this->url('teacher', 'rows'))->assertUnauthorized();
        $this->getJson($this->url('admin', 'rows'))->assertUnauthorized();

        $this->actingAs($s['students']['A'], 'student')
            ->getJson($this->url('teacher', 'rows'))->assertUnauthorized();
        $this->actingAs($s['students']['A'], 'student')
            ->postJson($this->url('admin', 'active', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['active' => false])
            ->assertUnauthorized();
    }

    public function test_an_admin_without_any_library_permission_is_refused(): void
    {
        $s = $this->scenario();

        $this->actingAs($s['admin'], 'admin')->getJson($this->url('admin', 'rows'))->assertForbidden();
    }

    public function test_a_view_only_admin_can_read_the_library_but_every_change_is_refused(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => 'admin.subject_content.view', 'guard_name' => 'admin']));
        $client = $this->actingAs($s['admin'], 'admin');
        $key = $video->getRouteKey();

        $client->getJson($this->url('admin', 'tree', ['subject' => $s['subject']->getRouteKey()]))->assertOk();
        $client->getJson($this->url('admin', 'rows'))->assertOk();

        $client->postJson($this->url('admin', 'active', ['type' => 'resources', 'key' => $key]), ['active' => false])
            ->assertForbidden()
            ->assertJsonPath('success', false);
        $client->deleteJson($this->url('admin', 'destroy', ['type' => 'resources', 'key' => $key]))->assertForbidden();
        $client->postJson($this->url('admin', 'bulk.active', ['subject' => $s['subject']->getRouteKey()]), ['active' => false, 'type' => 'video'])->assertForbidden();

        $this->assertTrue($video->fresh()->is_active, 'nothing may have changed');
    }

    public function test_the_admin_pages_tell_the_client_whether_it_may_edit(): void
    {
        $s = $this->scenario();

        $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => 'admin.subject_content.view', 'guard_name' => 'admin']));
        $this->assertFalse($s['admin']->can('admin.subject_content.edit'));

        $page = $this->actingAs($s['admin'], 'admin')->get(route('subject_content.manage', $s['subject']->getRouteKey()))->assertOk();
        $this->assertStringContainsString('"canEdit":false', $page->getContent());

        $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => 'admin.subject_content.edit', 'guard_name' => 'admin']));
        $page = $this->actingAs($s['admin']->fresh(), 'admin')->get(route('subject_content.manage', $s['subject']->getRouteKey()))->assertOk();
        $this->assertStringContainsString('"canEdit":true', $page->getContent());
    }

    public function test_route_keys_are_url_safe_so_javascript_can_concatenate_them(): void
    {
        $this->scenario();

        for ($id = 1; $id <= 3000; $id += 7) {
            $key = \Illuminate\Support\Facades\Crypt::encryptString((string) $id);
            $this->assertMatchesRegularExpression('/^[A-Za-z0-9=]+$/', $key, "key for id {$id} contains a character that breaks a URL path");
        }
    }

    // ---------------------------------------------------- the tree, for both roles

    #[DataProvider('roles')]
    public function test_the_tree_endpoint_returns_the_same_shape_for_admin_and_teacher(string $role): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $client = $role === 'teacher' ? $this->asTeacher($s) : $this->asAdmin($s);
        $response = $client->getJson($this->url($role, 'tree', ['subject' => $s['subject']->getRouteKey()]))->assertOk();

        $response->assertJsonPath('success', true)
            ->assertJsonPath('tree.units.0.lessons.0.resources.0.id', $video->id)
            ->assertJsonPath('tree.units.0.lessons.0.resources.0.state', 'visible')
            ->assertJsonStructure(['tree' => ['subject', 'groups', 'units', 'general', 'unplaced', 'stats', 'is_admin']]);
    }

    public static function roles(): array
    {
        return ['teacher' => ['teacher'], 'admin' => ['admin']];
    }

    public function test_a_teacher_cannot_open_a_colleagues_resource_even_with_its_key(): void
    {
        $s = $this->scenario();
        $theirs = $this->makeResource($s, $s['lesson1'], [$s['groupC']]);

        $this->asTeacher($s)
            ->getJson($this->url('teacher', 'scope', ['type' => 'resources', 'key' => $theirs->getRouteKey()]))
            ->assertNotFound();

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'active', ['type' => 'resources', 'key' => $theirs->getRouteKey()]), ['active' => false])
            ->assertNotFound();

        $this->assertTrue($theirs->fresh()->is_active);
    }

    public function test_garbage_keys_are_a_404_not_a_500(): void
    {
        $s = $this->scenario();

        $this->asTeacher($s)->getJson($this->url('teacher', 'scope', ['type' => 'resources', 'key' => 'not-a-real-key']))->assertNotFound();
        $this->asTeacher($s)->getJson($this->url('teacher', 'tree', ['subject' => 'zzz']))->assertNotFound();
    }

    // -------------------------------------------------- the user's four complaints

    /** "Sharing a video from one group to another does not work at all." */
    public function test_sharing_a_video_with_another_group_over_http_makes_it_appear_for_that_group(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'groups.attach', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['group_ids' => [$s['groupB']->id]])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['message', 'undo' => ['token', 'seconds']]);

        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));

        // the group B tab of the teacher now lists it, exactly like the students see it
        $this->asTeacher($s)
            ->getJson($this->url('teacher', 'tree', ['subject' => $s['subject']->getRouteKey(), 'group' => $s['groupB']->id]))
            ->assertJsonPath('tree.units.0.lessons.0.resources.0.id', $video->id);
    }

    public function test_detaching_groups_and_placements_uses_delete_with_a_json_body(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);
        $this->asTeacher($s)->postJson($this->url('teacher', 'resources.placements.attach', ['key' => $video->getRouteKey()]), [
            'targets' => [['type' => 'lesson', 'key' => $s['lesson2']->getRouteKey()]],
        ])->assertOk();

        // stop showing it to group B
        $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'groups.detach', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['group_ids' => [$s['groupB']->id]])
            ->assertOk()->assertJsonPath('success', true);
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));

        // remove it from lesson 2 only; lesson 1 keeps it
        $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'resources.placements.detach', ['key' => $video->getRouteKey()]), [
                'targets' => [['type' => 'lesson', 'key' => $s['lesson2']->getRouteKey()]],
            ])->assertOk();
        $this->assertSame(['L'.$s['lesson1']->id], $video->placements()->pluck('target_key')->all());

        // ...but never the last place by accident: a 422 with a machine readable code
        $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'resources.placements.detach', ['key' => $video->getRouteKey()]), [
                'targets' => [['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()]],
            ])->assertStatus(422)->assertJsonPath('code', 'last_placement')->assertJsonPath('can_move_to_general', true);

        $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'resources.placements.detach', ['key' => $video->getRouteKey()]), [
                'targets' => [['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()]], 'move_to_general' => true,
            ])->assertOk();
        $this->assertSame(['G'], $video->placements()->pluck('target_key')->all());
    }

    public function test_sharing_with_a_colleagues_group_is_refused_with_a_clear_message(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'groups.attach', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['group_ids' => [$s['groupC']->id]])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertSame([], $this->visibleIds($s, $s['students']['C']));
    }

    /** "I want a button to stop the videos from showing to students." */
    public function test_the_stop_all_videos_button_and_its_undo(): void
    {
        $s = $this->scenario();
        $v1 = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $v2 = $this->makeResource($s, $s['lesson2'], [$s['groupA'], $s['groupB']]);
        $doc = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['type' => 'document', 'url' => 'resources/x.pdf']);

        $response = $this->asTeacher($s)
            ->postJson($this->url('teacher', 'bulk.active', ['subject' => $s['subject']->getRouteKey()]), ['active' => false, 'type' => 'video'])
            ->assertOk()
            ->assertJsonPath('changed', 2);

        $this->assertSame([$doc->id], $this->visibleIds($s, $s['students']['A']), 'only the document is left');

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'undo'), ['token' => $response->json('undo.token')])
            ->assertOk();

        $this->assertSame([$v1->id, $v2->id, $doc->id], $this->visibleIds($s, $s['students']['A']));
    }

    public function test_the_kill_switch_works_for_a_single_resource_a_lesson_and_a_unit(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);

        foreach ([['resources', $video], ['lessons', $s['lesson1']], ['units', $s['unit']]] as [$type, $model]) {
            $this->asAdmin($s)
                ->postJson($this->url('admin', 'active', ['type' => $type, 'key' => $model->getRouteKey()]), ['active' => false])
                ->assertOk();
            $this->assertSame([], $this->visibleIds($s, $s['students']['A']), "after stopping the {$type}");

            $this->asAdmin($s)
                ->postJson($this->url('admin', 'active', ['type' => $type, 'key' => $model->getRouteKey()]), ['active' => true])
                ->assertOk();
            $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']), "after resuming the {$type}");
        }
    }

    /** "When I exclude a student from a video or unit, the unit and videos stay for that student." */
    public function test_excluding_a_student_from_a_unit_hides_it_everywhere_for_that_student(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1']);
        $student = $s['students']['A'];

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'exclusions', ['type' => 'units', 'key' => $s['unit']->getRouteKey()]), [
                'student_id' => $student->id, 'excluded' => true,
            ])->assertOk()->assertJsonPath('success', true);

        // listings
        $this->assertSame([], $this->visibleIds($s, $student));

        // the student's own pages and direct URLs
        $this->actingAs($student, 'student');
        $this->assertFalse(app(\App\Services\StudentContentGate::class)->canAccess($video->fresh(), $student->id));

        // the other students are untouched
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_the_students_table_endpoint_and_the_toggle(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']]);

        $rows = $this->asTeacher($s)
            ->getJson($this->url('teacher', 'students', ['type' => 'resources', 'key' => $video->getRouteKey()]))
            ->assertOk()->json('students');
        $this->assertCount(2, $rows);

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'exclusions', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['student_id' => $s['students']['A']->id, 'excluded' => true])
            ->assertOk();
        $this->assertSame(1, StudentContentExclusion::count());

        // a colleague's student cannot be touched
        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'exclusions', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['student_id' => $s['students']['C']->id, 'excluded' => true])
            ->assertForbidden();
    }

    // --------------------------------------------------------- delete / restore / trash

    public function test_delete_then_undo_then_restore_from_the_trash(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $delete = $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'destroy', ['type' => 'resources', 'key' => $video->getRouteKey()]))
            ->assertOk();
        $this->assertTrue($video->fresh()->trashed());

        $this->asTeacher($s)->postJson($this->url('teacher', 'undo'), ['token' => $delete->json('undo.token')])->assertOk();
        $this->assertFalse($video->fresh()->trashed());

        // delete again and bring it back from the trash instead of the toast
        $this->asTeacher($s)->deleteJson($this->url('teacher', 'destroy', ['type' => 'resources', 'key' => $video->getRouteKey()]))->assertOk();

        $trash = $this->asTeacher($s)->getJson($this->url('teacher', 'trash', ['subject' => $s['subject']->getRouteKey()]))->assertOk();
        $this->assertSame($video->id, $trash->json('resources.0.id'));

        $this->asTeacher($s)
            ->postJson($this->url('teacher', 'restore', ['type' => 'resources', 'key' => $video->getRouteKey()]))
            ->assertOk();
        $this->assertFalse($video->fresh()->trashed());
    }

    public function test_only_an_admin_can_destroy_for_good(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $video->delete();

        $this->asTeacher($s)
            ->deleteJson($this->url('teacher', 'resources.force', ['key' => $video->getRouteKey()]))
            ->assertForbidden();

        $this->asAdmin($s)
            ->deleteJson($this->url('admin', 'resources.force', ['key' => $video->getRouteKey()]))
            ->assertOk();
        $this->assertNull(SubjectResource::withTrashed()->find($video->id));
    }

    // -------------------------------------------------------------------- health, explain

    public function test_health_and_explain_endpoints(): void
    {
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id, $s['groupB']->id]);
        $video = $this->makeResource($s, $lesson, [$s['groupA']]);

        $health = $this->asTeacher($s)
            ->getJson($this->url('teacher', 'health', ['subject' => $s['subject']->getRouteKey()]))
            ->assertOk()->json();
        $codes = collect($health['issues'])->pluck('code');
        $this->assertTrue($codes->contains('declared_empty'), 'the lesson declared for group B whose students see nothing is reported');
        $this->assertTrue($codes->contains('missing_file'), 'factory resources point at files that do not exist');

        $why = $this->asTeacher($s)
            ->getJson($this->url('teacher', 'explain', ['resource' => $video->getRouteKey(), 'student_id' => $s['students']['B']->id]))
            ->assertOk()->json();
        $this->assertFalse($why['visible']);
        $this->assertSame('المورد موجّه لمجموعة الطالب', collect($why['steps'])->firstWhere('ok', false)['label']);

        $seen = $this->asTeacher($s)
            ->getJson($this->url('teacher', 'explain', ['resource' => $video->getRouteKey(), 'student_id' => $s['students']['A']->id]))
            ->assertOk()->json();
        $this->assertTrue($seen['visible']);
    }

    public function test_the_activity_feed_lists_my_actions_with_undo_state(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->asTeacher($s)->postJson($this->url('teacher', 'active', ['type' => 'resources', 'key' => $video->getRouteKey()]), ['active' => false])->assertOk();

        $entries = $this->asTeacher($s)
            ->getJson($this->url('teacher', 'activity', ['subject' => $s['subject']->getRouteKey()]))
            ->assertOk()->json('entries');

        $this->assertCount(1, $entries);
        $this->assertTrue($entries[0]['undoable']);
        $this->assertStringContainsString('إيقاف عرض', $entries[0]['summary']);
    }
}
