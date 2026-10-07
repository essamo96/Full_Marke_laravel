<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\ResourcePlacement;
use App\Models\SubjectResource;
use Illuminate\Support\Facades\DB;

/**
 * WHERE a resource appears: any number of lessons, units, or the subject's "general" area.
 *
 * Showing a video in a second lesson adds a placement - the file is never uploaded twice.
 * Removing a placement soft-deletes it (undoable), and a resource is never allowed to lose
 * its last placement by accident: an unplaced resource is visible to nobody, so the caller
 * must either keep one placement or explicitly ask for the general area.
 *
 * A "target" is an EducationalLesson, an EducationalUnit, or the string 'general'.
 */
final class PlacementManager
{
    public const GENERAL = 'general';

    public function __construct(private readonly LibraryJournal $journal) {}

    /**
     * Show the resource in more places.
     *
     * @param  array<int, EducationalLesson|EducationalUnit|string>  $targets
     */
    public function attach(LibraryActor $actor, SubjectResource $resource, array $targets): LibraryResult
    {
        $this->assertCanManage($actor, $resource);
        $targets = $this->normalize($resource, $targets);

        $before = [$this->journal->resource($resource)];

        $added = DB::transaction(function () use ($actor, $resource, $targets) {
            $added = 0;
            foreach ($targets as $target) {
                $added += $this->place($actor, $resource, $target) ? 1 : 0;
            }
            $this->syncLegacyColumn($resource);

            return $added;
        });

        if ($added === 0) {
            return new LibraryResult('المورد موجود في هذه الأماكن بالفعل.');
        }

        $labels = collect($targets)->map(fn ($t) => $this->label($t))->join('، ');
        $log = $this->journal->record(
            $actor, 'resource.placements.attach', "إضافة «{$resource->title}» إلى: {$labels}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult("تمت إضافة «{$resource->title}» إلى: {$labels} — دون إعادة رفع.", $log->uuid, ['changed' => $added]);
    }

    /**
     * Remove the resource from some places.
     *
     * @param  array<int, EducationalLesson|EducationalUnit|string>  $targets
     */
    public function detach(LibraryActor $actor, SubjectResource $resource, array $targets, bool $moveToGeneralIfLast = false): LibraryResult
    {
        $this->assertCanManage($actor, $resource);
        $targets = $this->normalize($resource, $targets);
        $keys = array_map(fn ($t) => $this->keyOf($t), $targets);

        $live = $resource->placements()->get();
        $remaining = $live->reject(fn ($p) => in_array($p->target_key, $keys, true));

        if ($remaining->isEmpty() && $live->isNotEmpty() && ! $moveToGeneralIfLast) {
            throw LibraryException::invalid(
                'هذا هو آخر مكان لعرض المورد. اتركه في مكان واحد على الأقل، أو انقله إلى «المرفقات العامة».',
                'last_placement',
                ['can_move_to_general' => true],
            );
        }

        $before = [$this->journal->resource($resource)];

        $removed = DB::transaction(function () use ($actor, $resource, $keys, $remaining, $moveToGeneralIfLast) {
            $keysToRemove = $keys;

            if ($remaining->isEmpty() && $moveToGeneralIfLast) {
                // the resource lands in the general area instead of becoming unplaced
                $this->place($actor, $resource, self::GENERAL);
                $keysToRemove = array_values(array_diff($keys, [ResourcePlacement::GENERAL]));
            }

            $removed = ResourcePlacement::where('subject_resource_id', $resource->id)
                ->whereIn('target_key', $keysToRemove)
                ->delete();

            $this->syncLegacyColumn($resource);

            return $removed;
        });

        if ($removed === 0) {
            return new LibraryResult('المورد غير موجود في هذه الأماكن أصلاً.');
        }

        $labels = collect($targets)->map(fn ($t) => $this->label($t))->join('، ');
        $log = $this->journal->record(
            $actor, 'resource.placements.detach', "إزالة «{$resource->title}» من: {$labels}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult("تمت إزالة «{$resource->title}» من: {$labels}.", $log->uuid, ['changed' => $removed]);
    }

    /** Hide / show the resource in ONE of its places (the placement's own kill switch). */
    public function setActive(LibraryActor $actor, SubjectResource $resource, EducationalLesson|EducationalUnit|string $target, bool $active): LibraryResult
    {
        $this->assertCanManage($actor, $resource);
        $key = $this->keyOf($target);

        $placement = ResourcePlacement::where('subject_resource_id', $resource->id)->where('target_key', $key)->first();
        if (! $placement) {
            throw LibraryException::notFound('المورد غير موجود في هذا المكان.');
        }

        if ($placement->is_active === $active) {
            return new LibraryResult($active ? 'المورد ظاهر في هذا المكان بالفعل.' : 'المورد مخفي في هذا المكان بالفعل.');
        }

        $before = [$this->journal->resource($resource)];
        $placement->forceFill(['is_active' => $active])->save();

        $label = $this->label($target);
        $log = $this->journal->record(
            $actor, $active ? 'resource.placement.show' : 'resource.placement.hide',
            ($active ? 'إظهار' : 'إخفاء')." «{$resource->title}» في {$label}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult(
            $active ? "عاد «{$resource->title}» ظاهراً في {$label}." : "تم إخفاء «{$resource->title}» في {$label} فقط — يبقى ظاهراً في أماكنه الأخرى.",
            $log->uuid,
        );
    }

    /**
     * Order the resources of one lesson / unit / the general area.
     *
     * @param  array<int>  $orderedResourceIds
     */
    public function reorder(LibraryActor $actor, EducationalLesson|EducationalUnit|string $container, int $subjectId, array $orderedResourceIds): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
        }

        $key = $this->keyOf($container);

        DB::transaction(function () use ($orderedResourceIds, $key, $subjectId) {
            foreach (array_values($orderedResourceIds) as $index => $resourceId) {
                ResourcePlacement::query()
                    ->where('subject_resource_id', (int) $resourceId)
                    ->where('target_key', $key)
                    ->whereHas('resource', fn ($r) => $r->where('subject_id', $subjectId))
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    // ------------------------------------------------------------------ primitives

    /**
     * Create (or bring back) a placement. No authorization, no journal - callers do that.
     * Returns false when the resource was already there.
     */
    public function place(LibraryActor $actor, SubjectResource $resource, EducationalLesson|EducationalUnit|string $target): bool
    {
        $key = $this->keyOf($target);

        $existing = ResourcePlacement::withTrashed()
            ->where('subject_resource_id', $resource->id)
            ->where('target_key', $key)
            ->first();

        if ($existing && ! $existing->trashed()) {
            return false;
        }

        $sort = $this->nextSortOrder($target, (int) $resource->subject_id);

        if ($existing) {
            $existing->restore();
            $existing->forceFill(['is_active' => true, 'sort_order' => $sort])->save();

            return true;
        }

        ResourcePlacement::create([
            'subject_resource_id' => $resource->id,
            'educational_unit_id' => $target instanceof EducationalUnit ? $target->id : null,
            'educational_lesson_id' => $target instanceof EducationalLesson ? $target->id : null,
            'sort_order' => $sort,
            'is_active' => true,
        ] + $actor->stamp());

        return true;
    }

    /**
     * Keep subject_resources.educational_lesson_id (the legacy "primary lesson") pointing at the
     * first lesson the resource is placed in, so old readers and a rollback still make sense.
     */
    public function syncLegacyColumn(SubjectResource $resource): void
    {
        $primary = ResourcePlacement::where('subject_resource_id', $resource->id)
            ->whereNotNull('educational_lesson_id')
            ->orderBy('sort_order')->orderBy('id')
            ->value('educational_lesson_id');

        if ((int) $resource->educational_lesson_id !== (int) $primary) {
            $resource->forceFill(['educational_lesson_id' => $primary])->saveQuietly();
        }
    }

    private function nextSortOrder(EducationalLesson|EducationalUnit|string $target, int $subjectId): int
    {
        $query = ResourcePlacement::query();

        match (true) {
            $target instanceof EducationalLesson => $query->where('educational_lesson_id', $target->id),
            $target instanceof EducationalUnit => $query->where('educational_unit_id', $target->id)->whereNull('educational_lesson_id'),
            default => $query->where('target_key', ResourcePlacement::GENERAL)
                ->whereHas('resource', fn ($r) => $r->where('subject_id', $subjectId)),
        };

        return ((int) $query->max('sort_order')) + 1;
    }

    // --------------------------------------------------------------------- helpers

    /**
     * @param  array<int, EducationalLesson|EducationalUnit|string>  $targets
     * @return array<int, EducationalLesson|EducationalUnit|string>
     */
    private function normalize(SubjectResource $resource, array $targets): array
    {
        if ($targets === []) {
            throw LibraryException::invalid('اختر مكاناً واحداً على الأقل.', 'no_targets');
        }

        $unique = [];
        foreach ($targets as $target) {
            if ($target !== self::GENERAL && ! $target instanceof EducationalLesson && ! $target instanceof EducationalUnit) {
                throw LibraryException::invalid('مكان غير معروف.', 'bad_target');
            }

            if ($target instanceof EducationalLesson || $target instanceof EducationalUnit) {
                $subjectId = ($target instanceof EducationalLesson ? $target->unit : $target)?->stage?->subject_id;
                if ((int) $subjectId !== (int) $resource->subject_id) {
                    throw LibraryException::invalid('لا يمكن وضع المورد في وحدة أو درس من مادة أخرى.', 'wrong_subject');
                }
            }

            $unique[$this->keyOf($target)] = $target;
        }

        return array_values($unique);
    }

    private function keyOf(EducationalLesson|EducationalUnit|string $target): string
    {
        return match (true) {
            $target instanceof EducationalLesson => ResourcePlacement::keyFor(null, $target->id),
            $target instanceof EducationalUnit => ResourcePlacement::keyFor($target->id, null),
            $target === self::GENERAL => ResourcePlacement::GENERAL,
            default => throw LibraryException::invalid('مكان غير معروف.', 'bad_target'),
        };
    }

    private function label(EducationalLesson|EducationalUnit|string $target): string
    {
        return match (true) {
            $target instanceof EducationalLesson => ($target->unit?->name_ar ? $target->unit->name_ar.' › ' : '').$target->name_ar,
            $target instanceof EducationalUnit => $target->name_ar,
            default => 'المرفقات العامة',
        };
    }

    private function assertCanManage(LibraryActor $actor, SubjectResource $resource): void
    {
        if (! $actor->canManageResource($resource)) {
            throw LibraryException::forbidden('هذا المورد يخدم مجموعات لا تتبع لك، لذلك لا يمكنك تغيير أماكن ظهوره.');
        }
    }
}
