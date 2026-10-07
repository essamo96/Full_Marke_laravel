<?php

namespace Tests\Feature\ResourceLibrary;

use App\Console\Commands\LibraryCleanupIncoming;
use App\Services\ResourceLibrary\IncomingUploads;
use App\Services\ResourceLibrary\LibraryActor;
use App\Services\ResourceLibrary\LibraryHealth;
use App\Support\Library\UploadLimits;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\BuildsLibraryScenario;
use Tests\TestCase;

/**
 * Videos parked in incoming/ by the chunked uploader: who uploaded them is recorded, a cancelled form
 * deletes its own upload, an orphan can be linked back to a resource whose file is missing or turned
 * into a new resource, and the daily cleanup only removes what nobody claimed within the grace period.
 */
class IncomingUploadsTest extends TestCase
{
    use BuildsLibraryScenario;
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('protected_videos');
        Storage::fake(config('chunk-upload.storage.disk', 'local'));
    }

    private function t(string $name, array $params = []): string
    {
        return route('teacher.library.'.$name, $params);
    }

    private function asAdmin(array $s)
    {
        foreach (['admin.resource-library.view', 'admin.subject_content.edit'] as $name) {
            $s['admin']->givePermissionTo(Permission::firstOrCreate(['name' => $name, 'guard_name' => 'admin']));
        }

        return $this->actingAs($s['admin'], 'admin');
    }

    /** Park a file in incoming/ as if `$uploader` had just finished uploading it. */
    private function park(string $name, string $bytes, ?LibraryActor $uploader, string $original = 'lecture.mp4'): string
    {
        $path = 'incoming/'.$name;
        Storage::disk('protected_videos')->put($path, $bytes);
        app(IncomingUploads::class)->register($path, $original, $uploader);

        return $path;
    }

    /** @return array<string, mixed> */
    private function chunk(string $filename, int $totalSize, int $bytes = 2048): array
    {
        // a plain temp file: UploadedFile::fake() sits on tmpfile(), which Windows deletes once pion moves it
        $tmp = tempnam(sys_get_temp_dir(), 'chunk');
        file_put_contents($tmp, str_repeat('v', $bytes));

        return [
            'file' => new UploadedFile($tmp, $filename, 'video/mp4', null, true),
            'resumableChunkNumber' => 1,
            'resumableTotalChunks' => 1,
            'resumableChunkSize' => 5 * 1024 * 1024,
            'resumableCurrentChunkSize' => $bytes,
            'resumableTotalSize' => $totalSize,
            'resumableType' => 'video/mp4',
            'resumableIdentifier' => $totalSize.'-'.preg_replace('/\W/', '', $filename),
            'resumableFilename' => $filename,
            'resumableRelativePath' => $filename,
        ];
    }

    public function test_a_finished_chunked_upload_is_parked_with_who_uploaded_it(): void
    {
        $s = $this->scenario();

        $response = $this->actingAs($s['teacher'], 'teacher')
            ->post($this->t('upload-chunk'), $this->chunk('محاضرة 1.mp4', 2048), ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('done', 100);

        $path = $response->json('path');
        $this->assertStringStartsWith('incoming/', $path);
        Storage::disk('protected_videos')->assertExists($path);

        $meta = app(IncomingUploads::class)->meta($path);
        $this->assertSame('محاضرة 1.mp4', $meta['original_filename']);
        $this->assertSame(LibraryActor::TEACHER, $meta['uploaded_by_type']);
        $this->assertSame($s['teacher']->id, $meta['uploaded_by_id']);
    }

    public function test_a_file_bigger_than_the_limit_is_refused_on_its_first_chunk(): void
    {
        $s = $this->scenario();
        config(['resource_library.upload.max_video_bytes' => 10 * 1024 * 1024]);

        $this->actingAs($s['teacher'], 'teacher')
            ->post($this->t('upload-chunk'), $this->chunk('huge.mp4', 50 * 1024 * 1024), ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonPath('errors.file.0', 'حجم الملف أكبر من الحد المسموح (10 MB).');

        $this->assertSame([], Storage::disk('protected_videos')->files('incoming'));
    }

    public function test_closing_the_form_deletes_its_own_upload_but_never_another_one(): void
    {
        $s = $this->scenario();
        $mine = $this->park('mine.mp4', 'aaaa', $s['actorTeacher']);
        $theirs = $this->park('theirs.mp4', 'bbbb', $s['actorColleague']);
        $used = $this->park('used.mp4', 'cccc', $s['actorTeacher']);
        $this->makeResource($s, $s['lesson1'], [], ['url' => $used]);
        Storage::disk('protected_videos')->put('resources/kept.mp4', 'dddd');

        $client = $this->actingAs($s['teacher'], 'teacher');

        $client->deleteJson($this->t('upload-chunk.discard'), ['path' => $mine])->assertOk();
        Storage::disk('protected_videos')->assertMissing([$mine, $mine.IncomingUploads::SIDECAR]);

        $client->deleteJson($this->t('upload-chunk.discard'), ['path' => $theirs])->assertForbidden();
        $client->deleteJson($this->t('upload-chunk.discard'), ['path' => $used])->assertStatus(422);
        $client->deleteJson($this->t('upload-chunk.discard'), ['path' => 'incoming/../resources/kept.mp4'])->assertStatus(422);
        $client->deleteJson($this->t('upload-chunk.discard'), ['path' => $theirs.IncomingUploads::SIDECAR])->assertNotFound();

        Storage::disk('protected_videos')->assertExists([$theirs, $used, 'resources/kept.mp4']);
    }

    public function test_orphans_are_listed_for_their_uploader_with_the_resource_whose_file_they_match(): void
    {
        $s = $this->scenario();
        $lost = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/gone.mp4', 'size_bytes' => 5, 'original_filename' => 'other-name.mp4']);
        $byName = $this->makeResource($s, $s['lesson2'], [$s['groupA']], ['url' => 'resources/gone2.mp4', 'size_bytes' => 999, 'original_filename' => 'Lecture.MP4']);
        $notTheirs = $this->makeResource($s, $s['lesson2'], [$s['groupC']], ['url' => 'resources/gone3.mp4', 'size_bytes' => 5]);
        $mine = $this->park('mine.mp4', '12345', $s['actorTeacher'], 'lecture.mp4');
        $this->park('theirs.mp4', 'xx', $s['actorColleague']);

        $response = $this->actingAs($s['teacher'], 'teacher')->getJson($this->t('uploads.orphans'))->assertOk();

        $this->assertSame([$mine], array_column($response->json('uploads'), 'path'), 'a teacher only sees their own uploads');
        $suggested = array_column($response->json('uploads.0.suggestions'), 'id');
        sort($suggested);
        $this->assertSame([$lost->id, $byName->id], $suggested, 'same size, or same original name - never a resource the teacher cannot edit');
        $this->assertSame($s['teacher']->name, $response->json('uploads.0.uploaded_by'));
        $this->assertEqualsCanonicalizing([$lost->id, $byName->id], array_column($response->json('missing'), 'id'));
        $this->assertNotContains($notTheirs->id, array_column($response->json('missing'), 'id'));

        $this->assertCount(2, $this->asAdmin($s)->getJson(route('library.uploads.orphans'))->assertOk()->json('uploads'), 'the admin sees every orphan');

        $this->actingAs($s['teacher'], 'teacher')->get($this->t('uploads.orphans.file').'?path='.urlencode($mine))->assertOk();
    }

    public function test_relinking_an_orphan_gives_the_resource_its_file_back(): void
    {
        $s = $this->scenario();
        $resource = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/gone.mp4', 'is_active' => false]);
        $present = $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/here.mp4']);
        Storage::disk('protected_videos')->put('resources/here.mp4', 'here');
        $path = $this->park('found.mp4', 'video-bytes', $s['actorTeacher'], 'found.mp4');

        $client = $this->actingAs($s['teacher'], 'teacher');

        $client->postJson($this->t('uploads.orphans.relink'), ['path' => $path, 'resource' => $present->getRouteKey()])
            ->assertStatus(422)->assertJsonPath('code', 'file_present');

        $client->postJson($this->t('uploads.orphans.relink'), ['path' => $path, 'resource' => $resource->getRouteKey()])
            ->assertOk()
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'ما زال موقوفاً'));

        $resource->refresh();
        $this->assertStringStartsWith('resources/', $resource->url);
        Storage::disk('protected_videos')->assertExists($resource->url);
        Storage::disk('protected_videos')->assertMissing([$path, $path.IncomingUploads::SIDECAR]);
        $this->assertSame('found.mp4', $resource->original_filename);
        $this->assertSame(11, (int) $resource->size_bytes);
        $this->assertSame([], app(IncomingUploads::class)->orphans($s['actorAdmin']));
    }

    public function test_adopting_an_orphan_as_a_new_resource_moves_it_and_drops_its_sidecar(): void
    {
        $s = $this->scenario();
        $path = $this->park('adopt.mp4', 'video', $s['actorTeacher'], 'adopt.mp4');

        $this->actingAs($s['teacher'], 'teacher')->postJson($this->t('resources.store', ['subject' => $s['subject']->getRouteKey()]), [
            'title' => 'من ملف مرفوع',
            'type' => 'video',
            'uploaded_path' => $path,
            'original_filename' => 'adopt.mp4',
            'targets' => [['type' => 'lesson', 'key' => $s['lesson1']->getRouteKey()]],
        ])->assertOk();

        Storage::disk('protected_videos')->assertMissing([$path, $path.IncomingUploads::SIDECAR]);
        $this->assertSame([], app(IncomingUploads::class)->orphanPaths()->all());
    }

    public function test_the_health_check_reports_orphan_uploads_to_whoever_can_handle_them(): void
    {
        $s = $this->scenario();
        $this->makeResource($s, $s['lesson1'], [$s['groupA']], ['url' => 'resources/gone.mp4', 'size_bytes' => 5]);
        $this->park('mine.mp4', '12345', $s['actorTeacher']);

        $issue = fn ($actor) => collect(app(LibraryHealth::class)->scan($actor, $s['subject'])['issues'])->firstWhere('code', 'orphan_uploads');

        $this->assertSame(1, $issue($s['actorTeacher'])['count']);
        $this->assertStringContainsString('يطابق مورداً ملفه مفقود', $issue($s['actorTeacher'])['items'][0]['detail']);
        $this->assertNull($issue($s['actorColleague']), 'not the colleague\'s upload');
        $this->assertSame(1, $issue($s['actorAdmin'])['count']);
    }

    public function test_the_cleanup_only_deletes_what_nobody_claimed_within_the_grace_period(): void
    {
        $s = $this->scenario();
        $disk = Storage::disk('protected_videos');

        $old = $this->park('old.mp4', 'old', $s['actorTeacher']);
        $disk->put($old.IncomingUploads::SIDECAR, json_encode(['uploaded_at' => now()->subDays(8)->toIso8601String()]));
        touch($disk->path($old), now()->subDays(8)->getTimestamp());

        $fresh = $this->park('fresh.mp4', 'fresh', $s['actorTeacher']);

        $legacy = 'incoming/legacy.mp4';
        $disk->put($legacy, 'from before sidecars');
        touch($disk->path($legacy), now()->subDays(30)->getTimestamp());

        $used = $this->park('used.mp4', 'used', $s['actorTeacher']);
        $disk->put($used.IncomingUploads::SIDECAR, json_encode(['uploaded_at' => now()->subDays(30)->toIso8601String()]));
        touch($disk->path($used), now()->subDays(30)->getTimestamp());
        $this->makeResource($s, $s['lesson1'], [], ['url' => $used]);

        $disk->put('incoming/vanished.mp4'.IncomingUploads::SIDECAR, '{}');

        $this->artisan('library:cleanup-incoming', ['--dry-run' => true])->assertSuccessful();
        $disk->assertExists($old);

        $this->artisan('library:cleanup-incoming')->assertSuccessful();

        $disk->assertMissing([$old, $old.IncomingUploads::SIDECAR, 'incoming/vanished.mp4'.IncomingUploads::SIDECAR]);
        $disk->assertExists([$fresh, $used]);
        $this->assertTrue($disk->exists($legacy), 'a file from before sidecars gets a full grace period from its first sighting');
        $this->assertArrayHasKey('first_seen_at', app(IncomingUploads::class)->meta($legacy));
        $this->assertNotNull(Cache::get(LibraryCleanupIncoming::LAST_RUN_KEY));
    }

    public function test_the_page_gets_the_chunk_size_php_accepts_and_ping_renews_the_token(): void
    {
        $s = $this->scenario();

        $page = $this->actingAs($s['teacher'], 'teacher')->get(route('teacher.library.index'))->assertOk();
        $this->assertStringContainsString('"chunk_bytes":'.UploadLimits::chunkBytes(), $page->getContent());

        $this->actingAs($s['teacher'], 'teacher')->getJson($this->t('ping'))
            ->assertOk()
            ->assertJsonPath('csrf', fn ($token) => is_string($token) && strlen($token) >= 40);

        $this->getJson(route('library.ping'))->assertUnauthorized();
    }

    public function test_php_size_strings_are_read_and_the_chunk_never_exceeds_the_server_limit(): void
    {
        $this->assertSame(2 * 1024 ** 2, UploadLimits::parseBytes('2M'));
        $this->assertSame(1024 ** 3, UploadLimits::parseBytes('1G'));
        $this->assertSame(512 * 1024, UploadLimits::parseBytes('512k'));
        $this->assertSame(1500, UploadLimits::parseBytes('1500'));
        $this->assertNull(UploadLimits::parseBytes('0'));
        $this->assertNull(UploadLimits::parseBytes('-1'));

        config(['resource_library.upload.chunk_bytes' => 100 * 1024 ** 3]);
        $server = UploadLimits::serverRequestBytes();
        $this->assertSame($server === null ? 100 * 1024 ** 3 : $server - UploadLimits::MARGIN_BYTES, UploadLimits::chunkBytes());
    }
}
