<?php

namespace Tests\Feature\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\SubjectResource;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Creating and editing content through the shared API: units, lessons, links, uploaded files,
 * uploaded-in-chunks videos, the preview stream - and the audience each one starts with.
 */
class LibraryContentApiTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('protected_videos');
    }

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

    private function t(string $name, array $params = []): string
    {
        return route('teacher.library.'.$name, $params);
    }

    public function test_a_teacher_builds_a_unit_a_lesson_and_a_video_link_and_students_of_the_group_see_it(): void
    {
        $s = $this->scenario();
        $subjectKey = $s['subject']->getRouteKey();

        $unitKey = $this->asTeacher($s)
            ->postJson($this->t('units.store', ['subject' => $subjectKey]), ['name_ar' => 'وحدة جديدة', 'audience_mode' => 'groups', 'group_ids' => [$s['groupA']->id]])
            ->assertOk()->json('key');

        $lessonKey = $this->asTeacher($s)
            ->postJson($this->t('lessons.store', ['key' => $unitKey]), ['name_ar' => 'درس جديد', 'audience_mode' => 'groups', 'group_ids' => [$s['groupA']->id]])
            ->assertOk()->json('key');

        // no explicit audience: it inherits the lesson's declared scope (group A), not "everyone"
        $this->asTeacher($s)
            ->postJson($this->t('resources.store', ['subject' => $subjectKey]), [
                'title' => 'شرح', 'type' => 'link', 'url' => 'https://youtu.be/abc123def45',
                'targets' => [['type' => 'lesson', 'key' => $lessonKey]],
            ])->assertOk()->assertJsonStructure(['key']);

        $resource = SubjectResource::where('title', 'شرح')->first();
        $this->assertFalse($resource->is_shared);
        $this->assertSame([$s['groupA']->id], $resource->groups()->pluck('groups.id')->all());
        $this->assertSame('teacher', $resource->created_by_type);
        $this->assertSame([$resource->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertSame([], $this->visibleIds($s, $s['students']['B']));
    }

    public function test_the_quick_video_upload_no_longer_leaks_to_every_group(): void
    {
        // The old quick-upload posted no audience at all, which silently meant "shared with every
        // group of the subject" - including other teachers' groups.
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id]);

        Storage::disk('protected_videos')->put('incoming/'.($name = 'abc.mp4'), 'fake-video-bytes');

        $this->asTeacher($s)->postJson($this->t('resources.store', ['subject' => $s['subject']->getRouteKey()]), [
            'title' => 'فيديو سريع', 'type' => 'video', 'uploaded_path' => 'incoming/'.$name, 'original_filename' => 'lecture.mp4',
            'targets' => [['type' => 'lesson', 'key' => $lesson->getRouteKey()]],
        ])->assertOk();

        $resource = SubjectResource::where('title', 'فيديو سريع')->first();
        $this->assertFalse($resource->is_shared);
        $this->assertSame([$s['groupA']->id], $resource->groups()->pluck('groups.id')->all());
        $this->assertSame([], $this->visibleIds($s, $s['students']['C']), 'the colleague\'s group must not receive it');

        // file metadata for the central repository
        $this->assertSame(16, (int) $resource->size_bytes);
        $this->assertNotNull($resource->content_hash);
        $this->assertSame('lecture.mp4', $resource->original_filename);
        $this->assertFalse(Storage::disk('protected_videos')->exists('incoming/'.$name), 'the chunk was moved into resources/');
        $this->assertTrue(Storage::disk('protected_videos')->exists($resource->url));
    }

    public function test_a_direct_pdf_upload_is_stored_and_previewable_by_the_teacher_only(): void
    {
        $s = $this->scenario();

        $this->asTeacher($s)->post($this->t('resources.store', ['subject' => $s['subject']->getRouteKey()]), [
            'title' => 'واجب', 'type' => 'document',
            'file' => UploadedFile::fake()->createWithContent('homework.pdf', "%PDF-1.4\n".str_repeat('x', 4096)),
            'targets' => [['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()]],
        ], ['Accept' => 'application/json'])->assertOk();

        $resource = SubjectResource::where('title', 'واجب')->first();
        $this->assertSame('homework.pdf', $resource->original_filename);
        $this->assertGreaterThan(0, $resource->size_bytes);

        $this->asTeacher($s)->get($this->t('resources.file', ['key' => $resource->getRouteKey()]))
            ->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');

        $colleague = $this->actingAs($s['colleague'], 'teacher');
        // the colleague has no group in this resource's audience (it is shared by the lesson default) -> make it private
        $resource->forceFill(['is_shared' => false])->save();
        $resource->groups()->sync([$s['groupA']->id]);
        $colleague->get($this->t('resources.file', ['key' => $resource->getRouteKey()]))->assertNotFound();
    }

    public function test_editing_a_resource_keeps_its_audience_and_exclusions(): void
    {
        // The old edit form re-saved the audience and exclusions it was loaded with, so a stale
        // form silently wiped exclusions. Editing the title now touches nothing else.
        $s = $this->scenario();
        $video = $this->makeResource($s, $s['lesson1'], [$s['groupA'], $s['groupB']], ['type' => 'link', 'url' => 'https://youtu.be/aaaaaaaaaaa']);
        $this->exclude($video, $s['subject']->id, [$s['students']['A']->id]);

        $this->asTeacher($s)->putJson($this->t('update', ['type' => 'resources', 'key' => $video->getRouteKey()]), [
            'title' => 'عنوان جديد', 'description' => 'وصف', 'allow_download' => true,
        ])->assertOk();

        $fresh = $video->fresh();
        $this->assertSame('عنوان جديد', $fresh->title);
        $this->assertTrue($fresh->allow_download);
        $this->assertSame(2, $fresh->groups()->count());
        $this->assertSame(1, $fresh->contentExclusions()->count());
    }

    public function test_renaming_a_unit_and_a_lesson(): void
    {
        $s = $this->scenario();

        $this->asTeacher($s)->putJson($this->t('update', ['type' => 'units', 'key' => $s['unit']->getRouteKey()]), ['name_ar' => 'اسم جديد'])
            ->assertForbidden(); // the unit is shared with the colleague's group too

        $this->asAdmin($s)->putJson(route('library.update', ['type' => 'units', 'key' => $s['unit']->getRouteKey()]), ['name_ar' => 'اسم جديد'])->assertOk();
        $this->assertSame('اسم جديد', $s['unit']->fresh()->name_ar);

        $this->asAdmin($s)->putJson(route('library.update', ['type' => 'lessons', 'key' => $s['lesson1']->getRouteKey()]), ['name_ar' => 'درس معدّل', 'name_en' => 'Edited'])->assertOk();
        $this->assertSame('Edited', $s['lesson1']->fresh()->name_en);
    }

    public function test_a_resource_can_be_created_in_several_places_at_once(): void
    {
        $s = $this->scenario();

        $this->asAdmin($s)->postJson(route('library.resources.store', ['subject' => $s['subject']->getRouteKey()]), [
            'title' => 'مراجعة', 'type' => 'link', 'url' => 'https://example.com/review',
            'targets' => [
                ['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()],
                ['type' => 'lesson', 'key' => $s['lesson2']->getRouteKey()],
                ['type' => 'unit', 'key' => $s['unit']->getRouteKey()],
                ['type' => 'general'],
            ],
        ])->assertOk();

        $resource = SubjectResource::where('title', 'مراجعة')->first();
        $this->assertCount(4, $resource->placements);
        $this->assertSame(1, SubjectResource::where('title', 'مراجعة')->count(), 'one upload, four places');
    }

    public function test_validation_errors_come_back_as_json_422(): void
    {
        $s = $this->scenario();

        $this->asTeacher($s)->postJson($this->t('resources.store', ['subject' => $s['subject']->getRouteKey()]), ['title' => ''])
            ->assertStatus(422)->assertJsonValidationErrors(['title', 'type']);

        $this->asTeacher($s)->postJson($this->t('units.store', ['subject' => $s['subject']->getRouteKey()]), [])
            ->assertStatus(422)->assertJsonValidationErrors(['name_ar']);
    }

    public function test_deleting_a_lesson_through_the_api_can_be_undone(): void
    {
        $s = $this->scenario();
        $lesson = EducationalLesson::factory()->create(['educational_unit_id' => $s['unit']->id, 'is_shared' => false]);
        $lesson->groups()->sync([$s['groupA']->id]);
        $video = $this->makeResource($s, $lesson, [$s['groupA']]);

        $delete = $this->asTeacher($s)->deleteJson($this->t('destroy', ['type' => 'lessons', 'key' => $lesson->getRouteKey()]))->assertOk();
        $this->assertSame([], $this->visibleIds($s, $s['students']['A']));
        $this->assertTrue(SubjectResource::withTrashed()->find($video->id)->trashed());

        $this->asTeacher($s)->postJson($this->t('undo'), ['token' => $delete->json('undo.token')])->assertOk();
        $this->assertSame([$video->id], $this->visibleIds($s, $s['students']['A']));
        $this->assertFalse(EducationalLesson::withTrashed()->find($lesson->id)->trashed());
        $this->assertNotNull(EducationalUnit::find($s['unit']->id));
    }

    public function test_reordering_resources_and_lessons(): void
    {
        $s = $this->scenario();
        $a = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);
        $b = $this->makeResource($s, $s['lesson1'], [$s['groupA']]);

        $this->asTeacher($s)->postJson($this->t('reorder.resources', ['subject' => $s['subject']->getRouteKey()]), [
            'container' => ['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()],
            'order' => [$b->getRouteKey(), $a->getRouteKey()],
        ])->assertOk();

        $this->assertSame([$b->id, $a->id], $s['lesson1']->fresh()->resources->pluck('id')->all());

        $this->asAdmin($s)->postJson(route('library.reorder.containers', ['subject' => $s['subject']->getRouteKey(), 'type' => 'lessons']), [
            'order' => [$s['lesson2']->getRouteKey(), $s['lesson1']->getRouteKey()],
        ])->assertOk();
        $this->assertSame([$s['lesson2']->id, $s['lesson1']->id], $s['unit']->fresh()->lessons->pluck('id')->all());
    }
}
