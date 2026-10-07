<?php

namespace App\Services\ResourceLibrary;

use App\Models\Admin;
use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\Registration;
use App\Models\ResourcePlacement;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Everything the admin and teacher screens READ from the library.
 *
 * One catalog serves both roles: it builds the same tree - units > lessons > resources, unit
 * level resources, general resources, drafts - and only the actor's reach differs. That is what
 * makes the admin library and the teacher's content page show exactly the same videos.
 *
 * Every resource / container comes back as a plain array ("state") that the Blade partials
 * render and the JSON endpoints return unchanged, so the page and its AJAX updates can never
 * disagree about what a row looks like.
 */
final class ResourceCatalog
{
    public const STATE_VISIBLE = 'visible';

    public const STATE_HIDDEN = 'hidden';

    public const STATE_PARTIAL = 'partial';

    public const STATE_DRAFT = 'draft';

    public const STATE_UNPLACED = 'unplaced';

    public const STATE_BLOCKED = 'blocked';

    public const STATE_PROCESSING = 'processing';

    private const STATE_LABELS = [
        self::STATE_VISIBLE => ['ظاهر للطلاب', 'success'],
        self::STATE_HIDDEN => ['موقوف عن الطلاب', 'danger'],
        self::STATE_PARTIAL => ['موقوف لبعض المجموعات', 'warning'],
        self::STATE_DRAFT => ['مسودة — لا يراه أحد', 'secondary'],
        self::STATE_UNPLACED => ['بلا مكان — لا يراه أحد', 'secondary'],
        self::STATE_BLOCKED => ['مخفي لأن الدرس/الوحدة موقوف', 'warning'],
        self::STATE_PROCESSING => ['قيد المعالجة', 'info'],
    ];

    private const TYPE_ICONS = [
        'video' => 'bi-play-circle-fill',
        'document' => 'bi-file-earmark-pdf-fill',
        'image' => 'bi-image-fill',
        'link' => 'bi-link-45deg',
        'zoom' => 'bi-camera-video-fill',
    ];

    public function __construct(private readonly ResourceFileStore $files) {}

    // ---------------------------------------------------------------- management tree

    /**
     * The manage screen of one subject: units > lessons > resources, plus the resources that
     * live at unit level, in the general area, or nowhere.
     *
     * @param  array{q?: ?string, type?: ?string, status?: ?string}  $filters
     * @return array<string, mixed>
     */
    public function manage(LibraryActor $actor, Subject $subject, ?int $groupId = null, array $filters = []): array
    {
        $this->assertSubject($actor, $subject->id);

        $ctx = $this->context($actor, $subject);
        $groupId = $groupId && $ctx['groups']->has($groupId) ? $groupId : null;

        // the resources that belong in this tab
        $resources = $this->managedResources($actor, $subject->id, $groupId)
            ->with(['groupLinks', 'placements'])
            ->withCount('contentExclusions')
            ->orderBy('id')
            ->get();

        $states = $resources->mapWithKeys(fn ($r) => [$r->id => $this->resourceState($actor, $r, $ctx)]);
        $visibleIds = $this->applyFilters($states, $filters);
        $filtering = $this->isFiltering($filters);

        // bucket the resources by where they are placed
        $buckets = ['G' => [], 'U' => [], 'L' => []];
        foreach ($resources as $resource) {
            foreach ($resource->placements as $placement) {
                $entry = [$placement->sort_order, $resource->id, $placement];
                match (true) {
                    $placement->isGeneral() => $buckets['G'][] = $entry,
                    (bool) $placement->educational_lesson_id => $buckets['L'][$placement->educational_lesson_id][] = $entry,
                    default => $buckets['U'][$placement->educational_unit_id][] = $entry,
                };
            }
        }

        $items = fn (array $entries) => collect($entries)
            ->filter(fn ($e) => $visibleIds->contains($e[1]))
            ->sortBy(fn ($e) => $e[0])
            ->map(fn ($e) => $states[$e[1]] + ['placement_active' => (bool) $e[2]->is_active])
            ->values()
            ->all();

        $units = [];
        foreach ($ctx['units'] as $unit) {
            // A container is listed in a tab when it holds something of the tab, or was
            // declared for it. A lesson declared "shared" simply follows its unit.
            $unitDeclared = $this->declaredFor($actor, $unit, $groupId, $ctx);

            $lessons = [];
            foreach ($unit->lessons as $lesson) {
                $entries = $buckets['L'][$lesson->id] ?? [];
                $lessonResources = $items($entries);
                $lessonDeclared = $lesson->is_shared ? $unitDeclared : $this->declaredFor($actor, $lesson, $groupId, $ctx);

                if ($entries === [] && ! $lessonDeclared) {
                    continue;
                }
                if ($filtering && $lessonResources === []) {
                    continue;
                }

                $lessons[] = $this->containerState($actor, $lesson, $subject->id, $lessonResources, $ctx);
            }

            $unitEntries = $buckets['U'][$unit->id] ?? [];
            $unitResources = $items($unitEntries);
            $listed = $unitEntries !== [] || $unitDeclared || $lessons !== [];

            if (! $listed || ($filtering && $lessons === [] && $unitResources === [])) {
                continue;
            }

            $lessonResources = collect($lessons)->flatMap(fn ($lesson) => $lesson['resources'])->all();
            $units[] = $this->containerState($actor, $unit, $subject->id, $unitResources, $ctx, $lessonResources) + ['lessons' => $lessons];
        }

        $general = $items($buckets['G']);

        // resources placed nowhere (only reachable by their creator / an admin)
        $unplaced = $resources->filter(fn ($r) => $r->placements->isEmpty())
            ->filter(fn ($r) => $visibleIds->contains($r->id))
            ->map(fn ($r) => $states[$r->id])->values()->all();

        return [
            'subject' => ['id' => $subject->id, 'key' => $subject->getRouteKey(), 'name' => $subject->name_ar ?: $subject->name_en],
            'groups' => $ctx['groups']->filter(fn ($g) => $actor->isAdmin() || in_array((int) $g->id, $ctx['mine'], true))->map(fn ($g) => [
                'id' => (int) $g->id,
                'name' => $g->name,
                'mine' => in_array((int) $g->id, $ctx['mine'], true),
                'students' => (int) ($ctx['students'][$g->id] ?? 0),
            ])->values()->all(),
            'selected_group' => $groupId,
            'units' => $units,
            'general' => $general,
            'unplaced' => $unplaced,
            'stats' => $this->stats($states->values()),
            'is_admin' => $actor->isAdmin(),
            'filters' => $filters,
        ];
    }

    /**
     * Resources of a subject the actor can manage, narrowed to one group's tab when asked.
     *
     * @return Builder<SubjectResource>
     */
    public function managedResources(LibraryActor $actor, int $subjectId, ?int $groupId = null): Builder
    {
        $query = SubjectResource::query()->where('subject_id', $subjectId);

        if ($actor->isTeacher()) {
            $mine = $actor->groupIdsFor($subjectId);

            // mirrors LibraryActor::canSeeResource()
            $query->where(function (Builder $w) use ($actor, $mine) {
                $w->where('is_shared', true)
                    ->orWhereHas('groupLinks', fn ($l) => $l->whereIn('group_id', $mine ?: [0]))
                    ->orWhere(fn ($own) => $own->where('created_by_type', $actor->type)->where('created_by_id', $actor->id()))
                    ->orWhere(fn ($draft) => $draft->whereNull('created_by_id')->where('is_shared', false)->whereDoesntHave('groupLinks'));
            });
        }

        if ($groupId) {
            $query->where(function (Builder $w) use ($groupId) {
                $w->where('is_shared', true)->orWhereHas('groupLinks', fn ($l) => $l->where('group_id', $groupId));
            });
        }

        return $query;
    }

    // ---------------------------------------------------------------- flat library

    /**
     * The library as a flat table: every resource the actor can manage, across subjects.
     *
     * @param  array<string, mixed>  $filters  subject (id), group (id), type, state, q, trashed, unplaced
     * @return array{rows: array<int, array<string, mixed>>, subjects: array<int, array<string, mixed>>, stats: array<string, int>}
     */
    public function library(LibraryActor $actor, array $filters = []): array
    {
        $subjectIds = $actor->subjectIds();
        $subjects = Subject::query()
            ->when($subjectIds !== null, fn ($q) => $q->whereIn('id', $subjectIds ?: [0]))
            ->with('program')
            ->orderBy('name_ar')
            ->get();

        $wanted = ! empty($filters['subject']) ? $subjects->where('id', (int) $filters['subject']) : $subjects;

        $rows = collect();
        $allStates = collect();

        foreach ($wanted as $subject) {
            $ctx = $this->context($actor, $subject);
            $groupId = ! empty($filters['group']) && $ctx['groups']->has((int) $filters['group']) ? (int) $filters['group'] : null;

            $query = $this->managedResources($actor, $subject->id, $groupId)
                ->with(['groupLinks', 'placements'])
                ->withCount('contentExclusions');

            if (! empty($filters['trashed'])) {
                $query->onlyTrashed();
            }

            $states = $query->orderByDesc('id')->get()
                ->map(fn ($r) => $this->resourceState($actor, $r, $ctx) + [
                    'subject_id' => (int) $subject->id,
                    'subject' => $subject->name_ar ?: $subject->name_en,
                    'subject_key' => $subject->getRouteKey(),
                ]);

            $allStates = $allStates->merge($states);

            $rows = $rows->merge($states->filter(fn ($state) => $this->passes($state, $filters)));
        }

        // the groups (columns of the distribution matrix) of the one subject being looked at
        $groups = [];
        if ($wanted->count() === 1 && ! empty($filters['subject'])) {
            $ctx = $this->context($actor, $wanted->first());
            $groups = $ctx['groups']
                ->filter(fn ($g) => $actor->isAdmin() || in_array((int) $g->id, $ctx['mine'], true))
                ->map(fn ($g) => ['id' => (int) $g->id, 'name' => $g->name, 'mine' => in_array((int) $g->id, $ctx['mine'], true)])
                ->values()->all();
        }

        return [
            'rows' => $rows->values()->all(),
            'groups' => $groups,
            'subjects' => $subjects->map(fn ($s) => [
                'id' => (int) $s->id,
                'key' => $s->getRouteKey(),
                'name' => $s->name_ar ?: $s->name_en,
                'program' => $s->program?->name_ar ?? $s->program?->title ?? null,
            ])->values()->all(),
            'stats' => $this->stats($allStates),
        ];
    }

    // --------------------------------------------------------------------- states

    /**
     * The row of one resource, as the actor sees it.
     *
     * @param  array<string, mixed>  $ctx
     * @return array<string, mixed>
     */
    public function resourceState(LibraryActor $actor, SubjectResource $resource, array $ctx): array
    {
        $audience = $actor->audienceOf($resource);
        $allGroupIds = $ctx['groups']->keys()->map(fn ($id) => (int) $id)->all();
        $mine = $ctx['mine'];
        $scope = $actor->isAdmin() ? $allGroupIds : $mine;

        $placements = $resource->relationLoaded('placements') ? $resource->placements : $resource->placements()->get();
        $placementViews = $placements->map(fn ($p) => $this->placementView($p, $ctx))->values()->all();
        $openPlacements = collect($placementViews)->filter(fn ($p) => $p['active'] && $p['container_active']);

        // who really sees it (within the actor's scope)
        $seeing = $audience['shared']
            ? array_values(array_diff($allGroupIds, $audience['paused']))
            : $audience['groups'];
        $seeingInScope = array_values(array_intersect($seeing, $scope));
        $pausedInScope = array_values(array_intersect($audience['paused'], $scope));
        $hasAudience = $audience['shared'] || $audience['groups'] !== [] || $audience['paused'] !== [];

        $state = match (true) {
            $resource->type === 'video' && ! $resource->isExternalLink() && $resource->processing_status !== 'ready' => self::STATE_PROCESSING,
            ! $resource->is_active => self::STATE_HIDDEN,
            $placementViews === [] => self::STATE_UNPLACED,
            $openPlacements->isEmpty() => self::STATE_BLOCKED,
            ! $hasAudience => self::STATE_DRAFT,
            $scope !== [] && $seeingInScope === [] && $pausedInScope !== [] => self::STATE_HIDDEN,
            $pausedInScope !== [] => self::STATE_PARTIAL,
            default => self::STATE_VISIBLE,
        };

        [$label, $tone] = self::STATE_LABELS[$state];
        if ($state === self::STATE_HIDDEN && $resource->is_active) {
            $label = 'موقوف لمجموعاتك';
        }

        // group tags: what the actor may name (teachers only see their own groups by name)
        $tags = [];
        $linkedIds = array_values(array_unique([...$audience['groups'], ...$audience['paused']]));
        foreach ($linkedIds as $gid) {
            if (! $ctx['groups']->has($gid) || (! $actor->isAdmin() && ! in_array($gid, $mine, true))) {
                continue;
            }
            $tags[] = [
                'id' => $gid,
                'name' => $ctx['groups'][$gid]->name,
                'paused' => in_array($gid, $audience['paused'], true),
                'mine' => in_array($gid, $mine, true),
            ];
        }

        $otherGroups = $actor->isAdmin() ? 0 : count(array_diff($audience['shared'] ? $allGroupIds : $linkedIds, $mine));

        $issues = [];
        if ($state === self::STATE_UNPLACED) {
            $issues[] = 'unplaced';
        }
        if ($state === self::STATE_DRAFT) {
            $issues[] = 'draft';
        }
        if ($state === self::STATE_BLOCKED) {
            $issues[] = 'blocked';
        }
        if ($resource->processing_status === 'failed') {
            $issues[] = 'processing_failed';
        }
        $fileMissing = $this->files->isMissing($resource);
        if ($fileMissing) {
            $issues[] = 'missing_file';
        }

        $canManage = $actor->canManageResource($resource);

        return [
            'kind' => 'resource',
            'key' => $resource->getRouteKey(),
            'id' => (int) $resource->id,
            'title' => $resource->title,
            'type' => $resource->type,
            'type_label' => config('resource_library.types.'.$resource->type, $resource->type),
            'icon' => self::TYPE_ICONS[$resource->type] ?? 'bi-link-45deg',
            'is_external' => $resource->isExternalLink(),
            'url' => $resource->isExternalLink() ? $resource->url : null,
            'description' => $resource->description,
            'allow_download' => (bool) $resource->allow_download,
            'processing_status' => $resource->processing_status,
            'original_filename' => $resource->original_filename,
            'size_bytes' => $resource->size_bytes,
            'size_label' => $resource->size_bytes ? $this->formatBytes((int) $resource->size_bytes) : null,
            'file_missing' => $fileMissing,
            'created_at' => $resource->created_at?->format('Y-m-d'),
            'created_by' => $this->creatorName($resource, $ctx),
            'deleted_at' => $resource->deleted_at?->format('Y-m-d H:i'),

            'is_active' => (bool) $resource->is_active,
            'state' => $state,
            'state_label' => $label,
            'state_tone' => $tone,

            'shared' => $audience['shared'],
            'groups' => $tags,
            'other_groups' => $otherGroups,
            'placements' => $placementViews,

            'exclusions' => (int) ($resource->content_exclusions_count ?? $resource->contentExclusions()->count()),

            'can' => [
                'manage' => $canManage,
                // a teacher can always stop showing it to their own groups, even when they cannot edit it
                'toggle' => $actor->isAdmin() || $canManage || $mine !== [],
                'share_all' => $actor->ownsAllGroupsOf((int) $resource->subject_id),
            ],
            'issues' => $issues,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $resourceStates
     * @param  array<string, mixed>  $ctx
     * @return array<string, mixed>
     */
    private function containerState(LibraryActor $actor, EducationalUnit|EducationalLesson $container, int $subjectId, array $resourceStates, array $ctx, array $alsoDerivedFrom = []): array
    {
        $declaredGroups = $container->groups->pluck('id')->map(fn ($id) => (int) $id)->all();
        $shared = (bool) $container->is_shared;
        $mine = $ctx['mine'];

        // who sees what is inside, derived from the resources (a unit also counts its lessons' resources)
        $derivedFrom = collect([...$resourceStates, ...$alsoDerivedFrom]);
        $derivedShared = $derivedFrom->contains(fn ($r) => $r['shared']);
        $derivedGroups = $derivedFrom->flatMap(fn ($r) => collect($r['groups'])->reject(fn ($g) => $g['paused'])->pluck('id'))->unique()->values()->all();

        $hidden = collect($resourceStates)->filter(fn ($r) => in_array($r['state'], [self::STATE_HIDDEN, self::STATE_PARTIAL], true))->count();

        return [
            'kind' => $container instanceof EducationalUnit ? 'unit' : 'lesson',
            'key' => $container->getRouteKey(),
            'id' => (int) $container->id,
            'name' => $container->name_ar ?: $container->name_en,
            'name_en' => $container->name_en,
            'is_active' => (bool) $container->is_active,
            'declared_shared' => $shared,
            'declared_groups' => $actor->isAdmin() ? $declaredGroups : array_values(array_intersect($declaredGroups, $mine)),
            'audience_shared' => $derivedShared,
            'audience_groups' => collect($derivedGroups)
                ->filter(fn ($id) => $ctx['groups']->has($id) && ($actor->isAdmin() || in_array($id, $mine, true)))
                ->map(fn ($id) => ['id' => $id, 'name' => $ctx['groups'][$id]->name, 'mine' => in_array($id, $mine, true)])
                ->values()->all(),
            'resources' => $resourceStates,
            'resources_count' => count($resourceStates),
            'hidden_count' => $hidden,
            'exclusions' => (int) ($container->content_exclusions_count ?? 0),
            'can_manage' => $actor->canManageContainer($container, $subjectId),
        ];
    }

    /**
     * @param  array<string, mixed>  $ctx
     * @return array<string, mixed>
     */
    private function placementView(ResourcePlacement $placement, array $ctx): array
    {
        if ($placement->isGeneral()) {
            return ['key' => 'general', 'type' => 'general', 'label' => 'المرفقات العامة', 'active' => (bool) $placement->is_active, 'container_active' => true];
        }

        if ($placement->educational_lesson_id) {
            $lesson = $ctx['lessonsById']->get((int) $placement->educational_lesson_id);
            $unit = $lesson ? $ctx['unitsById']->get((int) $lesson->educational_unit_id) : null;

            return [
                'key' => 'lesson:'.($lesson?->getRouteKey() ?? ''),
                'type' => 'lesson',
                'label' => $lesson ? (($unit?->name_ar ? $unit->name_ar.' › ' : '').$lesson->name_ar) : 'درس محذوف',
                'active' => (bool) $placement->is_active,
                'container_active' => (bool) ($lesson && $lesson->is_active && $unit && $unit->is_active),
            ];
        }

        $unit = $ctx['unitsById']->get((int) $placement->educational_unit_id);

        return [
            'key' => 'unit:'.($unit?->getRouteKey() ?? ''),
            'type' => 'unit',
            'label' => $unit ? $unit->name_ar : 'وحدة محذوفة',
            'active' => (bool) $placement->is_active,
            'container_active' => (bool) ($unit && $unit->is_active),
        ];
    }

    // -------------------------------------------------------------- scope drawer

    /**
     * Everything the "who sees it / where is it shown" drawer needs for one target.
     *
     * @return array<string, mixed>
     */
    public function scopeState(LibraryActor $actor, SubjectResource|EducationalUnit|EducationalLesson $target): array
    {
        $subject = Subject::findOrFail($this->subjectIdOf($target));
        $this->assertSubject($actor, $subject->id);
        $ctx = $this->context($actor, $subject);
        $mine = $ctx['mine'];

        if ($target instanceof SubjectResource) {
            if (! $actor->canSeeResource($target)) {
                throw LibraryException::forbidden();
            }

            $target->load('groupLinks', 'placements');
            $state = $this->resourceState($actor, $target, $ctx);
            $audience = $actor->audienceOf($target);

            $groups = $ctx['groups']->filter(fn ($g) => $actor->isAdmin() || in_array((int) $g->id, $mine, true))
                ->map(function ($g) use ($audience) {
                    $id = (int) $g->id;
                    $paused = in_array($id, $audience['paused'], true);
                    $linked = in_array($id, $audience['groups'], true);

                    return [
                        'id' => $id,
                        'name' => $g->name,
                        'sees' => ! $paused && ($audience['shared'] || $linked),
                        'paused' => $paused,
                        'linked' => $linked,
                    ];
                })->values()->all();

            return [
                'kind' => 'resource',
                'key' => $target->getRouteKey(),
                'title' => $target->title,
                'is_active' => (bool) $target->is_active,
                'shared' => $audience['shared'],
                'state' => $state['state'],
                'state_label' => $state['state_label'],
                'state_tone' => $state['state_tone'],
                'file_missing' => $state['file_missing'],
                'groups' => $groups,
                'other_groups' => $state['other_groups'],
                'placements' => $state['placements'],
                'destinations' => $this->destinations($ctx, $target),
                'can' => $state['can'],
            ];
        }

        $subjectId = (int) $subject->id;
        $resources = $this->managedResources($actor, $subjectId)->with('groupLinks')
            ->whereHas('placements', function ($p) use ($target) {
                $target instanceof EducationalLesson
                    ? $p->where('educational_lesson_id', $target->id)
                    : $p->where('educational_unit_id', $target->id)->orWhereIn('educational_lesson_id', $target->lessons()->pluck('id'));
            })->get();

        $seeing = [];
        foreach ($ctx['groups'] as $g) {
            if (! $actor->isAdmin() && ! in_array((int) $g->id, $mine, true)) {
                continue;
            }
            $count = $resources->filter(function ($r) use ($actor, $g) {
                $a = $actor->audienceOf($r);

                return in_array((int) $g->id, $a['paused'], true) ? false : ($a['shared'] || in_array((int) $g->id, $a['groups'], true));
            })->count();

            $seeing[] = ['id' => (int) $g->id, 'name' => $g->name, 'sees_count' => $count, 'total' => $resources->count()];
        }

        return [
            'kind' => $target instanceof EducationalUnit ? 'unit' : 'lesson',
            'key' => $target->getRouteKey(),
            'title' => $target->name_ar,
            'is_active' => (bool) $target->is_active,
            'resources_count' => $resources->count(),
            'groups' => $seeing,
            'declared_shared' => (bool) $target->is_shared,
            'can' => ['manage' => $actor->canManageContainer($target, $subjectId)],
        ];
    }

    /**
     * Where a resource can be placed: every unit with its lessons, marked where it already is.
     *
     * @param  array<string, mixed>  $ctx
     * @return array<string, mixed>
     */
    private function destinations(array $ctx, SubjectResource $resource): array
    {
        $placed = $resource->placements->pluck('target_key')->all();

        return [
            'general' => ['placed' => in_array(ResourcePlacement::GENERAL, $placed, true)],
            'units' => $ctx['units']->map(fn ($unit) => [
                'key' => $unit->getRouteKey(),
                'name' => $unit->name_ar,
                'placed' => in_array('U'.$unit->id, $placed, true),
                'lessons' => $unit->lessons->map(fn ($lesson) => [
                    'key' => $lesson->getRouteKey(),
                    'name' => $lesson->name_ar,
                    'placed' => in_array('L'.$lesson->id, $placed, true),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }

    // ------------------------------------------------------------------- trash

    /**
     * Deleted resources, lessons and units of a subject, newest first - what the trash lists.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function trash(LibraryActor $actor, Subject $subject): array
    {
        $this->assertSubject($actor, $subject->id);
        $ctx = $this->context($actor, $subject);

        $resources = $this->managedResources($actor, $subject->id)->onlyTrashed()
            ->with(['groupLinks', 'placements'])->orderByDesc('deleted_at')->limit(200)->get()
            ->map(fn ($r) => $this->resourceState($actor, $r, $ctx))->all();

        $stageIds = EducationalStage::where('subject_id', $subject->id)->pluck('id');
        $units = EducationalUnit::onlyTrashed()->whereIn('educational_stage_id', $stageIds)->orderByDesc('deleted_at')->get();
        $lessons = EducationalLesson::onlyTrashed()
            ->whereIn('educational_unit_id', EducationalUnit::withTrashed()->whereIn('educational_stage_id', $stageIds)->pluck('id'))
            ->orderByDesc('deleted_at')->get();

        $container = fn ($c, $kind) => [
            'kind' => $kind,
            'key' => $c->getRouteKey(),
            'name' => $c->name_ar,
            'deleted_at' => $c->deleted_at?->format('Y-m-d H:i'),
            'can_restore' => $actor->canAccessSubject($subject->id),
        ];

        return [
            'resources' => $resources,
            'units' => $units->map(fn ($u) => $container($u, 'unit'))->all(),
            'lessons' => $lessons->map(fn ($l) => $container($l, 'lesson'))->all(),
        ];
    }

    /**
     * Students the actor may preview or exclude in a subject (for the pickers).
     *
     * @return array<int, array{id: int, name: string, group: string, group_id: ?int}>
     */
    public function subjectStudents(LibraryActor $actor, Subject $subject): array
    {
        $this->assertSubject($actor, $subject->id);
        $pool = $actor->studentIdsFor($subject->id);

        return Registration::query()
            ->where('subject_id', $subject->id)
            ->whereIn('status', ExclusionManager::ACTIVE_STATUSES)
            ->when($pool !== null, fn ($q) => $q->whereIn('student_id', $pool ?: [0]))
            ->with(['student:id,full_name_ar,full_name_en', 'group:id,name'])
            ->get()
            ->groupBy('student_id')
            ->map(function ($regs, $studentId) {
                $first = $regs->first();

                return [
                    'id' => (int) $studentId,
                    'name' => $first->student ? ($first->student->full_name_ar ?: $first->student->full_name_en) : '#'.$studentId,
                    'group' => $regs->pluck('group.name')->filter()->unique()->join('، ') ?: 'بدون مجموعة',
                    'group_id' => $first->group_id ? (int) $first->group_id : null,
                ];
            })
            ->sortBy('name')->values()->all();
    }

    // ----------------------------------------------------------------- context

    /**
     * Preloaded lookups for one subject, so building a tree costs a handful of queries.
     *
     * @return array<string, mixed>
     */
    public function context(LibraryActor $actor, Subject $subject): array
    {
        $groups = Group::where('subject_id', $subject->id)->orderBy('name')->get()->keyBy('id');

        $stageIds = EducationalStage::where('subject_id', $subject->id)->pluck('id');
        $units = EducationalUnit::query()
            ->whereIn('educational_stage_id', $stageIds)
            ->with(['groups', 'lessons' => fn ($l) => $l->with('groups')->withCount('contentExclusions')->orderBy('sort_order')])
            ->withCount('contentExclusions')
            ->orderBy('sort_order')
            ->get();

        $lessons = $units->flatMap->lessons;

        $students = Registration::query()
            ->where('subject_id', $subject->id)
            ->whereIn('status', ExclusionManager::ACTIVE_STATUSES)
            ->whereNotNull('group_id')
            ->selectRaw('group_id, count(distinct student_id) as c')
            ->groupBy('group_id')
            ->pluck('c', 'group_id');

        return [
            'groups' => $groups,
            'mine' => $actor->groupIdsFor($subject->id),
            'units' => $units,
            'unitsById' => $units->keyBy('id'),
            'lessonsById' => $lessons->keyBy('id'),
            'students' => $students,
            'creators' => new \ArrayObject,
        ];
    }

    // ------------------------------------------------------------------ helpers

    /**
     * Was the container DECLARED for this tab? That is what keeps a brand-new, still empty unit
     * or lesson visible in the tab it was created in (see EducationalUnit docblock).
     * Students never depend on it.
     *
     * @param  array<string, mixed>  $ctx
     */
    private function declaredFor(LibraryActor $actor, EducationalUnit|EducationalLesson $container, ?int $groupId, array $ctx): bool
    {
        if ($container->is_shared) {
            return true;
        }

        $declared = $container->groups->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($groupId) {
            return in_array($groupId, $declared, true);
        }

        if ($declared === []) {
            return true; // a declared draft
        }

        return $actor->isAdmin() || array_intersect($declared, $ctx['mine']) !== [];
    }

    /**
     * @param  Collection<int|string, array<string, mixed>>  $states
     * @return Collection<int, int>  ids that pass the filters
     */
    private function applyFilters(Collection $states, array $filters): Collection
    {
        return $states->filter(fn ($state) => $this->passes($state, $filters))->pluck('id')->values();
    }

    /** @param  array<string, mixed>  $state */
    private function passes(array $state, array $filters): bool
    {
        if (! empty($filters['type']) && $state['type'] !== $filters['type']) {
            return false;
        }

        if (! empty($filters['q'])) {
            $needle = $this->normalize($filters['q']);
            $hay = $this->normalize($state['title'].' '.($state['description'] ?? '').' '.($state['original_filename'] ?? ''));
            foreach (array_filter(explode(' ', $needle)) as $word) {
                if (! str_contains($hay, $word)) {
                    return false;
                }
            }
        }

        $status = $filters['state'] ?? $filters['status'] ?? null;
        if ($status) {
            return match ($status) {
                'visible' => $state['state'] === self::STATE_VISIBLE,
                'hidden' => in_array($state['state'], [self::STATE_HIDDEN, self::STATE_PARTIAL], true),
                'draft' => in_array($state['state'], [self::STATE_DRAFT, self::STATE_UNPLACED], true),
                'blocked' => $state['state'] === self::STATE_BLOCKED,
                'excluded' => $state['exclusions'] > 0,
                'issues' => $state['issues'] !== [],
                default => true,
            };
        }

        if (! empty($filters['unplaced']) && $state['state'] !== self::STATE_UNPLACED) {
            return false;
        }

        return true;
    }

    private function isFiltering(array $filters): bool
    {
        return ! empty($filters['q']) || ! empty($filters['type']) || ! empty($filters['status']) || ! empty($filters['state']);
    }

    /** Arabic-insensitive search text: no diacritics, unified alef / yaa / taa marbuta. */
    private function normalize(?string $text): string
    {
        $text = mb_strtolower((string) $text);
        $text = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{0640}]/u', '', $text);
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace('ى', 'ي', $text);
        $text = str_replace('ة', 'ه', $text);

        return trim(preg_replace('/\s+/u', ' ', $text));
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $states
     * @return array<string, int>
     */
    private function stats(Collection $states): array
    {
        return [
            'resources' => $states->count(),
            'videos' => $states->where('type', 'video')->count(),
            'visible' => $states->where('state', self::STATE_VISIBLE)->count(),
            'hidden' => $states->filter(fn ($s) => in_array($s['state'], [self::STATE_HIDDEN, self::STATE_PARTIAL], true))->count(),
            'draft' => $states->filter(fn ($s) => in_array($s['state'], [self::STATE_DRAFT, self::STATE_UNPLACED], true))->count(),
            'excluded' => $states->filter(fn ($s) => $s['exclusions'] > 0)->count(),
            'issues' => $states->filter(fn ($s) => $s['issues'] !== [])->count(),
        ];
    }

    /** @param  array<string, mixed>  $ctx */
    private function creatorName(SubjectResource $resource, array $ctx): ?string
    {
        if (! $resource->created_by_id) {
            return null;
        }

        $key = $resource->created_by_type.':'.$resource->created_by_id;
        $ctx['creators'][$key] ??= match ($resource->created_by_type) {
            LibraryActor::ADMIN => Admin::find($resource->created_by_id)?->name,
            LibraryActor::TEACHER => Teacher::find($resource->created_by_id)?->name,
            default => null,
        };

        return $ctx['creators'][$key];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = (int) min(floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $i);

        return number_format($value, $value >= 10 || $i === 0 ? 0 : 1).' '.$units[$i];
    }

    private function subjectIdOf(SubjectResource|EducationalUnit|EducationalLesson $target): int
    {
        $subjectId = match (true) {
            $target instanceof SubjectResource => $target->subject_id,
            $target instanceof EducationalUnit => $target->stage?->subject_id,
            default => $target->unit?->stage?->subject_id,
        };

        if (! $subjectId) {
            throw LibraryException::notFound();
        }

        return (int) $subjectId;
    }

    private function assertSubject(LibraryActor $actor, int $subjectId): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
        }
    }
}
