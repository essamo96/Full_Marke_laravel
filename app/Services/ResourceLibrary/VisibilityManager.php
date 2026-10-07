<?php

namespace App\Services\ResourceLibrary;

use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Group;
use App\Models\ResourceGroupLink;
use App\Models\SubjectResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The visibility engine: WHO sees a resource, and the kill switch.
 *
 * Three independent levers, evaluated by StudentVisibility:
 *
 *  1. kill switch      subject_resources.is_active   off = hidden from EVERY student
 *  2. audience         is_shared (all groups) and the group links
 *                        active link  = the group sees it
 *                        paused link  = the group does NOT see it (overrides "shared")
 *  3. per student      exclusions (ExclusionManager)
 *
 * Admin and teacher call the same methods. The only difference is the reach of the actor:
 * a teacher can add / remove / pause only THEIR groups, and when a resource also serves
 * groups of other teachers, the teacher's "stop showing" pauses their groups instead of
 * flipping the global switch - so nothing they do can change what someone else's students see.
 */
final class VisibilityManager
{
    public function __construct(private readonly LibraryJournal $journal) {}

    // ---------------------------------------------------------------- kill switch

    /**
     * Stop (or resume) showing a resource to students.
     *
     * Reports in `mode` what really happened: `global` (the resource itself was switched) or
     * `groups` (a teacher paused / resumed their own groups on a resource they share with others).
     */
    public function setResourceActive(LibraryActor $actor, SubjectResource $resource, bool $active): LibraryResult
    {
        $this->assertCanSee($actor, $resource);

        $before = [$this->journal->resource($resource)];

        $mode = DB::transaction(fn () => $this->applyActive($actor, $resource, $active));

        $summary = match ($mode) {
            'noop' => 'لا تغيير',
            default => ($active ? 'تشغيل عرض' : 'إيقاف عرض')." «{$resource->title}» للطلاب".($mode === 'groups' ? ' (لمجموعاتك فقط)' : ''),
        };

        $log = $mode === 'noop' ? null : $this->journal->record(
            $actor, $active ? 'resource.activate' : 'resource.deactivate', $summary, $before, (int) $resource->subject_id, $resource, ['mode' => $mode]
        );

        return new LibraryResult(
            $this->activeMessage($resource, $active, $mode),
            $log?->uuid,
            ['mode' => $mode],
        );
    }

    /** Kill switch of a whole unit or lesson (everything inside disappears from students, nothing is deleted). */
    public function setContainerActive(LibraryActor $actor, EducationalUnit|EducationalLesson $container, bool $active): LibraryResult
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertSubject($actor, $subjectId);

        $label = $container instanceof EducationalUnit ? 'الوحدة' : 'الدرس';

        if ($actor->canManageContainer($container, $subjectId)) {
            $before = [$this->journal->container($container)];
            $container->forceFill(['is_active' => $active])->save();

            $log = $this->journal->record(
                $actor, $active ? 'container.activate' : 'container.deactivate',
                ($active ? 'تشغيل عرض ' : 'إيقاف عرض ')."{$label} «{$container->name_ar}»",
                $before, $subjectId, $container, ['mode' => 'global'],
            );

            return new LibraryResult(
                $active ? "تم تشغيل {$label} للطلاب." : "تم إيقاف {$label} — لن يراه أي طالب حتى تعيد تشغيله.",
                $log->uuid,
                ['mode' => 'global'],
            );
        }

        // The container is shared with groups of other teachers: act on the resources inside,
        // for this actor's groups only.
        $resources = $this->resourcesInside($container)->filter(fn ($r) => $actor->canSeeResource($r));

        return $this->bulkSetActive($actor, $subjectId, $resources, $active, ($active ? 'تشغيل ' : 'إيقاف ')."{$label} «{$container->name_ar}» لمجموعاتك");
    }

    /**
     * Switch many resources at once - the "stop every video" button.
     *
     * @param  Collection<int, SubjectResource>  $resources
     */
    public function bulkSetActive(LibraryActor $actor, int $subjectId, Collection $resources, bool $active, ?string $summary = null): LibraryResult
    {
        $this->assertSubject($actor, $subjectId);

        $resources = $resources->filter(fn ($r) => $actor->canSeeResource($r))->values();

        if ($resources->count() > config('resource_library.max_bulk', 500)) {
            throw LibraryException::invalid('عدد العناصر كبير جداً لإجراء واحد، قلّص النطاق وأعد المحاولة.', 'too_many');
        }

        $before = $this->journal->capture($resources);

        $stats = ['global' => 0, 'groups' => 0, 'noop' => 0, 'blocked' => 0];

        DB::transaction(function () use ($actor, $resources, $active, &$stats) {
            foreach ($resources as $resource) {
                try {
                    $stats[$this->applyActive($actor, $resource, $active)]++;
                } catch (LibraryException) {
                    $stats['blocked']++;
                }
            }
        });

        $changed = $stats['global'] + $stats['groups'];
        $summary ??= ($active ? 'تشغيل عرض ' : 'إيقاف عرض ').$changed.' مورد للطلاب';

        $log = $changed
            ? $this->journal->record($actor, $active ? 'bulk.activate' : 'bulk.deactivate', $summary, $before, $subjectId, null, $stats)
            : null;

        $message = $changed === 0
            ? 'لا توجد موارد تحتاج إلى تغيير.'
            : ($active ? "تم تشغيل عرض {$changed} مورد للطلاب." : "تم إيقاف عرض {$changed} مورد عن الطلاب.");

        if ($stats['blocked']) {
            $message .= " (تعذّر تغيير {$stats['blocked']} لأن الإدارة أوقفتها.)";
        }

        return new LibraryResult($message, $log?->uuid, ['changed' => $changed, 'stats' => $stats]);
    }

    /**
     * @return 'global'|'groups'|'noop'
     */
    private function applyActive(LibraryActor $actor, SubjectResource $resource, bool $active): string
    {
        if ($actor->canManageResource($resource)) {
            if ((bool) $resource->is_active === $active) {
                return 'noop';
            }

            $resource->forceFill([
                'is_active' => $active,
                'deactivated_at' => $active ? null : now(),
                'deactivated_by_type' => $active ? null : $actor->type,
                'deactivated_by_id' => $active ? null : $actor->id(),
            ])->save();

            return 'global';
        }

        // A teacher on a resource that also serves other people's groups.
        if (! $resource->is_active) {
            if ($active) {
                throw LibraryException::forbidden('أوقفت الإدارة هذا المورد ولا يمكن تشغيله من حسابك.');
            }

            return 'noop';
        }

        $changed = false;
        foreach ($actor->groupIdsFor((int) $resource->subject_id) as $groupId) {
            $changed = $this->applyGroupPause($actor, $resource, $groupId, ! $active) || $changed;
        }

        return $changed ? 'groups' : 'noop';
    }

    private function activeMessage(SubjectResource $resource, bool $active, string $mode): string
    {
        return match (true) {
            $mode === 'noop' => $active ? 'المورد ظاهر للطلاب بالفعل.' : 'المورد موقوف بالفعل.',
            $mode === 'groups' && $active => "تم تشغيل «{$resource->title}» لمجموعاتك.",
            $mode === 'groups' => "تم إيقاف «{$resource->title}» لمجموعاتك فقط — مجموعات المعلمين الآخرين لم تتأثر.",
            $active => "تم تشغيل «{$resource->title}» — أصبح ظاهراً للطلاب حسب المجموعات.",
            default => "تم إيقاف «{$resource->title}» — لن يراه أي طالب حتى تعيد تشغيله.",
        };
    }

    // ------------------------------------------------------------------- audience

    /** Share with every group of the subject, or go back to an explicit list of groups. */
    public function setShared(LibraryActor $actor, SubjectResource $resource, bool $shared): LibraryResult
    {
        $this->assertCanSee($actor, $resource);
        $subjectId = (int) $resource->subject_id;

        if (! $actor->canManageResource($resource)) {
            throw LibraryException::forbidden('هذا المورد يخدم مجموعات لا تتبع لك، لذلك لا يمكنك تغيير نطاقه العام.');
        }

        if ($shared && ! $actor->ownsAllGroupsOf($subjectId)) {
            throw LibraryException::forbidden('المشاركة مع كل مجموعات المادة متاحة للإدارة، أو للمعلم الذي يملك كل مجموعاتها. اختر مجموعاتك بدلاً من ذلك.');
        }

        if ((bool) $resource->is_shared === $shared) {
            return new LibraryResult($shared ? 'المورد مشترك مع كل المجموعات بالفعل.' : 'المورد محدد بمجموعات بالفعل.');
        }

        $before = [$this->journal->resource($resource)];

        DB::transaction(function () use ($actor, $resource, $shared, $subjectId) {
            if ($shared) {
                // explicit grants become redundant; paused rows stay (they still override sharing)
                ResourceGroupLink::where('subject_resource_id', $resource->id)->where('is_active', true)->delete();
                $resource->forceFill(['is_shared' => true])->save();

                return;
            }

            // "Shared" -> explicit: keep exactly who sees it today by listing every group.
            $paused = $resource->groupLinks()->where('is_active', false)->pluck('group_id')->all();
            $groups = array_diff($actor->allGroupIdsOf($subjectId), $paused);

            $resource->forceFill(['is_shared' => false])->save();
            $this->linkGroups($actor, $resource, $groups);
        });

        $log = $this->journal->record(
            $actor, 'resource.shared', ($shared ? 'مشاركة مع كل المجموعات: ' : 'تحديد المجموعات: ')."«{$resource->title}»",
            $before, $subjectId, $resource,
        );

        return new LibraryResult(
            $shared ? 'أصبح المورد ظاهراً لكل مجموعات المادة.' : 'تم تحويل المورد إلى قائمة مجموعات محددة — احذف الإضافات التي لا تريدها.',
            $log->uuid,
        );
    }

    /**
     * Add groups to the audience without ever duplicating a link (syncWithoutDetaching) and
     * without touching the groups already linked - "upload once, assign anywhere".
     *
     * @param  array<int>  $groupIds
     */
    public function attachGroups(LibraryActor $actor, SubjectResource $resource, array $groupIds): LibraryResult
    {
        $this->assertCanSee($actor, $resource);
        $groupIds = $this->assertGroups($actor, (int) $resource->subject_id, $groupIds);

        $before = [$this->journal->resource($resource)];

        $count = DB::transaction(fn () => $this->applyAttach($actor, $resource, $groupIds));

        if ($count === 0) {
            return new LibraryResult($resource->is_shared
                ? 'المورد مشترك مع كل المجموعات بالفعل.'
                : 'المجموعات المختارة تشاهد المورد بالفعل.');
        }

        $names = Group::whereIn('id', $groupIds)->pluck('name')->join('، ');
        $log = $this->journal->record(
            $actor, 'resource.groups.attach', "مشاركة «{$resource->title}» مع: {$names}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult("تمت مشاركة «{$resource->title}» مع: {$names} — دون إعادة رفع.", $log->uuid, ['changed' => $count]);
    }

    /**
     * Stop showing a resource to groups. Shared content cannot lose a group, so the group is
     * paused instead (it can be resumed with one click); explicit links are detached (soft delete).
     *
     * @param  array<int>  $groupIds
     */
    public function detachGroups(LibraryActor $actor, SubjectResource $resource, array $groupIds): LibraryResult
    {
        $this->assertCanSee($actor, $resource);
        $groupIds = $this->assertGroups($actor, (int) $resource->subject_id, $groupIds);

        $before = [$this->journal->resource($resource)];

        $count = DB::transaction(function () use ($actor, $resource, $groupIds) {
            $count = 0;

            foreach ($groupIds as $groupId) {
                if ($resource->is_shared) {
                    $count += (int) $this->applyGroupPause($actor, $resource, $groupId, true);

                    continue;
                }

                $count += ResourceGroupLink::where('subject_resource_id', $resource->id)->where('group_id', $groupId)->delete();
            }

            return $count;
        });

        if ($count === 0) {
            return new LibraryResult('المجموعات المختارة لا تشاهد المورد أصلاً.');
        }

        $names = Group::whereIn('id', $groupIds)->pluck('name')->join('، ');
        $log = $this->journal->record(
            $actor, 'resource.groups.detach', "إيقاف «{$resource->title}» عن: {$names}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult("لن تشاهد المجموعات التالية «{$resource->title}» بعد الآن: {$names}", $log->uuid, ['changed' => $count]);
    }

    /** Pause / resume the resource for ONE group without losing the link. */
    public function setGroupPaused(LibraryActor $actor, SubjectResource $resource, int $groupId, bool $paused): LibraryResult
    {
        $this->assertCanSee($actor, $resource);
        $this->assertGroups($actor, (int) $resource->subject_id, [$groupId]);

        $before = [$this->journal->resource($resource)];
        $changed = DB::transaction(fn () => $this->applyGroupPause($actor, $resource, $groupId, $paused));

        $name = Group::find($groupId)?->name ?? '';

        if (! $changed) {
            return new LibraryResult($paused ? "«{$name}» لا تشاهد المورد أصلاً." : "«{$name}» تشاهد المورد بالفعل.");
        }

        $log = $this->journal->record(
            $actor, $paused ? 'resource.group.pause' : 'resource.group.resume',
            ($paused ? 'إيقاف ' : 'تشغيل ')."«{$resource->title}» لمجموعة {$name}", $before, (int) $resource->subject_id, $resource,
        );

        return new LibraryResult(
            $paused ? "تم إيقاف المورد لمجموعة «{$name}»." : "عاد المورد ظاهراً لمجموعة «{$name}».",
            $log->uuid,
        );
    }

    // ------------------------------------------------- unit / lesson (bulk) audience

    /**
     * Show everything inside a unit/lesson to more groups, and remember them in the container's
     * declared scope so new content added there starts with the same audience.
     *
     * @param  array<int>  $groupIds
     */
    public function attachGroupsToContainer(LibraryActor $actor, EducationalUnit|EducationalLesson $container, array $groupIds): LibraryResult
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertSubject($actor, $subjectId);
        $groupIds = $this->assertGroups($actor, $subjectId, $groupIds);

        $resources = $this->resourcesInside($container)->filter(fn ($r) => $actor->canSeeResource($r));
        $before = [...$this->journal->capture($resources), $this->journal->container($container)];

        $touched = DB::transaction(function () use ($actor, $container, $groupIds, $resources) {
            $touched = 0;
            foreach ($resources as $resource) {
                $touched += $this->applyAttach($actor, $resource, $groupIds) > 0 ? 1 : 0;
            }

            if (! $container->is_shared) {
                $container->groups()->syncWithoutDetaching($groupIds);
            }

            return $touched;
        });

        $names = Group::whereIn('id', $groupIds)->pluck('name')->join('، ');
        $label = $container instanceof EducationalUnit ? 'الوحدة' : 'الدرس';
        $log = $this->journal->record(
            $actor, 'container.groups.attach', "مشاركة {$label} «{$container->name_ar}» مع: {$names}", $before, $subjectId, $container,
        );

        return new LibraryResult(
            "تمت مشاركة {$label} «{$container->name_ar}» مع: {$names} ({$touched} مورد).",
            $log->uuid,
            ['changed' => $touched],
        );
    }

    /**
     * Stop showing everything inside a unit/lesson to some groups.
     *
     * @param  array<int>  $groupIds
     */
    public function detachGroupsFromContainer(LibraryActor $actor, EducationalUnit|EducationalLesson $container, array $groupIds): LibraryResult
    {
        $subjectId = $this->subjectIdOf($container);
        $this->assertSubject($actor, $subjectId);
        $groupIds = $this->assertGroups($actor, $subjectId, $groupIds);

        $resources = $this->resourcesInside($container)->filter(fn ($r) => $actor->canSeeResource($r));
        $before = [...$this->journal->capture($resources), $this->journal->container($container)];

        $touched = DB::transaction(function () use ($actor, $container, $groupIds, $resources) {
            $touched = 0;
            foreach ($resources as $resource) {
                $any = false;
                foreach ($groupIds as $groupId) {
                    if ($resource->is_shared) {
                        $any = $this->applyGroupPause($actor, $resource, $groupId, true) || $any;
                    } else {
                        $any = ResourceGroupLink::where('subject_resource_id', $resource->id)->where('group_id', $groupId)->delete() > 0 || $any;
                    }
                }
                $touched += $any ? 1 : 0;
            }

            if (! $container->is_shared) {
                $container->groups()->detach($groupIds);
            }

            return $touched;
        });

        $names = Group::whereIn('id', $groupIds)->pluck('name')->join('، ');
        $label = $container instanceof EducationalUnit ? 'الوحدة' : 'الدرس';
        $log = $this->journal->record(
            $actor, 'container.groups.detach', "إيقاف {$label} «{$container->name_ar}» عن: {$names}", $before, $subjectId, $container,
        );

        return new LibraryResult(
            "لن تشاهد المجموعات التالية {$label} «{$container->name_ar}» بعد الآن: {$names} ({$touched} مورد).",
            $log->uuid,
            ['changed' => $touched],
        );
    }

    /**
     * Audience primitive for code that creates content (no authorization, no journal entry):
     * link the groups without ever duplicating a link.
     *
     * @param  array<int>  $groupIds
     */
    public function grantAudience(LibraryActor $actor, SubjectResource $resource, array $groupIds): int
    {
        return $this->linkGroups($actor, $resource, array_values(array_unique(array_map('intval', $groupIds))));
    }

    // ------------------------------------------------------------ internal helpers

    /**
     * Add groups to the audience of a resource. Returns how many groups actually changed.
     *
     * @param  array<int>  $groupIds
     */
    private function applyAttach(LibraryActor $actor, SubjectResource $resource, array $groupIds): int
    {
        if ($resource->is_shared) {
            // Everyone already sees it - the only thing left to do is to undo a pause.
            return $this->setLinksActive($resource, $groupIds, true, deleteWhenShared: true);
        }

        return $this->linkGroups($actor, $resource, $groupIds);
    }

    /**
     * Link groups to a resource: restores soft-deleted links, un-pauses paused ones and creates
     * the missing ones with syncWithoutDetaching() so a group is never linked twice.
     *
     * @param  array<int>  $groupIds
     */
    private function linkGroups(LibraryActor $actor, SubjectResource $resource, array $groupIds): int
    {
        if ($groupIds === []) {
            return 0;
        }

        $changed = 0;

        // bring back links that were detached earlier (the unique key forbids a second row)
        $trashed = ResourceGroupLink::onlyTrashed()
            ->where('subject_resource_id', $resource->id)
            ->whereIn('group_id', $groupIds)
            ->get();
        foreach ($trashed as $link) {
            $link->restore();
            $link->forceFill(['is_active' => true])->save();
            $changed++;
        }

        // un-pause the ones that are linked but paused
        $changed += ResourceGroupLink::where('subject_resource_id', $resource->id)
            ->whereIn('group_id', $groupIds)
            ->where('is_active', false)
            ->update(['is_active' => true]);

        // add whatever is still missing
        $result = $resource->groups()->syncWithoutDetaching(
            collect($groupIds)->mapWithKeys(fn ($id) => [$id => ['is_active' => true] + $actor->stamp()])->all()
        );

        return $changed + count($result['attached']);
    }

    /**
     * Pause (or resume) one group. A shared resource has no link to pause, so a "deny" row is
     * created (and removed again on resume); an explicitly linked resource just flips the link.
     *
     * @return bool whether anything changed
     */
    private function applyGroupPause(LibraryActor $actor, SubjectResource $resource, int $groupId, bool $paused): bool
    {
        $link = ResourceGroupLink::where('subject_resource_id', $resource->id)->where('group_id', $groupId)->first();

        if ($paused) {
            if ($link) {
                if (! $link->is_active) {
                    return false;
                }
                $link->forceFill(['is_active' => false])->save();

                return true;
            }

            if (! $resource->is_shared) {
                return false; // the group never saw it - nothing to pause
            }

            $trashed = ResourceGroupLink::onlyTrashed()->where('subject_resource_id', $resource->id)->where('group_id', $groupId)->first();
            if ($trashed) {
                $trashed->restore();
                $trashed->forceFill(['is_active' => false])->save();
            } else {
                ResourceGroupLink::create([
                    'subject_resource_id' => $resource->id,
                    'group_id' => $groupId,
                    'is_active' => false,
                ] + $actor->stamp());
            }

            return true;
        }

        if (! $link || $link->is_active) {
            return false;
        }

        if ($resource->is_shared) {
            $link->delete(); // a deny row on shared content: removing it resumes the group
        } else {
            $link->forceFill(['is_active' => true])->save();
        }

        return true;
    }

    /**
     * @param  array<int>  $groupIds
     */
    private function setLinksActive(SubjectResource $resource, array $groupIds, bool $active, bool $deleteWhenShared = false): int
    {
        $query = ResourceGroupLink::where('subject_resource_id', $resource->id)
            ->whereIn('group_id', $groupIds)
            ->where('is_active', ! $active);

        return $deleteWhenShared && $resource->is_shared ? $query->delete() : $query->update(['is_active' => $active]);
    }

    /** @return Collection<int, SubjectResource> every resource shown inside a unit/lesson. */
    private function resourcesInside(EducationalUnit|EducationalLesson $container): Collection
    {
        $query = SubjectResource::query()->with('groupLinks');

        if ($container instanceof EducationalLesson) {
            return $query->whereHas('placements', fn ($p) => $p->where('educational_lesson_id', $container->id))->get();
        }

        return $query->whereHas('placements', fn ($p) => $p
            ->where('educational_unit_id', $container->id)
            ->orWhereIn('educational_lesson_id', $container->lessons()->pluck('id')))->get();
    }

    /**
     * @param  array<int>  $groupIds
     * @return array<int>
     */
    private function assertGroups(LibraryActor $actor, int $subjectId, array $groupIds): array
    {
        $groupIds = array_values(array_unique(array_filter(array_map('intval', $groupIds))));

        if ($groupIds === []) {
            throw LibraryException::invalid('اختر مجموعة واحدة على الأقل.', 'no_groups');
        }

        $valid = Group::whereIn('id', $groupIds)->where('subject_id', $subjectId)->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (count($valid) !== count($groupIds)) {
            throw LibraryException::invalid('إحدى المجموعات لا تتبع مادة هذا المحتوى.', 'wrong_subject');
        }

        foreach ($groupIds as $groupId) {
            if (! $actor->canManageGroup($subjectId, $groupId)) {
                throw LibraryException::forbidden('يمكنك التحكم في مجموعاتك فقط.');
            }
        }

        return $groupIds;
    }

    private function assertCanSee(LibraryActor $actor, SubjectResource $resource): void
    {
        if (! $actor->canSeeResource($resource)) {
            throw LibraryException::forbidden();
        }
    }

    private function assertSubject(LibraryActor $actor, int $subjectId): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
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
