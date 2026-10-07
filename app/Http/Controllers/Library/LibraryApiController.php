<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\Controller;
use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\ResourceAuditLog;
use App\Models\SubjectResource;
use App\Services\ResourceLibrary\ContentManager;
use App\Services\ResourceLibrary\ExclusionManager;
use App\Services\ResourceLibrary\IncomingUploads;
use App\Services\ResourceLibrary\LibraryActor;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryHealth;
use App\Services\ResourceLibrary\LibraryJournal;
use App\Services\ResourceLibrary\LibraryResult;
use App\Services\ResourceLibrary\LibraryTargets;
use App\Services\ResourceLibrary\PlacementManager;
use App\Services\ResourceLibrary\ResourceCatalog;
use App\Services\ResourceLibrary\ResourceExplainer;
use App\Services\ResourceLibrary\VisibilityManager;
use App\Services\StudentContentGate;
use App\Support\RouteKey;
use App\Traits\HandlesChunkedUploads;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The JSON API of the resource library - ONE implementation, mounted twice:
 * under /admin/library for admins and /teacher/library for teachers.
 *
 * Subclasses only say who is acting (actor()). Everything else - permissions, undo, the rules
 * about shared content - lives in the services, so an admin and a teacher get identical behaviour
 * and a teacher is automatically fenced into their own groups and students.
 */
abstract class LibraryApiController extends Controller
{
    use HandlesChunkedUploads;

    public function __construct(
        protected readonly ResourceCatalog $catalog,
        protected readonly VisibilityManager $visibility,
        protected readonly PlacementManager $placements,
        protected readonly ExclusionManager $exclusions,
        protected readonly ContentManager $content,
        protected readonly LibraryJournal $journal,
        protected readonly LibraryHealth $health,
        protected readonly LibraryTargets $targets,
        protected readonly ResourceExplainer $explainer,
        protected readonly StudentContentGate $gate,
    ) {}

    abstract protected function actor(): LibraryActor;

    // ------------------------------------------------------------------- reading

    /** The manage tree of a subject (units > lessons > resources), optionally one group's tab. */
    public function tree(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);

        $tree = $this->catalog->manage(
            $actor,
            $subject,
            $request->filled('group') ? (int) $request->query('group') : null,
            $request->only(['q', 'type', 'status']),
        );

        return response()->json(['success' => true, 'tree' => $tree]);
    }

    /** The flat library: every resource the actor manages, across subjects. */
    public function rows(Request $request): JsonResponse
    {
        $actor = $this->actor();
        $filters = $request->only(['group', 'type', 'state', 'q']);
        $filters['trashed'] = $request->boolean('trashed');

        if ($request->filled('subject')) {
            $subjectId = (int) $request->query('subject');
            if (! $actor->canAccessSubject($subjectId)) {
                throw LibraryException::forbidden();
            }
            $filters['subject'] = $subjectId;
        }

        return response()->json(['success' => true] + $this->catalog->library($actor, $filters));
    }

    /** Everything the "who sees it / where is it shown" drawer needs. */
    public function scope(string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $target = $this->targets->resolve($actor, $type, $key);

        return response()->json(['success' => true, 'scope' => $this->catalog->scopeState($actor, $target)]);
    }

    /** Rows of the students table (the exclusion switches). */
    public function students(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $target = $this->targets->resolve($actor, $type, $key);

        return response()->json([
            'success' => true,
            'students' => $this->exclusions->students($actor, $target, $request->boolean('everyone')),
        ]);
    }

    /** Students of a subject the actor may preview / exclude (picker data). */
    public function subjectStudents(string $subject): JsonResponse
    {
        $actor = $this->actor();

        return response()->json([
            'success' => true,
            'students' => $this->catalog->subjectStudents($actor, $this->targets->subject($actor, $subject)),
        ]);
    }

    public function trash(string $subject): JsonResponse
    {
        $actor = $this->actor();

        return response()->json(['success' => true] + $this->catalog->trash($actor, $this->targets->subject($actor, $subject)));
    }

    /** Recent journaled actions of a subject, with whether each can still be undone. */
    public function activity(string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);
        $window = (int) config('resource_library.undo.window_seconds', 900);

        $entries = ResourceAuditLog::forSubject($subject->id)
            ->when($actor->isTeacher(), fn ($q) => $q->where('actor_type', $actor->type)->where('actor_id', $actor->id()))
            ->whereNotNull('summary')
            ->latest('id')->limit(40)->get()
            ->map(fn ($log) => [
                'token' => $log->uuid,
                'summary' => $log->summary,
                'actor' => $log->actor_type === LibraryActor::ADMIN ? 'الإدارة' : 'معلم',
                'at' => $log->created_at?->format('Y-m-d H:i'),
                'undone' => $log->isUndone(),
                'undoable' => ! $log->isUndone() && ! empty($log->before)
                    && ($actor->isAdmin() || $actor->is($log->actor_type, $log->actor_id))
                    && $log->created_at->diffInSeconds(now()) <= $window,
            ])->values();

        return response()->json(['success' => true, 'entries' => $entries]);
    }

    /** What a student (or a whole group) actually sees of a subject. */
    public function preview(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);

        $request->validate(['student_id' => 'nullable|integer', 'group_id' => 'nullable|integer']);

        if ($request->filled('student_id')) {
            $studentId = (int) $request->input('student_id');
            $pool = $actor->studentIdsFor($subject->id);
            if ($pool !== null && ! in_array($studentId, $pool, true)) {
                throw LibraryException::forbidden('يمكنك معاينة طلاب مجموعاتك فقط.');
            }
            $tree = $this->gate->tree($studentId, $subject);
            $who = $this->targets->student($studentId)->name;
        } else {
            $groupId = (int) $request->input('group_id');
            if (! $groupId || ! $actor->canManageGroup($subject->id, $groupId)) {
                throw LibraryException::forbidden('اختر طالباً أو مجموعة تتبعك.');
            }
            $tree = $this->gate->tree(0, $subject, [$groupId]);
            $who = 'مجموعة «'.$this->targets->group($groupId)->name.'»';
        }

        $resource = fn ($r) => ['id' => (int) $r->id, 'title' => $r->title, 'type' => $r->type, 'icon' => 'bi-'.($r->type === 'video' ? 'play-circle-fill' : ($r->type === 'document' ? 'file-earmark-text-fill' : ($r->type === 'image' ? 'image-fill' : 'link-45deg')))];

        return response()->json([
            'success' => true,
            'who' => $who,
            'general' => $tree['general']->map($resource)->values(),
            'units' => $tree['units']->map(fn ($unit) => [
                'name' => $unit->name_ar,
                'resources' => $unit->directResources->map($resource)->values(),
                'lessons' => $unit->lessons->map(fn ($lesson) => [
                    'name' => $lesson->name_ar,
                    'resources' => $lesson->resources->map($resource)->values(),
                ])->values(),
            ])->values(),
        ]);
    }

    /** Why does (or doesn't) a student see a resource? */
    public function explain(Request $request): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['resource' => 'required|string', 'student_id' => 'required|integer|exists:students,id']);

        $resource = $this->targets->resolve($actor, 'resources', $data['resource']);

        return response()->json(['success' => true] + $this->explainer->explain(
            $actor, $resource, $this->targets->student((int) $data['student_id'])
        ));
    }

    // ----------------------------------------------------------- creating & editing

    /** New unit. `audience` = ['mode' => inherit|shared|groups, 'group_ids' => []] (inherit = everything the actor reaches). */
    public function storeUnit(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);
        $data = $request->validate(array_merge($this->nameRules(), $this->audienceRules()));

        $unit = $this->content->createUnit($actor, $subject, $data, $this->audienceFrom($data));
        $this->journal->note($actor, 'unit.create', 'إضافة وحدة «'.$unit->name_ar.'»', $subject->id, $unit);

        return response()->json(['success' => true, 'message' => 'تمت إضافة الوحدة.', 'key' => $unit->getRouteKey()]);
    }

    public function storeLesson(Request $request, string $key): JsonResponse
    {
        $actor = $this->actor();
        $unit = $this->targets->resolve($actor, 'units', $key);
        $data = $request->validate(array_merge($this->nameRules(), $this->audienceRules()));

        $lesson = $this->content->createLesson($actor, $unit, $data, $this->audienceFrom($data));
        $this->journal->note($actor, 'lesson.create', 'إضافة درس «'.$lesson->name_ar.'»', $this->targets->subjectIdOf($lesson), $lesson);

        return response()->json(['success' => true, 'message' => 'تمت إضافة الدرس.', 'key' => $lesson->getRouteKey()]);
    }

    /** New resource: one upload, shown in any number of places, to the chosen audience. */
    public function storeResource(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);

        $data = $request->validate(array_merge($this->resourceRules(), $this->audienceRules(), [
            'targets' => 'nullable|array',
            'targets.*.type' => 'required|string|in:lesson,unit,general',
            'targets.*.key' => 'nullable|string',
            'excluded_student_ids' => 'nullable|array',
            'excluded_student_ids.*' => 'integer',
        ]));

        $result = $this->content->createResource(
            $actor,
            $subject,
            $data,
            $this->targets->placements($actor, $data['targets'] ?? []),
            $this->audienceFrom($data),
            $request->file('file'),
            $data['excluded_student_ids'] ?? [],
        );

        return response()->json(['success' => true, 'message' => $result->message, 'key' => $result->data['resource']->getRouteKey()]);
    }

    /** Edit a resource (title, description, link, replacement file), or rename a unit / lesson. */
    public function update(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $target = $this->targets->resolve($actor, $type, $key);

        if ($target instanceof SubjectResource) {
            $data = $request->validate($this->resourceRules(updating: true));
            $result = $this->content->updateResource($actor, $target, $data, $request->file('file'));

            return response()->json(['success' => true, 'message' => $result->message]);
        }

        $data = $request->validate($this->nameRules());
        $target instanceof EducationalUnit
            ? $this->content->updateUnit($actor, $target, $data)
            : $this->content->updateLesson($actor, $target, $data);

        return response()->json(['success' => true, 'message' => 'تم الحفظ.']);
    }

    /**
     * Stream the stored file for the admin / teacher preview (Range requests supported, so a
     * video can be scrubbed). Students never use this: they go through StudentContentGate.
     */
    public function file(string $key)
    {
        $actor = $this->actor();
        $resource = $this->targets->resolve($actor, 'resources', $key);

        abort_if($resource->isExternalLink() || ! $resource->url, 404);
        $files = app(\App\Services\ResourceLibrary\ResourceFileStore::class);
        abort_unless($files->exists($resource), 404);

        $headers = [
            'Content-Disposition' => 'inline; filename="'.addslashes($resource->original_filename ?: basename($resource->url)).'"',
            // rendered inside the page only: keep it out of caches and other sites' frames
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'same-origin',
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
        ];

        if ($resource->isVideo()) {
            $headers['Content-Type'] = 'video/mp4';
            $headers['Accept-Ranges'] = 'bytes';
        }

        return response()->file($files->disk()->path($resource->url), $headers);
    }

    /** One chunk of a resumable video upload (see HandlesChunkedUploads). */
    /** Keeps the session alive while a long upload runs and hands back its current CSRF token. */
    public function ping(): JsonResponse
    {
        $this->actor();

        return response()->json(['success' => true, 'csrf' => csrf_token()]);
    }

    public function uploadChunk(Request $request)
    {
        $actor = $this->actor();

        $response = $this->receiveChunkedUpload($request);

        $data = $response->getData(true);
        if (! empty($data['path'])) {
            $this->uploads()->register($data['path'], $data['original_filename'] ?? null, $actor);
        }

        return $response;
    }

    /** The resource form was closed without saving: drop the video it had already uploaded. */
    public function discardUpload(Request $request): JsonResponse
    {
        $data = $request->validate(['path' => 'required|string|max:255']);

        return $this->json($this->uploads()->discard($this->actor(), $data['path']));
    }

    // ------------------------------------------------------- orphan uploads

    /** Uploads parked in incoming/ that no resource uses, plus the resources whose file is missing. */
    public function orphanUploads(): JsonResponse
    {
        $actor = $this->actor();
        $uploads = $this->uploads();

        return response()->json([
            'success' => true,
            'uploads' => $uploads->orphans($actor),
            'missing' => $uploads->missingResources($actor)->map(fn ($r) => $uploads->resourceOption($r))->values(),
            'grace_hours' => (int) config('resource_library.upload.orphan_grace_hours'),
        ]);
    }

    /** Play / view an orphan upload so whoever handles it can recognise it (Range supported). */
    public function orphanUploadFile(Request $request)
    {
        $data = $request->validate(['path' => 'required|string|max:255']);
        $absolute = $this->uploads()->absolutePath($this->actor(), $data['path']);

        return response()->file($absolute, [
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function relinkOrphanUpload(Request $request): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['path' => 'required|string|max:255', 'resource' => 'required|string']);
        $resource = $this->targets->resolve($actor, 'resources', $data['resource']);

        return $this->json($this->uploads()->relink($actor, $resource, $data['path']));
    }

    public function discardOrphanUpload(Request $request): JsonResponse
    {
        $data = $request->validate(['path' => 'required|string|max:255']);

        return $this->json($this->uploads()->discard($this->actor(), $data['path']));
    }

    private function uploads(): IncomingUploads
    {
        return app(IncomingUploads::class);
    }

    /** @return array<string, mixed> */
    private function nameRules(): array
    {
        return ['name_ar' => 'required|string|max:255', 'name_en' => 'nullable|string|max:255'];
    }

    /** @return array<string, mixed> */
    private function audienceRules(): array
    {
        return [
            'audience_mode' => 'nullable|string|in:inherit,shared,groups',
            'group_ids' => 'nullable|array',
            'group_ids.*' => 'integer',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{mode: string, group_ids: array<int>}
     */
    private function audienceFrom(array $data): array
    {
        return ['mode' => $data['audience_mode'] ?? 'inherit', 'group_ids' => $data['group_ids'] ?? []];
    }

    /** @return array<string, mixed> */
    private function resourceRules(bool $updating = false): array
    {
        return [
            'title' => 'required|string|max:255',
            'type' => ($updating ? 'nullable' : 'required').'|in:'.implode(',', array_keys(config('resource_library.types'))),
            'url' => ($updating ? 'nullable' : 'nullable|required_without_all:uploaded_path,file').'|string|max:500',
            'file' => 'nullable|file|max:51200|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,gif',
            'uploaded_path' => 'nullable|string|starts_with:incoming/',
            'original_filename' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'allow_download' => 'nullable|boolean',
        ];
    }

    // -------------------------------------------------------------------- health

    public function health(string $subject): JsonResponse
    {
        $actor = $this->actor();

        return response()->json(['success' => true] + $this->health->scan($actor, $this->targets->subject($actor, $subject)));
    }

    public function fixHealth(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['code' => 'required|string|max:60', 'params' => 'nullable|array']);

        return $this->json($this->health->fix($actor, $this->targets->subject($actor, $subject), $data['code'], $data['params'] ?? []));
    }

    // ------------------------------------------------------------ kill switch & audience

    /** Stop / resume showing a resource, lesson or unit to students. */
    public function setActive(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['active' => 'required|boolean']);
        $target = $this->targets->resolve($actor, $type, $key);

        return $this->json($target instanceof SubjectResource
            ? $this->visibility->setResourceActive($actor, $target, (bool) $data['active'])
            : $this->visibility->setContainerActive($actor, $target, (bool) $data['active']));
    }

    /**
     * "Stop every video": switch many resources at once.
     * scope: `type` limits to one type (e.g. video), `ids` to specific resources, `group` to one tab.
     */
    public function bulkActive(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);

        $data = $request->validate([
            'active' => 'required|boolean',
            'type' => 'nullable|string|in:'.implode(',', array_keys(config('resource_library.types'))),
            'group' => 'nullable|integer',
            'ids' => 'nullable|array',
            'ids.*' => 'string',
        ]);

        $query = $this->catalog->managedResources($actor, $subject->id, $data['group'] ?? null)->with('groupLinks');

        if (! empty($data['type'])) {
            $query->where('type', $data['type']);
        }

        if (! empty($data['ids'])) {
            $ids = collect($data['ids'])->map(fn ($key) => RouteKey::decrypt($key))->filter()->all();
            $query->whereIn('id', $ids);
        }

        $label = (! empty($data['type']) ? ($data['active'] ? 'تشغيل كل ' : 'إيقاف كل ').config('resource_library.types.'.$data['type']) : ($data['active'] ? 'تشغيل كل الموارد' : 'إيقاف كل الموارد'));

        return $this->json($this->visibility->bulkSetActive($actor, $subject->id, $query->get(), (bool) $data['active'], $label.' في العرض الحالي'));
    }

    public function attachGroups(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['group_ids' => 'required|array|min:1', 'group_ids.*' => 'integer']);
        $target = $this->targets->resolve($actor, $type, $key);

        return $this->json($target instanceof SubjectResource
            ? $this->visibility->attachGroups($actor, $target, $data['group_ids'])
            : $this->visibility->attachGroupsToContainer($actor, $target, $data['group_ids']));
    }

    public function detachGroups(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['group_ids' => 'required|array|min:1', 'group_ids.*' => 'integer']);
        $target = $this->targets->resolve($actor, $type, $key);

        return $this->json($target instanceof SubjectResource
            ? $this->visibility->detachGroups($actor, $target, $data['group_ids'])
            : $this->visibility->detachGroupsFromContainer($actor, $target, $data['group_ids']));
    }

    public function pauseGroup(Request $request, string $key, int $group): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['paused' => 'required|boolean']);
        $resource = $this->targets->resolve($actor, 'resources', $key);

        return $this->json($this->visibility->setGroupPaused($actor, $resource, $group, (bool) $data['paused']));
    }

    public function setShared(Request $request, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['shared' => 'required|boolean']);
        $resource = $this->targets->resolve($actor, 'resources', $key);

        return $this->json($this->visibility->setShared($actor, $resource, (bool) $data['shared']));
    }

    // ----------------------------------------------------------------- placements

    public function attachPlacements(Request $request, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['targets' => 'required|array|min:1', 'targets.*.type' => 'required|string', 'targets.*.key' => 'nullable|string']);
        $resource = $this->targets->resolve($actor, 'resources', $key);

        return $this->json($this->placements->attach($actor, $resource, $this->targets->placements($actor, $data['targets'])));
    }

    public function detachPlacements(Request $request, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate([
            'targets' => 'required|array|min:1', 'targets.*.type' => 'required|string', 'targets.*.key' => 'nullable|string',
            'move_to_general' => 'nullable|boolean',
        ]);
        $resource = $this->targets->resolve($actor, 'resources', $key);

        return $this->json($this->placements->detach(
            $actor, $resource, $this->targets->placements($actor, $data['targets']), (bool) ($data['move_to_general'] ?? false)
        ));
    }

    public function setPlacementActive(Request $request, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate(['target' => 'required|array', 'target.type' => 'required|string', 'target.key' => 'nullable|string', 'active' => 'required|boolean']);
        $resource = $this->targets->resolve($actor, 'resources', $key);
        [$target] = $this->targets->placements($actor, [$data['target']]);

        return $this->json($this->placements->setActive($actor, $resource, $target, (bool) $data['active']));
    }

    /** Order the resources of a lesson / unit / the general area. */
    public function reorderResources(Request $request, string $subject): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);
        $data = $request->validate([
            'container' => 'required|array', 'container.type' => 'required|string', 'container.key' => 'nullable|string',
            'order' => 'required|array', 'order.*' => 'string',
        ]);

        [$container] = $this->targets->placements($actor, [$data['container']]);
        $ids = collect($data['order'])->map(fn ($k) => RouteKey::decrypt($k))->filter()->values()->all();

        $this->placements->reorder($actor, $container, $subject->id, $ids);

        return response()->json(['success' => true]);
    }

    /** Order units or lessons. */
    public function reorderContainers(Request $request, string $subject, string $type): JsonResponse
    {
        $actor = $this->actor();
        $subject = $this->targets->subject($actor, $subject);
        abort_unless(in_array($type, ['units', 'lessons'], true), 404);

        $data = $request->validate(['order' => 'required|array', 'order.*' => 'string']);
        $ids = collect($data['order'])->map(fn ($k) => RouteKey::decrypt($k))->filter()->values()->all();

        $this->content->reorderContainers($actor, $type, $subject->id, $ids);

        return response()->json(['success' => true]);
    }

    // ------------------------------------------------------------------ exclusions

    /** One switch: exclude / let back in a single student (`student_id`) or several (`student_ids`). */
    public function setExclusion(Request $request, string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $data = $request->validate([
            'student_id' => 'nullable|integer',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'integer',
            'excluded' => 'required|boolean',
        ]);

        $ids = $data['student_ids'] ?? (isset($data['student_id']) ? [$data['student_id']] : []);
        $target = $this->targets->resolve($actor, $type, $key);

        return $this->json($this->exclusions->setMany($actor, $target, $ids, (bool) $data['excluded']));
    }

    // --------------------------------------------------------- delete / restore / undo

    public function destroy(string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $target = $this->targets->resolve($actor, $type, $key);

        return $this->json($target instanceof SubjectResource
            ? $this->content->deleteResource($actor, $target)
            : $this->content->deleteContainer($actor, $target));
    }

    public function restore(string $type, string $key): JsonResponse
    {
        $actor = $this->actor();
        $target = $this->targets->resolve($actor, $type, $key, withTrashed: true);

        return $this->json($target instanceof SubjectResource
            ? $this->content->restoreResource($actor, $target)
            : $this->content->restoreContainer($actor, $target));
    }

    public function forceDestroy(string $key): JsonResponse
    {
        $actor = $this->actor();
        $resource = $this->targets->resolve($actor, 'resources', $key, withTrashed: true);

        return $this->json($this->content->forceDeleteResource($actor, $resource));
    }

    public function undo(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => 'required|string|max:64']);

        return $this->json($this->journal->undo($this->actor(), $data['token']));
    }

    // ------------------------------------------------------------------- helpers

    protected function json(LibraryResult $result): JsonResponse
    {
        return response()->json($result->toArray());
    }
}
