<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\ResourceAuditLog;
use App\Models\ResourceGroupLink;
use App\Models\ResourcePlacement;
use App\Models\StudentContentExclusion;
use App\Models\SubjectResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Audit trail + undo for every change made through the resource library.
 *
 * Before an action runs, the manager takes a SNAPSHOT of every row it is about to touch
 * (the resource and its group links, placements and exclusions; or a unit/lesson and its
 * declared groups and exclusions). The snapshot is stored with the action. Undo does not
 * try to "reverse" the action - it puts each snapshotted entity back into exactly the state
 * it was in, which works the same for a delete, a kill switch, a share or a bulk action.
 *
 * Because links and placements are soft-deleted, nothing is ever lost between the action
 * and the undo.
 */
final class LibraryJournal
{
    private const RESOURCE_ATTRS = [
        'is_active', 'is_shared', 'sort_order', 'educational_lesson_id',
        'deleted_at', 'deleted_by', 'deleted_by_type',
        'deactivated_at', 'deactivated_by_type', 'deactivated_by_id',
    ];

    private const CONTAINER_ATTRS = ['is_active', 'is_shared', 'sort_order', 'deleted_at', 'deleted_by_type', 'deleted_by_id'];

    private const LINK_ATTRS = ['group_id', 'is_active', 'created_by_type', 'created_by_id', 'deleted_at', 'created_at', 'updated_at'];

    private const PLACEMENT_ATTRS = [
        'educational_unit_id', 'educational_lesson_id', 'target_key', 'sort_order', 'is_active',
        'created_by_type', 'created_by_id', 'deleted_at', 'created_at', 'updated_at',
    ];

    private const EXCLUSION_ATTRS = [
        'student_id', 'subject_id', 'reason', 'excluded_by_type', 'excluded_by_id', 'created_at', 'updated_at',
    ];

    // ---------------------------------------------------------------- snapshots

    /** @return array<string, mixed> */
    public function resource(SubjectResource $resource): array
    {
        $id = $resource->getKey();
        $row = SubjectResource::withTrashed()->findOrFail($id);

        return [
            'kind' => 'resource',
            'id' => $id,
            'attrs' => Arr::only($row->getAttributes(), self::RESOURCE_ATTRS),
            'links' => ResourceGroupLink::withTrashed()->where('subject_resource_id', $id)->get()
                ->map(fn ($link) => Arr::only($link->getAttributes(), self::LINK_ATTRS))->all(),
            'placements' => ResourcePlacement::withTrashed()->where('subject_resource_id', $id)->get()
                ->map(fn ($placement) => Arr::only($placement->getAttributes(), self::PLACEMENT_ATTRS))->all(),
            'exclusions' => $this->exclusionRows($row),
        ];
    }

    /** @return array<string, mixed> */
    public function container(EducationalUnit|EducationalLesson $container): array
    {
        $row = $container::withTrashed()->findOrFail($container->getKey());

        return [
            'kind' => $container instanceof EducationalUnit ? 'unit' : 'lesson',
            'id' => $row->getKey(),
            'attrs' => Arr::only($row->getAttributes(), self::CONTAINER_ATTRS),
            'groups' => $row->groups()->pluck('groups.id')->map(fn ($id) => (int) $id)->all(),
            'exclusions' => $this->exclusionRows($row),
        ];
    }

    /**
     * @param  iterable<Model>  $models  resources, units or lessons
     * @return array<int, array<string, mixed>>
     */
    public function capture(iterable $models): array
    {
        $snapshots = [];

        foreach ($models as $model) {
            $snapshots[] = $model instanceof SubjectResource
                ? $this->resource($model)
                : $this->container($model);
        }

        return $snapshots;
    }

    // ------------------------------------------------------------------ journal

    /**
     * Open batches: while one is open, record() does not write a row of its own - it hands its
     * snapshots to the batch, which writes ONE entry when it closes. A "fix everything" button
     * made of twenty small actions therefore still shows a single Undo.
     *
     * @var array<int, array<int, array<string, mixed>>>
     */
    private array $batches = [];

    /**
     * Run several journaled actions as one undoable action.
     *
     * @param  callable(): void  $callback
     */
    public function batch(LibraryActor $actor, string $action, string $summary, ?int $subjectId, callable $callback): ?ResourceAuditLog
    {
        $this->batches[] = [];

        try {
            DB::transaction($callback);
        } catch (\Throwable $e) {
            array_pop($this->batches);

            throw $e;
        }

        $snapshots = array_pop($this->batches);

        // the OLDEST snapshot of an entity is the state to go back to
        $unique = [];
        foreach ($snapshots as $snapshot) {
            $unique[$snapshot['kind'].':'.$snapshot['id']] ??= $snapshot;
        }

        if ($unique === []) {
            return null;
        }

        if ($this->batches !== []) {
            // nested batch: hand everything to the parent
            array_push($this->batches[array_key_last($this->batches)], ...array_values($unique));

            return null;
        }

        return $this->record($actor, $action, $summary, array_values($unique), $subjectId);
    }

    /**
     * @param  array<int, array<string, mixed>>  $before  snapshots taken before the action
     * @param  array<string, mixed>  $meta
     */
    public function record(
        LibraryActor $actor,
        string $action,
        string $summary,
        array $before,
        ?int $subjectId,
        ?Model $target = null,
        array $meta = [],
    ): ResourceAuditLog {
        if ($this->batches !== []) {
            array_push($this->batches[array_key_last($this->batches)], ...$before);

            return new ResourceAuditLog; // unsaved: the batch writes the real entry
        }

        return ResourceAuditLog::create([
            'actor_type' => $actor->type,
            'actor_id' => $actor->id(),
            'subject_id' => $subjectId,
            'action' => $action,
            'target_type' => $target ? class_basename($target) : null,
            'target_id' => $target?->getKey(),
            'summary' => mb_substr($summary, 0, 255),
            'before' => $before,
            'meta' => $meta ?: null,
        ]);
    }

    /** Journal an action that has nothing to snapshot (e.g. a brand-new upload). */
    public function note(LibraryActor $actor, string $action, string $summary, ?int $subjectId, ?Model $target = null, array $meta = []): ResourceAuditLog
    {
        return $this->record($actor, $action, $summary, [], $subjectId, $target, $meta);
    }

    // --------------------------------------------------------------------- undo

    public function undo(LibraryActor $actor, string $token): LibraryResult
    {
        $log = ResourceAuditLog::where('uuid', $token)->first();

        if (! $log) {
            throw LibraryException::notFound('لا يوجد إجراء للتراجع عنه.');
        }

        $mine = $actor->isAdmin() || $actor->is($log->actor_type, $log->actor_id);
        if (! $mine || ($log->subject_id && ! $actor->canAccessSubject((int) $log->subject_id))) {
            throw LibraryException::forbidden('لا يمكنك التراجع عن إجراء قام به شخص آخر.');
        }

        if ($log->isUndone()) {
            throw LibraryException::invalid('تم التراجع عن هذا الإجراء مسبقاً.', 'already_undone');
        }

        $window = (int) config('resource_library.undo.window_seconds', 900);
        if ($log->created_at->diffInSeconds(now()) > $window) {
            throw LibraryException::invalid('انتهت مهلة التراجع عن هذا الإجراء.', 'undo_expired');
        }

        if (empty($log->before)) {
            throw LibraryException::invalid('هذا الإجراء لا يدعم التراجع.', 'not_undoable');
        }

        DB::transaction(function () use ($log, $actor) {
            foreach ($log->before as $snapshot) {
                $this->restore($snapshot);
            }

            $log->forceFill([
                'undone_at' => now(),
                'undone_by_type' => $actor->type,
                'undone_by_id' => $actor->id(),
            ])->save();
        });

        return new LibraryResult('تم التراجع: '.$log->summary, null, [
            'action' => $log->action,
            'subject_id' => $log->subject_id,
        ]);
    }

    /**
     * Put the entity described by a snapshot back exactly as it was.
     *
     * @param  array<string, mixed>  $snapshot
     */
    private function restore(array $snapshot): void
    {
        match ($snapshot['kind']) {
            'resource' => $this->restoreResource($snapshot),
            'unit', 'lesson' => $this->restoreContainer($snapshot),
            default => null,
        };
    }

    /** @param  array<string, mixed>  $snapshot */
    private function restoreResource(array $snapshot): void
    {
        $resource = SubjectResource::withTrashed()->find($snapshot['id']);
        if (! $resource) {
            return; // purged for good since - nothing to put back
        }

        $resource->forceFill($snapshot['attrs'])->saveQuietly();

        // group links, keyed by group (unique key resource + group)
        $existing = ResourceGroupLink::withTrashed()->where('subject_resource_id', $resource->id)->get()->keyBy('group_id');
        foreach ($snapshot['links'] as $row) {
            $link = $existing->get($row['group_id']) ?? new ResourceGroupLink([
                'subject_resource_id' => $resource->id,
                'group_id' => $row['group_id'],
            ]);
            $link->forceFill(Arr::except($row, ['group_id']))->save();
        }
        ResourceGroupLink::withTrashed()
            ->where('subject_resource_id', $resource->id)
            ->when($snapshot['links'], fn ($q) => $q->whereNotIn('group_id', Arr::pluck($snapshot['links'], 'group_id')))
            ->forceDelete();

        // placements, keyed by target (unique key resource + target_key)
        $existing = ResourcePlacement::withTrashed()->where('subject_resource_id', $resource->id)->get()->keyBy('target_key');
        foreach ($snapshot['placements'] as $row) {
            $placement = $existing->get($row['target_key']) ?? new ResourcePlacement(['subject_resource_id' => $resource->id]);
            $placement->forceFill($row)->save();
        }
        ResourcePlacement::withTrashed()
            ->where('subject_resource_id', $resource->id)
            ->when($snapshot['placements'], fn ($q) => $q->whereNotIn('target_key', Arr::pluck($snapshot['placements'], 'target_key')))
            ->forceDelete();

        $this->restoreExclusions($resource, $snapshot['exclusions']);
    }

    /** @param  array<string, mixed>  $snapshot */
    private function restoreContainer(array $snapshot): void
    {
        $class = $snapshot['kind'] === 'unit' ? EducationalUnit::class : EducationalLesson::class;
        $container = $class::withTrashed()->find($snapshot['id']);
        if (! $container) {
            return;
        }

        $container->forceFill($snapshot['attrs'])->saveQuietly();
        $container->groups()->sync($snapshot['groups']);
        $this->restoreExclusions($container, $snapshot['exclusions']);
    }

    /** @return array<int, array<string, mixed>> */
    private function exclusionRows(Model $target): array
    {
        return StudentContentExclusion::query()
            ->where('excludable_type', $target->getMorphClass())
            ->where('excludable_id', $target->getKey())
            ->get()
            ->map(fn ($row) => Arr::only($row->getAttributes(), self::EXCLUSION_ATTRS))
            ->all();
    }

    /** @param  array<int, array<string, mixed>>  $rows */
    private function restoreExclusions(Model $target, array $rows): void
    {
        StudentContentExclusion::query()
            ->where('excludable_type', $target->getMorphClass())
            ->where('excludable_id', $target->getKey())
            ->delete();

        foreach ($rows as $row) {
            (new StudentContentExclusion)->forceFill($row + [
                'excludable_type' => $target->getMorphClass(),
                'excludable_id' => $target->getKey(),
            ])->save();
        }
    }
}
