<?php

namespace Tests\Feature\ResourceLibrary;

use App\Services\ResourceLibrary\LibraryHealth;
use App\Services\ResourceLibrary\LibraryJournal;
use App\Services\ResourceLibrary\ResourceCatalog;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * A resource row whose file is gone from the disk (a database restored without its files): the staff see
 * it flagged, switch every such resource off in one undoable click, and the student gets a clear message.
 */
class MissingFilesTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('protected_videos');
    }

    private function stateOf(array $s, int $id, $actor = null): array
    {
        $tree = app(ResourceCatalog::class)->manage($actor ?? $s['actorAdmin'], $s['subject']);

        return collect($tree['units'][0]['lessons'])->flatMap(fn ($l) => $l['resources'])->firstWhere('id', $id);
    }

    private function issue(array $s, string $code, $actor = null): ?array
    {
        return collect(app(LibraryHealth::class)->scan($actor ?? $s['actorAdmin'], $s['subject'])['issues'])->firstWhere('code', $code);
    }

    public function test_the_tree_flags_a_resource_whose_file_is_gone_and_only_that_one(): void
    {
        $s = $this->scenario();
        $present = $this->makeResource($s, $s['lesson1'], [], ['url' => 'resources/here.mp4']);
        $missing = $this->makeResource($s, $s['lesson1'], [], ['url' => 'resources/gone.mp4']);
        $link = $this->makeResource($s, $s['lesson1'], [], ['type' => 'link', 'url' => 'https://youtu.be/abc123def45']);
        Storage::disk('protected_videos')->put('resources/here.mp4', 'bytes');

        $this->assertFalse($this->stateOf($s, $present->id)['file_missing']);
        $this->assertFalse($this->stateOf($s, $link->id)['file_missing'], 'a link has no file to lose');

        $state = $this->stateOf($s, $missing->id);
        $this->assertTrue($state['file_missing']);
        $this->assertContains('missing_file', $state['issues'], 'it counts in the "has problems" filter');
        $this->assertTrue(app(ResourceCatalog::class)->scopeState($s['actorAdmin'], $missing)['file_missing'], 'the scope drawer warns too');
    }

    public function test_one_click_switches_every_missing_file_off_and_one_undo_brings_them_back(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/gone-a.mp4']);
        $b = $this->makeResource($s, $s['lesson2'], [], ['url' => 'resources/gone-b.mp4']);
        $fine = $this->makeResource($s, $s['lesson2'], [], ['url' => 'resources/fine.mp4']);
        Storage::disk('protected_videos')->put('resources/fine.mp4', 'bytes');

        $issue = $this->issue($s, 'missing_file');
        $this->assertSame('deactivate_missing', $issue['fix']);
        $this->assertSame(2, $issue['count']);

        $result = app(LibraryHealth::class)->fix($s['actorAdmin'], $s['subject'], 'deactivate_missing');

        $this->assertSame(2, $result->data['fixed']);
        $this->assertFalse($a->fresh()->is_active);
        $this->assertFalse($b->fresh()->is_active);
        $this->assertTrue($fine->fresh()->is_active);
        $this->assertSame([$fine->id], $this->visibleIds($s, $s['students']['A']));

        // still listed (the file is still missing), but nothing left to switch off
        $this->assertNull($this->issue($s, 'missing_file')['fix']);

        app(LibraryJournal::class)->undo($s['actorAdmin'], $result->undoToken);
        $this->assertTrue($a->fresh()->is_active);
        $this->assertTrue($b->fresh()->is_active);
    }

    public function test_a_teacher_sharing_a_missing_file_with_others_only_pauses_their_own_groups(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupC']], ['url' => 'resources/gone.mp4']);

        $result = app(LibraryHealth::class)->fix($s['actorTeacher'], $s['subject'], 'deactivate_missing');

        $this->assertSame(1, $result->data['fixed']);
        $this->assertTrue($resource->fresh()->is_active, 'the colleague\'s group is untouched');
        $this->assertNotContains($resource->id, $this->visibleIds($s, $s['students']['A']));
        $this->assertContains($resource->id, $this->visibleIds($s, $s['students']['C']));
        $this->assertNull($this->issue($s, 'missing_file', $s['actorTeacher'])['fix'], 'nothing more the teacher can switch off');
    }

    public function test_the_student_gets_a_clear_message_instead_of_a_bare_404_and_staff_get_one_log_line(): void
    {
        $s = $this->scenario();
        $student = $s['students']['A'];
        $student->forceFill(['email_verified_at' => now()])->save();
        $video = $this->makeResource($s, $s['lesson1'], [], ['url' => 'resources/gone.mp4']);
        $pdf = $this->makeResource($s, $s['lesson1'], [], ['type' => 'document', 'url' => 'resources/gone.pdf', 'allow_download' => true]);

        Log::spy();

        $this->actingAs($student, 'student')->getJson(route('student.resources.resolve', $video))
            ->assertStatus(410)
            ->assertJsonPath('code', 'file_missing')
            ->assertJsonPath('message', 'هذا الملف غير متاح حالياً، تواصل مع المدرس.');

        $this->actingAs($student, 'student')->get(route('student.resources.file', $pdf), ['Referer' => url('/')])
            ->assertStatus(410)->assertJsonPath('code', 'file_missing');

        // a download link opened in the tab renders the page with the same message
        $this->actingAs($student, 'student')->get(route('student.resources.download', $pdf), ['Sec-Fetch-Mode' => 'navigate'])
            ->assertStatus(410)->assertSee('هذا الملف غير متاح حالياً');

        // a second hit on the same video does not log again
        $this->actingAs($student, 'student')->getJson(route('student.resources.resolve', $video))->assertStatus(410);

        Log::shouldHaveReceived('warning')->twice();
    }

    public function test_a_student_who_may_not_see_it_still_gets_forbidden_not_the_file_status(): void
    {
        $s = $this->scenario();
        $student = $s['students']['C'];
        $student->forceFill(['email_verified_at' => now()])->save();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/gone.mp4']);

        $this->actingAs($student, 'student')->getJson(route('student.resources.resolve', $video))->assertForbidden();
    }
}
