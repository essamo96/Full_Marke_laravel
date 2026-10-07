<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\Registration;
use App\Models\Student;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Every screen that shows library content actually renders, for the right role - and the student
 * screens never show (or link to) what the student must not see.
 */
class PagesRenderTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    private function verified(Student $student): Student
    {
        $student->forceFill(['email_verified_at' => now()])->save();

        return $student;
    }

    /** The RL_CONFIG object a workspace page hands to its JavaScript (Arabic is \u-escaped in the HTML). */
    private function configOf(string $html): array
    {
        $this->assertSame(1, preg_match('~window\.RL_CONFIG = (\{.*\});</script>~sU', $html, $m), 'the page embeds RL_CONFIG');

        return json_decode($m[1], true);
    }

    // ----------------------------------------------------------------- students

    public function test_the_registration_page_no_longer_leaks_excluded_videos_or_raw_urls(): void
    {
        $s = $this->scenario();
        $student = $this->verified($s['students']['A']);
        $registration = Registration::where('student_id', $student->id)->first();

        $secret = $this->makeResource($s, $s['lesson1'], [], ['type' => 'link', 'title' => 'فيديو سري', 'url' => 'https://youtube.com/watch?v=SECRETVIDEO1']);
        $visible = $this->makeResource($s, $s['lesson2'], [], ['type' => 'link', 'title' => 'فيديو مسموح', 'url' => 'https://youtube.com/watch?v=ALLOWEDVIDEO']);
        $otherGroups = $this->makeResource($s, $s['lesson2'], [$s['groupB']], ['type' => 'link', 'title' => 'فيديو مجموعة أخرى', 'url' => 'https://youtube.com/watch?v=OTHERGROUPVID']);

        $this->exclude($secret, $s['subject']->id, [$student->id]);

        $html = $this->actingAs($student, 'student')
            ->get(route('student.registrations.show', $registration))
            ->assertOk()->getContent();

        $this->assertStringContainsString('فيديو مسموح', $html);
        $this->assertStringNotContainsString('فيديو سري', $html, 'an excluded video must not be listed');
        $this->assertStringNotContainsString('فيديو مجموعة أخرى', $html, 'another group\'s video must not be listed');
        $this->assertStringNotContainsString('SECRETVIDEO1', $html);
        $this->assertStringNotContainsString('youtube.com', $html, 'external links are never printed as raw hrefs');
    }

    public function test_excluding_a_student_from_a_unit_removes_it_from_every_student_page_and_every_direct_url(): void
    {
        $s = $this->scenario();
        $student = $this->verified($s['students']['A']);
        $video = $this->makeResource($s, $s['lesson1'], [], ['type' => 'link', 'title' => 'درس الوحدة', 'url' => 'https://youtu.be/abc123def45']);
        $this->exclude($s['unit'], $s['subject']->id, [$student->id]);

        $this->actingAs($student, 'student')->get(route('student.resources'))
            ->assertOk()->assertDontSee('درس الوحدة');

        $this->actingAs($student, 'student')->get(route('student.groups.show', $s['groupA']))
            ->assertOk()->assertDontSee('درس الوحدة');

        // the links the player calls: resolve / link / file / download / stream
        foreach (['student.resources.resolve', 'student.resources.link', 'student.resources.download'] as $route) {
            $this->actingAs($student, 'student')->get(route($route, $video))->assertForbidden();
        }

        // a classmate who is not excluded still gets everything
        $other = $this->verified($s['students']['B']);
        $this->actingAs($other, 'student')->get(route('student.resources'))->assertOk()->assertSee('درس الوحدة');
        $this->actingAs($other, 'student')->get(route('student.resources.resolve', $video))->assertOk()->assertJsonPath('kind', 'youtube');
    }

    public function test_a_switched_off_video_disappears_for_everyone_and_cannot_be_opened(): void
    {
        $s = $this->scenario();
        $student = $this->verified($s['students']['A']);
        $video = $this->makeResource($s, $s['lesson1'], [], ['type' => 'link', 'title' => 'موقوف الآن', 'url' => 'https://youtu.be/abc123def45']);

        $this->actingAs($student, 'student')->get(route('student.resources'))->assertSee('موقوف الآن');

        $video->update(['is_active' => false]);

        $this->actingAs($student, 'student')->get(route('student.resources'))->assertDontSee('موقوف الآن');
        $this->actingAs($student, 'student')->get(route('student.resources.resolve', $video))->assertNotFound();
    }

    public function test_unit_level_and_general_resources_show_on_the_student_pages(): void
    {
        $s = $this->scenario();
        $student = $this->verified($s['students']['A']);
        \App\Models\SubjectResource::factory()->forUnit($s['unit'])->create(['subject_id' => $s['subject']->id, 'is_shared' => true, 'title' => 'مورد على الوحدة', 'type' => 'link', 'url' => 'https://example.com/u']);
        $this->makeResource($s, null, [], ['title' => 'مورد عام', 'type' => 'link', 'url' => 'https://example.com/g']);

        $this->actingAs($student, 'student')->get(route('student.resources'))
            ->assertOk()->assertSee('مورد على الوحدة')->assertSee('مورد عام');

        $this->actingAs($student, 'student')->get(route('student.groups.show', $s['groupA']))
            ->assertOk()->assertSee('مورد على الوحدة')->assertSee('مورد عام');
    }

    // ------------------------------------------------------------------ teacher

    public function test_the_teacher_screens_render(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['title' => 'درس تجريبي للمعلم']);
        $teacher = $this->actingAs($s['teacher'], 'teacher');

        $teacher->get(route('teacher.content.index'))->assertOk();
        $teacher->get(route('teacher.content.hub'))->assertOk();
        $teacher->get(route('teacher.groups.show', $s['groupA']))->assertOk()->assertSee('درس تجريبي للمعلم');

        $manage = $teacher->get(route('teacher.content.manage', $s['subject']))->assertOk();
        $manage->assertSee('rl-app', false)->assertSee('rl-manager.js', false);
        $config = $this->configOf($manage->getContent());
        $this->assertSame('teacher', $config['role']);
        $this->assertSame('درس تجريبي للمعلم', $config['tree']['units'][0]['lessons'][0]['resources'][0]['title']);

        $library = $teacher->get(route('teacher.library.index'))->assertOk();
        $library->assertSee('rl-library.js', false)->assertSee('مكتبة الموارد');
    }

    public function test_a_teacher_cannot_open_the_manage_screen_of_a_subject_they_do_not_teach(): void
    {
        $s = $this->scenario();
        $stranger = \App\Models\Teacher::factory()->create();

        $this->actingAs($stranger, 'teacher')->get(route('teacher.content.manage', $s['subject']))->assertForbidden();
    }

    public function test_the_teacher_manage_screen_embeds_only_the_teachers_groups(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupC']], ['title' => 'مورد الزميل الخاص']);

        $html = $this->actingAs($s['teacher'], 'teacher')->get(route('teacher.content.manage', $s['subject']))->assertOk()->getContent();

        $this->assertStringNotContainsString('مورد الزميل الخاص', $html);
        $this->assertStringNotContainsString('"name":"C"', $html, 'a colleague\'s group is not even named');
    }

    // -------------------------------------------------------------------- admin

    private function adminClient(array $s)
    {
        foreach (['admin.resource-library.view', 'admin.resource-archive.view', 'admin.subject_content.view', 'admin.subject_content.edit'] as $name) {
            $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']));
        }

        return $this->actingAs($s['admin'], 'admin');
    }

    public function test_the_admin_screens_render_and_accept_both_kinds_of_subject_links(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [], ['title' => 'درس تجريبي للأدمن']);
        $admin = $this->adminClient($s);

        // new route key and the serialized Crypt::encrypt() id older links carry
        foreach ([$s['subject']->getRouteKey(), Crypt::encrypt($s['subject']->id)] as $key) {
            $page = $admin->get(route('subject_content.manage', $key))->assertOk()->assertSee('rl-app', false);
            $this->assertSame('درس تجريبي للأدمن', $this->configOf($page->getContent())['tree']['units'][0]['lessons'][0]['resources'][0]['title']);
        }

        $admin->get(route('resource-library.view'))->assertOk()->assertSee('rl-library.js', false);
        $admin->get(route('resource-library.view', ['subject_id' => Crypt::encrypt($s['subject']->id)]))->assertOk();
        $admin->get(route('resource-archive.view'))->assertOk();
    }

    public function test_the_archive_restores_and_destroys_through_the_content_manager(): void
    {
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $video->delete();
        $admin = $this->adminClient($s);

        $admin->post(route('resource-archive.restore', $video->id))->assertRedirect();
        $this->assertFalse($video->fresh()->trashed());
        $this->assertSame(1, $video->groups()->count(), 'audience came back with it');

        $video->delete();
        $admin->delete(route('resource-archive.force-delete', $video->id))->assertRedirect();
        $this->assertNull(\App\Models\SubjectResource::withTrashed()->find($video->id));
    }
}
