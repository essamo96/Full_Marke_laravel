<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Registration;
use App\Models\ResourceGroupLink;
use App\Models\ResourcePlacement;
use App\Models\StudentContentExclusion;
use App\Models\Subject;
use App\Models\SubjectResource;
use App\Support\RouteKey;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * "Extract the errors": scans a subject's library for things that silently make content
 * invisible or wrong, explains each one in plain Arabic, and fixes most of them in one click.
 *
 * Every fix goes through the same managers as the screens (so it is authorized, journaled and
 * undoable) and runs inside one journal batch: "fix all" is a single Undo.
 */
final class LibraryHealth
{
    public const ERROR = 'error';

    public const WARNING = 'warning';

    public const INFO = 'info';

    public function __construct(
        private readonly ResourceCatalog $catalog,
        private readonly LibraryJournal $journal,
        private readonly VisibilityManager $visibility,
        private readonly PlacementManager $placements,
        private readonly ResourceFileStore $files,
        private readonly IncomingUploads $uploads,
    ) {}

    /**
     * @return array{issues: array<int, array<string, mixed>>, summary: array<string, int>}
     */
    public function scan(LibraryActor $actor, Subject $subject): array
    {
        $this->assertSubject($actor, $subject->id);

        $ctx = $this->catalog->context($actor, $subject);
        $resources = $this->catalog->managedResources($actor, $subject->id)->with(['groupLinks', 'placements'])->get();
        $states = $resources->mapWithKeys(fn ($r) => [$r->id => $this->catalog->resourceState($actor, $r, $ctx)]);

        $issues = array_values(array_filter([
            $this->unplaced($resources, $states),
            $this->failedProcessing($resources, $states),
            $this->missingFiles($resources, $states),
            $this->orphanUploads($actor),
            $this->drafts($resources, $states),
            $this->blocked($resources, $states),
            $this->declaredButEmpty($actor, $resources, $ctx),
            $this->duplicates($actor, $resources, $states),
            $this->redundantLinks($resources, $states),
            $this->orphanExclusions($actor, $subject),
            $this->ungroupedStudents($subject),
        ]));

        return [
            'issues' => $issues,
            'summary' => [
                self::ERROR => collect($issues)->where('severity', self::ERROR)->sum('count'),
                self::WARNING => collect($issues)->where('severity', self::WARNING)->sum('count'),
                self::INFO => collect($issues)->where('severity', self::INFO)->sum('count'),
            ],
        ];
    }

    // ----------------------------------------------------------------- the checks

    /** @return array<string, mixed>|null */
    private function unplaced(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $states[$r->id]['state'] === ResourceCatalog::STATE_UNPLACED);

        return $this->issue('unplaced', self::ERROR, 'موارد بلا مكان',
            'هذه الموارد غير موضوعة في أي وحدة أو درس، لذلك لا يراها أي طالب.',
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id]))->all(),
            'place_general', 'وضعها في المرفقات العامة');
    }

    /** @return array<string, mixed>|null */
    private function failedProcessing(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $r->processing_status === 'failed');

        return $this->issue('processing_failed', self::ERROR, 'فيديوهات فشلت معالجتها',
            'فشلت معالجة هذه الفيديوهات ولن تعمل للطلاب. أعد رفعها.',
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id], $r->processing_error))->all());
    }

    /** @return array<string, mixed>|null */
    private function missingFiles(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $this->files->isMissing($r));
        $isLive = fn ($r) => $states[$r->id]['state'] !== ResourceCatalog::STATE_HIDDEN;
        $live = $bad->filter($isLive)->count();

        return $this->issue('missing_file', self::ERROR, 'ملفات غير موجودة على السيرفر',
            'سجل المورد موجود لكن ملفه مفقود (غالباً بعد استرجاع نسخة احتياطية لقاعدة البيانات بدون مجلد الملفات). '
                .($live ? "{$live} منها ما زال ظاهراً للطلاب وسيرون رسالة «غير متاح». أوقفها الآن ثم استبدل ملفاتها." : 'كلها موقوفة عن الطلاب؛ استبدل ملفاتها لإعادتها.'),
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id], $r->url.($isLive($r) ? '' : ' — موقوف')))->all(),
            $live ? 'deactivate_missing' : null, 'إيقافها عن الطلاب');
    }

    /**
     * Uploads parked in incoming/ that never became a resource (not tied to a subject: admins see
     * all of them, a teacher only their own). Handled in the "uploaded files" panel.
     *
     * @return array<string, mixed>|null
     */
    private function orphanUploads(LibraryActor $actor): ?array
    {
        $orphans = $this->uploads->orphans($actor);
        $hours = (int) config('resource_library.upload.orphan_grace_hours', 168);

        return $this->issue('orphan_uploads', self::INFO, 'ملفات مرفوعة غير مربوطة بأي مورد',
            'رُفعت هذه الملفات ثم لم يُحفظ النموذج. اربطها بمورد ملفه مفقود، أو أنشئ منها مورداً، أو احذفها. تُحذف تلقائياً بعد '.$hours.' ساعة.',
            array_map(fn ($upload) => [
                'kind' => 'orphan_upload',
                'label' => $upload['original_filename'] ?: basename($upload['path']),
                'detail' => trim(($upload['size_bytes'] ? round($upload['size_bytes'] / 1048576, 1).' MB · ' : '').$upload['modified_at']
                    .($upload['suggestions'] ? ' · يطابق مورداً ملفه مفقود' : '')),
            ], $orphans));
    }

    /** @return array<string, mixed>|null */
    private function drafts(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $states[$r->id]['state'] === ResourceCatalog::STATE_DRAFT);

        return $this->issue('draft', self::WARNING, 'مسودات لا يراها أحد',
            'هذه الموارد موضوعة في مكانها لكن لا توجد أي مجموعة تراها.',
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id]))->all(),
            'share_with_my_groups', 'إظهارها لمجموعاتي');
    }

    /** @return array<string, mixed>|null */
    private function blocked(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $states[$r->id]['state'] === ResourceCatalog::STATE_BLOCKED);

        return $this->issue('blocked', self::WARNING, 'موارد مخفية بسبب وحدة أو درس موقوف',
            'المورد نفسه شغّال، لكن كل الأماكن التي يظهر فيها موقوفة أو محذوفة، فلا يراه الطلاب.',
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id], collect($states[$r->id]['placements'])->pluck('label')->join('، ')))->all(),
            'activate_containers', 'تشغيل الوحدات والدروس الموقوفة');
    }

    /**
     * Containers DECLARED for a group (that is what makes them appear in the group's tab) whose
     * content the group's students cannot actually see - the classic "I shared the lesson with
     * the group and nothing shows up".
     *
     * @return array<string, mixed>|null
     */
    private function declaredButEmpty(LibraryActor $actor, Collection $resources, array $ctx): ?array
    {
        $items = [];
        $scope = $actor->isAdmin() ? $ctx['groups']->keys()->all() : $ctx['mine'];

        $inside = fn (EducationalLesson|EducationalUnit $c) => $resources->filter(fn ($r) => $r->placements->contains(fn ($p) => $c instanceof EducationalLesson
            ? (int) $p->educational_lesson_id === $c->id
            : (int) $p->educational_unit_id === $c->id));

        $audienceSees = function (SubjectResource $r, int $groupId) use ($actor) {
            $a = $actor->audienceOf($r);

            return $r->is_active && ! in_array($groupId, $a['paused'], true) && ($a['shared'] || in_array($groupId, $a['groups'], true));
        };

        foreach ($ctx['units'] as $unit) {
            foreach ($unit->lessons as $lesson) {
                if ($lesson->is_shared) {
                    continue;
                }
                $contents = $inside($lesson);
                if ($contents->isEmpty()) {
                    continue;
                }

                foreach ($lesson->groups as $group) {
                    if (! in_array((int) $group->id, $scope, true)) {
                        continue;
                    }
                    if ($contents->contains(fn ($r) => $audienceSees($r, (int) $group->id))) {
                        continue;
                    }

                    $items[] = [
                        'kind' => 'lesson',
                        'id' => (int) $lesson->id,
                        'key' => $lesson->getRouteKey(),
                        'group_id' => (int) $group->id,
                        'label' => $unit->name_ar.' › '.$lesson->name_ar,
                        'detail' => "مخصص لمجموعة «{$group->name}» لكن موارده ({$contents->count()}) لا تظهر لها",
                    ];
                }
            }
        }

        return $this->issue('declared_empty', self::WARNING, 'دروس مخصصة لمجموعة لكن طلابها لا يرون شيئاً فيها',
            'الدرس مضاف لهذه المجموعة، لكن موارده غير مشاركة معها. اضغط إصلاح لإظهار محتواه لها.',
            $items, 'share_container_with_group', 'إظهار محتواها للمجموعة');
    }

    /** @return array<string, mixed>|null */
    private function duplicates(LibraryActor $actor, Collection $resources, Collection $states): ?array
    {
        $fingerprint = fn (SubjectResource $r) => match (true) {
            $r->content_hash !== null => 'h:'.$r->content_hash,
            $r->isExternalLink() => 'u:'.mb_strtolower(rtrim((string) $r->url, '/')),
            $r->size_bytes && $r->original_filename => 'f:'.$r->size_bytes.':'.mb_strtolower($r->original_filename),
            default => null,
        };

        $sets = $resources->filter(fn ($r) => $fingerprint($r))->groupBy($fingerprint)->filter(fn ($set) => $set->count() > 1);

        $items = $sets->map(fn ($set) => [
            'kind' => 'duplicate_set',
            'label' => $set->first()->title,
            'detail' => 'مرفوع '.$set->count().' مرات — يمكن دمجها في مورد واحد مع الاحتفاظ بكل أماكن عرضها ومجموعاتها',
            // route keys are re-encrypted with a random IV on every call, so they can never be
            // compared for equality - ids are what the fix matches on
            'ids' => $set->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            'keys' => $set->map(fn ($r) => $r->getRouteKey())->values()->all(),
            'resources' => $set->map(fn ($r) => $this->resourceItem($states[$r->id]))->values()->all(),
        ])->values()->all();

        return $this->issue('duplicates', self::INFO, 'موارد مكررة (نفس الملف أو الرابط)',
            'الملف نفسه مرفوع أكثر من مرة. ارفع مرة واحدة وأظهره في كل مكان: ادمج النسخ ليبقى مورد واحد.',
            $items, $actor->isAdmin() ? 'merge_duplicates' : null, 'دمج النسخ المكررة');
    }

    /** @return array<string, mixed>|null */
    private function redundantLinks(Collection $resources, Collection $states): ?array
    {
        $bad = $resources->filter(fn ($r) => $r->is_shared && $r->groupLinks->whereNull('deleted_at')->where('is_active', true)->isNotEmpty());

        return $this->issue('redundant_links', self::INFO, 'ربط مجموعات زائد على مورد مشترك',
            'المورد مشترك مع كل المجموعات أصلاً، وتوجد روابط مجموعات إضافية لا تفعل شيئاً.',
            $bad->map(fn ($r) => $this->resourceItem($states[$r->id]))->all(),
            'clean_links', 'حذف الروابط الزائدة');
    }

    /** @return array<string, mixed>|null */
    private function orphanExclusions(LibraryActor $actor, Subject $subject): ?array
    {
        if (! $actor->isAdmin()) {
            return null;
        }

        $enrolled = Registration::where('subject_id', $subject->id)->whereIn('status', ExclusionManager::ACTIVE_STATUSES)->pluck('student_id');

        $rows = StudentContentExclusion::query()->where('subject_id', $subject->id)->get()
            ->filter(function ($row) use ($enrolled) {
                $target = $row->excludable_type::withTrashed()->find($row->excludable_id);

                return ! $target || $target->trashed() || ! $enrolled->contains($row->student_id);
            });

        return $this->issue('orphan_exclusions', self::INFO, 'استثناءات بلا أثر',
            'استثناءات لطلاب لم يعودوا مسجلين في المادة، أو على محتوى محذوف.',
            $rows->map(fn ($row) => ['kind' => 'exclusion', 'label' => '#'.$row->id.' — طالب '.$row->student_id, 'id' => $row->id])->values()->all(),
            'clean_exclusions', 'حذف الاستثناءات الزائدة');
    }

    /** @return array<string, mixed>|null */
    private function ungroupedStudents(Subject $subject): ?array
    {
        $count = Registration::where('subject_id', $subject->id)
            ->whereIn('status', ExclusionManager::ACTIVE_STATUSES)
            ->whereNull('group_id')
            ->count();

        if ($count === 0) {
            return null;
        }

        return $this->issue('ungrouped_students', self::INFO, 'طلاب مسجلون بدون مجموعة',
            "{$count} طالب مسجل في المادة بلا مجموعة؛ لا يرون إلا المحتوى المشترك مع كل المجموعات.",
            [['kind' => 'note', 'label' => "{$count} طالب"]]);
    }

    // --------------------------------------------------------------------- fixes

    /**
     * Apply one kind of fix to everything the scan found for it (within the actor's reach),
     * as a single undoable action.
     */
    public function fix(LibraryActor $actor, Subject $subject, string $code, array $params = []): LibraryResult
    {
        $this->assertSubject($actor, $subject->id);

        $ctx = $this->catalog->context($actor, $subject);
        $resources = $this->catalog->managedResources($actor, $subject->id)->with(['groupLinks', 'placements'])->get();
        $states = $resources->mapWithKeys(fn ($r) => [$r->id => $this->catalog->resourceState($actor, $r, $ctx)]);

        $touched = 0;

        $log = $this->journal->batch($actor, 'health.fix', 'إصلاح تلقائي: '.$code, $subject->id, function () use ($actor, $subject, $code, $params, $resources, $states, $ctx, &$touched) {
            $touched = match ($code) {
                'place_general' => $this->fixPlaceGeneral($actor, $resources, $states),
                'deactivate_missing' => $this->fixDeactivateMissing($actor, $resources, $states),
                'share_with_my_groups' => $this->fixShareWithMyGroups($actor, $subject, $resources, $states),
                'activate_containers' => $this->fixActivateContainers($actor, $resources, $states, $ctx),
                'share_container_with_group' => $this->fixShareContainerWithGroup($actor, $resources, $ctx, $params),
                'clean_links' => $this->fixCleanLinks($actor, $resources),
                'clean_exclusions' => $this->fixCleanExclusions($actor, $subject),
                'merge_duplicates' => $this->fixMergeDuplicates($actor, $resources, $states, $params),
                default => throw LibraryException::invalid('إصلاح غير معروف.', 'unknown_fix'),
            };
        });

        return new LibraryResult(
            $touched > 0 ? "تم إصلاح {$touched} عنصر." : 'لا يوجد ما يحتاج إلى إصلاح.',
            $log?->uuid,
            ['fixed' => $touched],
        );
    }

    private function fixPlaceGeneral(LibraryActor $actor, Collection $resources, Collection $states): int
    {
        $n = 0;
        foreach ($resources as $resource) {
            if ($states[$resource->id]['state'] !== ResourceCatalog::STATE_UNPLACED || ! $actor->canManageResource($resource)) {
                continue;
            }
            $this->placements->attach($actor, $resource, [PlacementManager::GENERAL]);
            $n++;
        }

        return $n;
    }

    /** Stop showing resources whose file is gone (a teacher who shares one with others pauses their own groups). */
    private function fixDeactivateMissing(LibraryActor $actor, Collection $resources, Collection $states): int
    {
        $n = 0;
        foreach ($resources as $resource) {
            if ($states[$resource->id]['state'] === ResourceCatalog::STATE_HIDDEN || ! $this->files->isMissing($resource)) {
                continue;
            }
            $result = $this->visibility->setResourceActive($actor, $resource, false);
            $n += ($result->data['mode'] ?? null) === 'noop' ? 0 : 1;
        }

        return $n;
    }

    private function fixShareWithMyGroups(LibraryActor $actor, Subject $subject, Collection $resources, Collection $states): int
    {
        $n = 0;
        foreach ($resources as $resource) {
            if ($states[$resource->id]['state'] !== ResourceCatalog::STATE_DRAFT || ! $actor->canManageResource($resource)) {
                continue;
            }

            if ($actor->ownsAllGroupsOf($subject->id)) {
                $this->visibility->setShared($actor, $resource, true);
            } else {
                $this->visibility->attachGroups($actor, $resource, $actor->groupIdsFor($subject->id));
            }
            $n++;
        }

        return $n;
    }

    private function fixActivateContainers(LibraryActor $actor, Collection $resources, Collection $states, array $ctx): int
    {
        $n = 0;
        $done = [];

        foreach ($resources as $resource) {
            if ($states[$resource->id]['state'] !== ResourceCatalog::STATE_BLOCKED) {
                continue;
            }

            foreach ($resource->placements as $placement) {
                $container = $placement->educational_lesson_id
                    ? $ctx['lessonsById']->get((int) $placement->educational_lesson_id)
                    : $ctx['unitsById']->get((int) $placement->educational_unit_id);
                $unit = $container instanceof EducationalLesson ? $ctx['unitsById']->get((int) $container->educational_unit_id) : $container;

                foreach (array_filter([$container, $unit]) as $c) {
                    $id = get_class($c).':'.$c->id;
                    if ($c->is_active || isset($done[$id])) {
                        continue;
                    }
                    $this->visibility->setContainerActive($actor, $c, true);
                    $done[$id] = true;
                    $n++;
                }

                if (! $placement->is_active) {
                    $this->placements->setActive($actor, $resource, $container ?? PlacementManager::GENERAL, true);
                    $n++;
                }
            }
        }

        return $n;
    }

    /** @param  array<string, mixed>  $params  optional: lesson (key), group (id) to fix one item only */
    private function fixShareContainerWithGroup(LibraryActor $actor, Collection $resources, array $ctx, array $params): int
    {
        $report = $this->declaredButEmpty($actor, $resources, $ctx);
        $n = 0;

        foreach ($report['items'] ?? [] as $item) {
            if (! empty($params['key']) && RouteKey::decrypt($params['key']) !== $item['id']) {
                continue;
            }

            $lesson = EducationalLesson::find($item['id']);
            if (! $lesson) {
                continue;
            }

            $this->visibility->attachGroupsToContainer($actor, $lesson, [$item['group_id']]);
            $n++;
        }

        return $n;
    }

    private function fixCleanLinks(LibraryActor $actor, Collection $resources): int
    {
        $n = 0;
        foreach ($resources as $resource) {
            if (! $resource->is_shared || ! $actor->canManageResource($resource)) {
                continue;
            }

            $before = [$this->journal->resource($resource)];
            $removed = ResourceGroupLink::where('subject_resource_id', $resource->id)->where('is_active', true)->delete();
            if ($removed) {
                $this->journal->record($actor, 'resource.links.clean', "تنظيف روابط «{$resource->title}»", $before, (int) $resource->subject_id, $resource);
                $n++;
            }
        }

        return $n;
    }

    private function fixCleanExclusions(LibraryActor $actor, Subject $subject): int
    {
        if (! $actor->isAdmin()) {
            throw LibraryException::forbidden();
        }

        $scan = $this->orphanExclusions($actor, $subject);
        $ids = collect($scan['items'] ?? [])->pluck('id')->all();

        return $ids ? StudentContentExclusion::whereIn('id', $ids)->delete() : 0;
    }

    /**
     * Merge each set of duplicates into its oldest resource: audiences, placements and
     * exclusions are united on the keeper, the copies go to the trash.
     *
     * @param  array<string, mixed>  $params  optional: keys => resource route keys of ONE set to merge
     */
    private function fixMergeDuplicates(LibraryActor $actor, Collection $resources, Collection $states, array $params): int
    {
        if (! $actor->isAdmin()) {
            throw LibraryException::forbidden('دمج المكررات متاح للإدارة فقط.');
        }

        $report = $this->duplicates($actor, $resources, $states);
        $n = 0;

        foreach ($report['items'] ?? [] as $set) {
            if (! empty($params['keys'])) {
                $wanted = array_map(fn ($key) => RouteKey::decrypt($key), $params['keys']);
                if (array_diff($wanted, $set['ids']) !== []) {
                    continue;
                }
            }

            $members = $resources->filter(fn ($r) => in_array((int) $r->id, $set['ids'], true))->sortBy('id')->values();
            $keeper = $members->shift();

            foreach ($members as $copy) {
                $this->mergeInto($actor, $keeper->fresh(['groupLinks', 'placements']), $copy->fresh(['groupLinks', 'placements']));
                $n++;
            }
        }

        return $n;
    }

    private function mergeInto(LibraryActor $actor, SubjectResource $keeper, SubjectResource $copy): void
    {
        $this->journal->record($actor, 'resource.merge', "دمج «{$copy->title}» في «{$keeper->title}»", [
            $this->journal->resource($keeper), $this->journal->resource($copy),
        ], (int) $keeper->subject_id, $keeper);

        DB::transaction(function () use ($actor, $keeper, $copy) {
            // audience: union
            if ($copy->is_shared) {
                $keeper->forceFill(['is_shared' => true])->save();
                ResourceGroupLink::where('subject_resource_id', $keeper->id)->where('is_active', true)->delete();
            } elseif (! $keeper->is_shared) {
                $this->visibility->grantAudience($actor, $keeper, $copy->groupLinks->whereNull('deleted_at')->where('is_active', true)->pluck('group_id')->all());
            }

            // placements: union
            foreach ($copy->placements as $placement) {
                $existing = ResourcePlacement::withTrashed()->where('subject_resource_id', $keeper->id)->where('target_key', $placement->target_key)->first();
                if (! $existing) {
                    ResourcePlacement::create([
                        'subject_resource_id' => $keeper->id,
                        'educational_unit_id' => $placement->educational_unit_id,
                        'educational_lesson_id' => $placement->educational_lesson_id,
                        'sort_order' => $placement->sort_order,
                        'is_active' => $placement->is_active,
                    ] + $actor->stamp());
                } elseif ($existing->trashed()) {
                    $existing->restore();
                }
            }
            $this->placements->syncLegacyColumn($keeper);

            // exclusions: union
            foreach ($copy->contentExclusions as $exclusion) {
                StudentContentExclusion::firstOrCreate([
                    'student_id' => $exclusion->student_id,
                    'excludable_type' => $keeper->getMorphClass(),
                    'excludable_id' => $keeper->id,
                ], ['subject_id' => $keeper->subject_id] + $actor->stamp('excluded_by'));
            }

            $copy->forceFill(['deleted_by' => $actor->id(), 'deleted_by_type' => $actor->type])->saveQuietly();
            $copy->delete();
        });
    }

    // ------------------------------------------------------------------- helpers

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<string, mixed>|null
     */
    private function issue(string $code, string $severity, string $title, string $detail, array $items, ?string $fix = null, ?string $fixLabel = null): ?array
    {
        if ($items === []) {
            return null;
        }

        $items = array_values($items); // a filtered collection keeps its old keys: JSON must get a list

        return [
            'code' => $code,
            'severity' => $severity,
            'title' => $title,
            'detail' => $detail,
            'count' => count($items),
            'items' => $items,
            'fix' => $fix,
            'fix_label' => $fixLabel,
        ];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    private function resourceItem(array $state, ?string $detail = null): array
    {
        return [
            'kind' => 'resource',
            'key' => $state['key'],
            'label' => $state['title'],
            'type' => $state['type'],
            'detail' => $detail,
        ];
    }

    private function assertSubject(LibraryActor $actor, int $subjectId): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
        }
    }
}
