<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\ResourcePlacement;
use App\Models\Subject;
use App\Models\SubjectResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Creating, editing, deleting and restoring the content itself: resources, lessons, units.
 *
 * Deleting is always SOFT and reversible:
 *   - a resource keeps its row, links, placements and exclusions
 *   - deleting a lesson/unit takes its placements with it, and the resources that lived ONLY
 *     there go to the trash too (instead of silently turning into "general" resources visible
 *     to everyone, which is what the old hard delete did)
 * Everything removed by one delete shares one deleted_at, so restoring brings back exactly
 * that group and nothing else.
 */
final class ContentManager
{
    public function __construct(
        private readonly LibraryJournal $journal,
        private readonly VisibilityManager $visibility,
        private readonly PlacementManager $placements,
        private readonly ExclusionManager $exclusions,
        private readonly ResourceFileStore $files,
    ) {}

    // ------------------------------------------------------------------ resources

    /**
     * Upload once. `$targets` are the places it appears in (lessons / units / 'general'), and
     * `$audience` who sees it: ['mode' => 'inherit'|'shared'|'groups', 'group_ids' => [...]].
     *
     * @param  array<string, mixed>  $data  title, type, url?, description?, allow_download?, uploaded_path?, original_filename?
     * @param  array<int, EducationalLesson|EducationalUnit|string>  $targets
     * @param  array<string, mixed>  $audience
     * @param  array<int>  $excludedStudentIds
     */
    public function createResource(
        LibraryActor $actor,
        Subject $subject,
        array $data,
        array $targets = [],
        array $audience = [],
        ?UploadedFile $file = null,
        array $excludedStudentIds = [],
    ): LibraryResult {
        $this->assertSubject($actor, $subject->id);

        $targets = $targets ?: [PlacementManager::GENERAL];
        foreach ($targets as $target) {
            $this->assertPlaceable($actor, $subject, $target);
        }

        $resolved = $this->resolveAudience($actor, $subject, $audience, $targets);

        $source = $this->files->ingest(
            $data['type'],
            $data['uploaded_path'] ?? null,
            $file,
            $data['url'] ?? null,
            $data['original_filename'] ?? null,
        );

        $resource = DB::transaction(function () use ($actor, $subject, $data, $targets, $resolved, $source, $excludedStudentIds) {
            $resource = SubjectResource::create([
                'subject_id' => $subject->id,
                'title' => $data['title'],
                'type' => $data['type'],
                'category' => $data['type'],
                'description' => $data['description'] ?? null,
                'allow_download' => (bool) ($data['allow_download'] ?? false),
                'processing_status' => 'ready',
                'is_active' => true,
                'is_shared' => $resolved['shared'],
                'sort_order' => 0,
            ] + $source + $actor->stamp());

            foreach ($targets as $target) {
                $this->placements->place($actor, $resource, $target);
            }
            $this->placements->syncLegacyColumn($resource);

            if ($resolved['groups']) {
                $this->visibility->grantAudience($actor, $resource, $resolved['groups']);
            }

            if ($excludedStudentIds) {
                $this->exclusions->setMany($actor, $resource, $excludedStudentIds, true);
            }

            return $resource;
        });

        $this->journal->note($actor, 'resource.create', "رفع «{$resource->title}»", $subject->id, $resource);

        return new LibraryResult("تم حفظ «{$resource->title}».", null, ['resource' => $resource]);
    }

    /**
     * Edit the resource itself: title, description, link, file, download flag.
     * Who sees it and where it appears are separate actions (VisibilityManager / PlacementManager).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateResource(LibraryActor $actor, SubjectResource $resource, array $data, ?UploadedFile $file = null): LibraryResult
    {
        if (! $actor->canManageResource($resource)) {
            throw LibraryException::forbidden('هذا المورد يخدم مجموعات لا تتبع لك، لذلك لا يمكنك تعديل بياناته.');
        }

        $attributes = [
            'title' => $data['title'] ?? $resource->title,
            'description' => $data['description'] ?? $resource->description,
            'allow_download' => (bool) ($data['allow_download'] ?? $resource->allow_download),
        ];

        $oldFileToDelete = null;
        $replacing = ! empty($data['uploaded_path']) || $file
            || (in_array($resource->type, ['link', 'zoom'], true) && ! empty($data['url']) && $data['url'] !== $resource->url);

        if ($replacing) {
            $source = $this->files->ingest(
                $resource->type,
                $data['uploaded_path'] ?? null,
                $file,
                $data['url'] ?? null,
                $data['original_filename'] ?? null,
            );

            if (! $resource->isExternalLink()) {
                $oldFileToDelete = clone $resource;
            }

            $attributes += $source + ['processing_status' => 'ready'];
        }

        $resource->update($attributes);

        if ($oldFileToDelete) {
            $this->files->delete($oldFileToDelete);
        }

        $this->journal->note($actor, 'resource.update', "تعديل «{$resource->title}»", (int) $resource->subject_id, $resource);

        return new LibraryResult("تم تحديث «{$resource->title}».", null, ['resource' => $resource->fresh()]);
    }

    /**
     * Delete a resource (soft). A teacher can only delete what nobody outside their groups
     * depends on; for anything else "delete" means "stop showing it to my groups".
     */
    public function deleteResource(LibraryActor $actor, SubjectResource $resource): LibraryResult
    {
        if (! $actor->canSeeResource($resource)) {
            throw LibraryException::forbidden();
        }

        if (! $actor->canManageResource($resource)) {
            return $this->visibility
                ->detachGroups($actor, $resource, $actor->groupIdsFor((int) $resource->subject_id))
                ->with(['detached_only' => true]);
        }

        $before = [$this->journal->resource($resource)];

        $resource->forceFill(['deleted_by' => $actor->id(), 'deleted_by_type' => $actor->type])->saveQuietly();
        $resource->delete();

        $log = $this->journal->record($actor, 'resource.delete', "حذف «{$resource->title}»", $before, (int) $resource->subject_id, $resource);

        return new LibraryResult("تم حذف «{$resource->title}» — يمكنك التراجع الآن أو استرجاعه لاحقاً من المحذوفات.", $log->uuid);
    }

    /** Bring a deleted resource back, with its audience, placements and exclusions. */
    public function restoreResource(LibraryActor $actor, SubjectResource $resource): LibraryResult
    {
        $subjectId = (int) $resource->subject_id;
        $this->assertSubject($actor, $subjectId);

        if (! $resource->trashed()) {
            return new LibraryResult('المورد غير محذوف.');
        }

        // The resource row is trashed, so judge it as the live row it will become.
        $before = [$this->journal->resource($resource)];

        $resource->restore();
        $resource->forceFill(['deleted_by' => null, 'deleted_by_type' => null])->saveQuietly();

        $this->journal->record($actor, 'resource.restore', "استرجاع «{$resource->title}»", $before, $subjectId, $resource);

        $missing = ! $resource->isExternalLink() && $resource->url && ! $this->files->exists($resource);

        return new LibraryResult(
            $missing
                ? "تم استرجاع «{$resource->title}» لكن ملفه غير موجود على السيرفر — أعد رفعه."
                : "تم استرجاع «{$resource->title}».",
            null,
            ['file_missing' => $missing],
        );
    }

    /** Admin only: remove the row and the file for good. */
    public function forceDeleteResource(LibraryActor $actor, SubjectResource $resource): LibraryResult
    {
        if (! $actor->isAdmin()) {
            throw LibraryException::forbidden('الحذف النهائي متاح للإدارة فقط.');
        }

        $title = $resource->title;
        $this->files->delete($resource);
        $resource->forceDelete();

        return new LibraryResult("تم حذف «{$title}» نهائياً.");
    }

    // ----------------------------------------------------------- units and lessons

    /**
     * @param  array{name_ar: string, name_en?: ?string}  $data
     * @param  array<string, mixed>  $audience  declared scope: ['mode' => 'shared'|'groups', 'group_ids' => []]
     */
    public function createUnit(LibraryActor $actor, Subject $subject, array $data, array $audience = []): EducationalUnit
    {
        $this->assertSubject($actor, $subject->id);

        $stage = EducationalStage::firstOrCreate(
            ['subject_id' => $subject->id],
            ['name_ar' => $subject->name_ar, 'name_en' => $subject->name_en, 'is_active' => true],
        );

        $declared = $this->resolveAudience($actor, $subject, $audience, []);

        return DB::transaction(function () use ($stage, $data, $declared) {
            $unit = $stage->units()->create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
                'is_active' => true,
                'is_shared' => $declared['shared'],
                'sort_order' => ((int) EducationalUnit::where('educational_stage_id', $stage->id)->max('sort_order')) + 1,
            ]);

            $unit->groups()->sync($declared['shared'] ? [] : $declared['groups']);

            return $unit;
        });
    }

    /** @param  array{name_ar: string, name_en?: ?string}  $data */
    public function updateUnit(LibraryActor $actor, EducationalUnit $unit, array $data): EducationalUnit
    {
        $this->assertContainer($actor, $unit);
        $unit->update(['name_ar' => $data['name_ar'], 'name_en' => $data['name_en'] ?? null]);

        return $unit;
    }

    /**
     * @param  array{name_ar: string, name_en?: ?string}  $data
     * @param  array<string, mixed>  $audience
     */
    public function createLesson(LibraryActor $actor, EducationalUnit $unit, array $data, array $audience = []): EducationalLesson
    {
        $subject = $unit->stage->subject;
        $this->assertSubject($actor, $subject->id);

        $declared = $this->resolveAudience($actor, $subject, $audience, []);

        return DB::transaction(function () use ($unit, $data, $declared) {
            $lesson = $unit->lessons()->create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
                'is_active' => true,
                'is_shared' => $declared['shared'],
                'sort_order' => ((int) EducationalLesson::where('educational_unit_id', $unit->id)->max('sort_order')) + 1,
            ]);

            $lesson->groups()->sync($declared['shared'] ? [] : $declared['groups']);

            return $lesson;
        });
    }

    /** @param  array{name_ar: string, name_en?: ?string}  $data */
    public function updateLesson(LibraryActor $actor, EducationalLesson $lesson, array $data): EducationalLesson
    {
        $this->assertContainer($actor, $lesson);
        $lesson->update(['name_ar' => $data['name_ar'], 'name_en' => $data['name_en'] ?? null]);

        return $lesson;
    }

    /**
     * Delete a unit or lesson (soft) together with what lives only inside it.
     * Resources that are also shown somewhere else just lose this placement.
     */
    public function deleteContainer(LibraryActor $actor, EducationalUnit|EducationalLesson $container): LibraryResult
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertContainer($actor, $container);

        $lessonIds = $container instanceof EducationalUnit ? $container->lessons()->pluck('id')->all() : [$container->id];
        $placements = ResourcePlacement::query()
            ->where(function ($q) use ($container, $lessonIds) {
                $q->whereIn('educational_lesson_id', $lessonIds ?: [0]);
                if ($container instanceof EducationalUnit) {
                    $q->orWhere('educational_unit_id', $container->id);
                }
            })->get();

        $resources = SubjectResource::with('groupLinks')->whereIn('id', $placements->pluck('subject_resource_id')->unique())->get();

        foreach ($resources as $resource) {
            if (! $actor->canManageResource($resource)) {
                throw LibraryException::forbidden('يحتوي على موارد تخص مجموعات أخرى، لذلك لا يمكنك حذفه.');
            }
        }

        $before = [...$this->journal->capture($resources), $this->journal->container($container)];

        $now = Carbon::now()->startOfSecond();
        $label = $container instanceof EducationalUnit ? 'الوحدة' : 'الدرس';

        DB::transaction(function () use ($actor, $container, $placements, $resources, $lessonIds, $now) {
            foreach ($placements as $placement) {
                $placement->forceFill(['deleted_at' => $now])->saveQuietly();
            }

            // resources left without any live placement go to the trash with the container
            foreach ($resources as $resource) {
                if (! ResourcePlacement::where('subject_resource_id', $resource->id)->exists()) {
                    $resource->forceFill([
                        'deleted_at' => $now,
                        'deleted_by' => $actor->id(),
                        'deleted_by_type' => $actor->type,
                    ])->saveQuietly();
                }
                $this->placements->syncLegacyColumn($resource);
            }

            if ($container instanceof EducationalUnit && $lessonIds) {
                EducationalLesson::whereIn('id', $lessonIds)->get()->each(
                    fn ($lesson) => $lesson->forceFill(['deleted_at' => $now] + $actor->stamp('deleted_by'))->saveQuietly()
                );
            }

            $container->forceFill(['deleted_at' => $now] + $actor->stamp('deleted_by'))->saveQuietly();
        });

        $trashed = $resources->filter(fn ($r) => SubjectResource::withTrashed()->find($r->id)?->trashed())->count();
        $log = $this->journal->record(
            $actor, 'container.delete', "حذف {$label} «{$container->name_ar}»", $before, $subjectId, $container, ['resources_trashed' => $trashed],
        );

        return new LibraryResult(
            "تم حذف {$label} «{$container->name_ar}»".($trashed ? " و{$trashed} مورد بداخله" : '').' — يمكنك التراجع الآن.',
            $log->uuid,
            ['resources_trashed' => $trashed],
        );
    }

    /** Bring back a deleted unit/lesson together with everything that was deleted with it. */
    public function restoreContainer(LibraryActor $actor, EducationalUnit|EducationalLesson $container): LibraryResult
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertSubject($actor, $subjectId);

        if (! $container->trashed()) {
            return new LibraryResult('العنصر غير محذوف.');
        }

        $stamp = $container->deleted_at;
        $before = [$this->journal->container($container)];

        DB::transaction(function () use ($container, $stamp) {
            $lessonIds = $container instanceof EducationalUnit
                ? EducationalLesson::onlyTrashed()->where('educational_unit_id', $container->id)->where('deleted_at', $stamp)->pluck('id')->all()
                : [$container->id];

            $container->restore();
            $container->forceFill(['deleted_by_type' => null, 'deleted_by_id' => null])->saveQuietly();

            if ($container instanceof EducationalUnit && $lessonIds) {
                EducationalLesson::onlyTrashed()->whereIn('id', $lessonIds)->get()->each(function ($lesson) {
                    $lesson->restore();
                    $lesson->forceFill(['deleted_by_type' => null, 'deleted_by_id' => null])->saveQuietly();
                });
            }

            $placementQuery = ResourcePlacement::onlyTrashed()->where('deleted_at', $stamp)->where(function ($q) use ($container, $lessonIds) {
                $q->whereIn('educational_lesson_id', $lessonIds ?: [0]);
                if ($container instanceof EducationalUnit) {
                    $q->orWhere('educational_unit_id', $container->id);
                }
            });
            $resourceIds = $placementQuery->pluck('subject_resource_id')->unique();
            $placementQuery->get()->each->restore();

            SubjectResource::onlyTrashed()->whereIn('id', $resourceIds)->where('deleted_at', $stamp)->get()->each(function ($resource) {
                $resource->restore();
                $resource->forceFill(['deleted_by' => null, 'deleted_by_type' => null])->saveQuietly();
            });

            foreach (SubjectResource::whereIn('id', $resourceIds)->get() as $resource) {
                $this->placements->syncLegacyColumn($resource);
            }
        });

        $label = $container instanceof EducationalUnit ? 'الوحدة' : 'الدرس';
        $this->journal->record($actor, 'container.restore', "استرجاع {$label} «{$container->name_ar}»", $before, $subjectId, $container);

        return new LibraryResult("تم استرجاع {$label} «{$container->name_ar}» وما حُذف معه.");
    }

    /**
     * Order units / lessons.
     *
     * @param  array<int>  $orderedIds
     */
    public function reorderContainers(LibraryActor $actor, string $type, int $subjectId, array $orderedIds): void
    {
        $this->assertSubject($actor, $subjectId);

        DB::transaction(function () use ($type, $subjectId, $orderedIds) {
            foreach (array_values($orderedIds) as $index => $id) {
                if ($type === 'units') {
                    EducationalUnit::where('id', (int) $id)
                        ->whereHas('stage', fn ($s) => $s->where('subject_id', $subjectId))
                        ->update(['sort_order' => $index + 1]);
                } else {
                    EducationalLesson::where('id', (int) $id)
                        ->whereHas('unit.stage', fn ($s) => $s->where('subject_id', $subjectId))
                        ->update(['sort_order' => $index + 1]);
                }
            }
        });
    }

    // -------------------------------------------------------------------- helpers

    /**
     * Decide the audience of new content.
     *
     *  shared  every group of the subject - only for someone who reaches every group; a teacher
     *          who does not gets the list of THEIR groups instead (never other teachers' students)
     *  groups  exactly the listed groups, which must be groups the actor controls
     *  inherit what the first container it is placed in was declared for (a draft stays a draft);
     *          with no container: everyone for an admin, all own groups for a teacher
     *
     * @param  array<string, mixed>  $audience
     * @param  array<int, EducationalLesson|EducationalUnit|string>  $targets
     * @return array{shared: bool, groups: array<int>}
     */
    private function resolveAudience(LibraryActor $actor, Subject $subject, array $audience, array $targets): array
    {
        $mode = $audience['mode'] ?? 'inherit';
        $mine = $actor->groupIdsFor($subject->id);

        $everything = fn () => $actor->ownsAllGroupsOf($subject->id)
            ? ['shared' => true, 'groups' => []]
            : ['shared' => false, 'groups' => $mine];

        if ($mode === 'shared') {
            return $everything();
        }

        if ($mode === 'groups') {
            $groups = array_values(array_unique(array_map('intval', $audience['group_ids'] ?? [])));
            if (array_diff($groups, $mine) !== []) {
                throw LibraryException::forbidden('يمكنك اختيار مجموعاتك فقط.');
            }

            return ['shared' => false, 'groups' => $groups];
        }

        foreach ($targets as $target) {
            if ($target instanceof EducationalLesson || $target instanceof EducationalUnit) {
                if ($target->is_shared) {
                    return $everything();
                }

                $declared = $target->groups()->pluck('groups.id')->map(fn ($id) => (int) $id)->all();

                return ['shared' => false, 'groups' => $actor->isAdmin() ? $declared : array_values(array_intersect($declared, $mine))];
            }
        }

        return $everything();
    }

    private function assertSubject(LibraryActor $actor, int $subjectId): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
        }
    }

    private function assertPlaceable(LibraryActor $actor, Subject $subject, EducationalLesson|EducationalUnit|string $target): void
    {
        if ($target === PlacementManager::GENERAL) {
            return;
        }

        if ($this->subjectIdOf($target) !== (int) $subject->id) {
            throw LibraryException::invalid('لا يمكن وضع المورد في وحدة أو درس من مادة أخرى.', 'wrong_subject');
        }
    }

    private function assertContainer(LibraryActor $actor, EducationalUnit|EducationalLesson $container): void
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertSubject($actor, $subjectId);

        if (! $actor->canManageContainer($container, $subjectId)) {
            throw LibraryException::forbidden('هذا العنصر مشترك مع مجموعات لا تتبع لك.');
        }
    }

    private function subjectIdOf(EducationalUnit|EducationalLesson $container): int
    {
        $unit = $container instanceof EducationalLesson ? $container->unit : $container;
        $subjectId = $unit?->stage?->subject_id;

        if (! $subjectId) {
            throw LibraryException::notFound();
        }

        return (int) $subjectId;
    }
}
