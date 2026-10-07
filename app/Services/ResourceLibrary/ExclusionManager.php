<?php

namespace App\Services\ResourceLibrary;

use App\Models\Admin;
use App\Models\EducationalLesson;
use App\Models\EducationalUnit;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentContentExclusion;
use App\Models\SubjectResource;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Per-student exclusions: "this student must not see this video / lesson / unit".
 *
 * An exclusion always wins - over group sharing, "shared with all groups" and personal
 * grants - and it cascades: excluding a student from a unit hides everything inside it.
 * The student-facing queries and every direct URL (stream / file / link / download) go
 * through the same gate, so the unit and its videos disappear everywhere, not only from
 * the listing page.
 *
 * Admin and teacher use the same calls; a teacher can only exclude students registered in
 * one of their own groups, and an exclusion they never touch (an admin's, or a colleague's
 * on a student of another group) is left alone.
 */
final class ExclusionManager
{
    /** Registration statuses that count as "currently enrolled". */
    public const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    public function __construct(private readonly LibraryJournal $journal) {}

    // ------------------------------------------------------------------- reading

    /**
     * Rows of the "students" table: everyone the actor may exclude who could see the target,
     * plus anyone already excluded, each with the state of their toggle.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function students(LibraryActor $actor, SubjectResource|EducationalUnit|EducationalLesson $target, bool $everyone = false): Collection
    {
        $subjectId = $this->subjectIdOf($target);
        $this->assertAccess($actor, $subjectId, $target);

        $pool = $actor->studentIdsFor($subjectId);
        $audience = $everyone ? null : $this->audienceGroupIds($target, $subjectId);

        $excludedRows = StudentContentExclusion::query()
            ->where('excludable_type', $target->getMorphClass())
            ->where('excludable_id', $target->getKey())
            ->get()
            ->keyBy('student_id');

        $registrations = Registration::query()
            ->where('subject_id', $subjectId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->with(['student:id,full_name_ar,full_name_en', 'group:id,name'])
            ->when($pool !== null, fn ($q) => $q->whereIn('student_id', $pool ?: [0]))
            ->when($audience !== null, fn ($q) => $q->where(function ($w) use ($audience, $excludedRows) {
                $w->whereIn('group_id', $audience ?: [0])->orWhereIn('student_id', $excludedRows->keys()->all() ?: [0]);
            }))
            ->get()
            ->groupBy('student_id');

        $inherited = $target instanceof SubjectResource ? $this->inheritedExclusions($target) : collect();
        $excluders = $this->excluderNames($excludedRows);

        return $registrations->map(function (Collection $regs, $studentId) use ($excludedRows, $inherited, $excluders) {
            $registration = $regs->first();
            $student = $registration->student;
            $row = $excludedRows->get($studentId);

            return [
                'student_id' => (int) $studentId,
                'name' => $student ? ($student->full_name_ar ?: $student->full_name_en) : '#'.$studentId,
                'group_id' => $registration->group_id ? (int) $registration->group_id : null,
                'group' => $regs->pluck('group.name')->filter()->unique()->join('، ') ?: 'بدون مجموعة',
                'excluded' => $row !== null,
                'excluded_by' => $row ? ($excluders[$row->excluded_by_type.':'.$row->excluded_by_id] ?? null) : null,
                'excluded_by_type' => $row?->excluded_by_type,
                'inherited' => $inherited->get($studentId),
            ];
        })->sortBy('name')->values();
    }

    // ------------------------------------------------------------------- writing

    /** Exclude (or let back in) ONE student - what a row's toggle switch does. */
    public function set(LibraryActor $actor, SubjectResource|EducationalUnit|EducationalLesson $target, int $studentId, bool $excluded): LibraryResult
    {
        return $this->setMany($actor, $target, [$studentId], $excluded);
    }

    /**
     * Exclude / re-include several students at once.
     *
     * @param  array<int>  $studentIds
     */
    public function setMany(LibraryActor $actor, SubjectResource|EducationalUnit|EducationalLesson $target, array $studentIds, bool $excluded): LibraryResult
    {
        $subjectId = $this->subjectIdOf($target);
        $this->assertAccess($actor, $subjectId, $target);

        $studentIds = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if ($studentIds === []) {
            throw LibraryException::invalid('اختر طالباً واحداً على الأقل.', 'no_students');
        }

        $this->assertStudents($actor, $subjectId, $studentIds, mustBeEnrolled: $excluded);

        $before = [$this->snapshot($target)];

        $changed = DB::transaction(function () use ($actor, $target, $subjectId, $studentIds, $excluded) {
            $changed = 0;

            foreach ($studentIds as $studentId) {
                $query = StudentContentExclusion::query()
                    ->where('excludable_type', $target->getMorphClass())
                    ->where('excludable_id', $target->getKey())
                    ->where('student_id', $studentId);

                if ($excluded) {
                    if (! $query->exists()) {
                        StudentContentExclusion::create([
                            'student_id' => $studentId,
                            'subject_id' => $subjectId,
                            'excludable_type' => $target->getMorphClass(),
                            'excludable_id' => $target->getKey(),
                        ] + $actor->stamp('excluded_by'));
                        $changed++;
                    }
                } else {
                    $changed += $query->delete();
                }
            }

            return $changed;
        });

        $names = Student::whereIn('id', $studentIds)->get()->map(fn ($s) => $s->full_name_ar ?: $s->full_name_en)->take(3)->join('، ');
        $more = count($studentIds) > 3 ? ' و'.(count($studentIds) - 3).' آخرين' : '';
        $label = $this->label($target);

        if ($changed === 0) {
            return new LibraryResult($excluded ? 'الطلاب المختارون مستثنون بالفعل.' : 'الطلاب المختارون غير مستثنين أصلاً.');
        }

        $log = $this->journal->record(
            $actor,
            $excluded ? 'exclusion.add' : 'exclusion.remove',
            ($excluded ? 'استثناء ' : 'إلغاء استثناء ')."{$names}{$more} من {$label}",
            $before, $subjectId, $target,
        );

        return new LibraryResult(
            $excluded
                ? "تم استثناء {$names}{$more} — لن يظهر لهم {$label}".($target instanceof SubjectResource ? '.' : ' ولا ما بداخله.')
                : "تم إلغاء استثناء {$names}{$more} من {$label}.",
            $log->uuid,
            ['changed' => $changed],
        );
    }

    /**
     * Make the exclusion list exactly `$studentIds` for the students the actor controls.
     * Exclusions on students outside the actor's reach are preserved untouched.
     *
     * @param  array<int>  $studentIds
     */
    public function replace(LibraryActor $actor, SubjectResource|EducationalUnit|EducationalLesson $target, array $studentIds): LibraryResult
    {
        $subjectId = $this->subjectIdOf($target);
        $this->assertAccess($actor, $subjectId, $target);

        $wanted = collect($studentIds)->map(fn ($id) => (int) $id)->filter()->unique();
        $pool = $actor->studentIdsFor($subjectId);

        $current = StudentContentExclusion::query()
            ->where('excludable_type', $target->getMorphClass())
            ->where('excludable_id', $target->getKey())
            ->pluck('student_id')->map(fn ($id) => (int) $id);

        $inReach = fn ($id) => $pool === null || in_array($id, $pool, true);

        $toAdd = $wanted->filter($inReach)->diff($current)->values()->all();
        $toRemove = $current->filter($inReach)->diff($wanted)->values()->all();

        $result = new LibraryResult('لا توجد تغييرات في الاستثناءات.');
        if ($toAdd) {
            $result = $this->setMany($actor, $target, $toAdd, true);
        }
        if ($toRemove) {
            $result = $this->setMany($actor, $target, $toRemove, false);
        }

        return $result;
    }

    // ------------------------------------------------------------------- helpers

    /** @return array<string, mixed> */
    private function snapshot(Model $target): array
    {
        return $target instanceof SubjectResource
            ? $this->journal->resource($target)
            : $this->journal->container($target);
    }

    /**
     * Groups whose students can currently reach the target. null = every group.
     *
     * @return array<int>|null
     */
    private function audienceGroupIds(Model $target, int $subjectId): ?array
    {
        if ($target instanceof SubjectResource) {
            return $this->resourceAudience($target);
        }

        $resources = SubjectResource::query()->with('groupLinks')
            ->whereHas('placements', function ($p) use ($target) {
                $target instanceof EducationalLesson
                    ? $p->where('educational_lesson_id', $target->id)
                    : $p->where('educational_unit_id', $target->id)->orWhereIn('educational_lesson_id', $target->lessons()->pluck('id'));
            })->get();

        $groups = [];
        foreach ($resources as $resource) {
            $audience = $this->resourceAudience($resource);
            if ($audience === null) {
                return null;
            }
            $groups = [...$groups, ...$audience];
        }

        if ($target->is_shared) {
            return null;
        }

        return array_values(array_unique([...$groups, ...$target->groups()->pluck('groups.id')->map(fn ($id) => (int) $id)->all()]));
    }

    /** @return array<int>|null null = shared with everyone */
    private function resourceAudience(SubjectResource $resource): ?array
    {
        if ($resource->is_shared) {
            return null;
        }

        return $resource->groupLinks->whereNull('deleted_at')->pluck('group_id')->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /**
     * Students for whom EVERY placement of the resource is blocked by an exclusion on a lesson/unit.
     *
     * @return Collection<int, string>  student id => 'الدرس' | 'الوحدة'
     */
    private function inheritedExclusions(SubjectResource $resource): Collection
    {
        $placements = $resource->placements()->with('lesson.unit', 'unit')->get();
        if ($placements->isEmpty() || $placements->contains(fn ($p) => $p->isGeneral())) {
            return collect();
        }

        $perPlacement = $placements->map(function ($placement) {
            $blocked = collect();
            $lesson = $placement->lesson;
            $unit = $lesson?->unit ?? $placement->unit;

            if ($lesson) {
                foreach ($lesson->contentExclusions()->pluck('student_id') as $id) {
                    $blocked[(int) $id] = 'الدرس';
                }
            }
            if ($unit) {
                foreach ($unit->contentExclusions()->pluck('student_id') as $id) {
                    $blocked[(int) $id] = $blocked[(int) $id] ?? 'الوحدة';
                }
            }

            return $blocked;
        });

        return $perPlacement->first()->filter(fn ($label, $studentId) => $perPlacement->every(fn ($blocked) => $blocked->has($studentId)));
    }

    /**
     * @param  Collection<int, StudentContentExclusion>  $rows
     * @return array<string, string>  "type:id" => name
     */
    private function excluderNames(Collection $rows): array
    {
        $names = [];

        $adminIds = $rows->where('excluded_by_type', LibraryActor::ADMIN)->pluck('excluded_by_id')->filter()->unique();
        if ($adminIds->isNotEmpty()) {
            foreach (Admin::whereIn('id', $adminIds)->get(['id', 'name']) as $admin) {
                $names[LibraryActor::ADMIN.':'.$admin->id] = $admin->name;
            }
        }

        $teacherIds = $rows->where('excluded_by_type', LibraryActor::TEACHER)->pluck('excluded_by_id')->filter()->unique();
        if ($teacherIds->isNotEmpty()) {
            foreach (Teacher::whereIn('id', $teacherIds)->get(['id', 'name']) as $teacher) {
                $names[LibraryActor::TEACHER.':'.$teacher->id] = $teacher->name;
            }
        }

        return $names;
    }

    /**
     * @param  array<int>  $studentIds
     */
    private function assertStudents(LibraryActor $actor, int $subjectId, array $studentIds, bool $mustBeEnrolled): void
    {
        $pool = $actor->studentIdsFor($subjectId);

        if ($pool !== null && array_diff($studentIds, $pool) !== []) {
            throw LibraryException::forbidden('يمكنك التحكم في طلاب مجموعاتك فقط.');
        }

        if ($mustBeEnrolled) {
            $enrolled = Registration::where('subject_id', $subjectId)
                ->whereIn('student_id', $studentIds)
                ->whereIn('status', self::ACTIVE_STATUSES)
                ->pluck('student_id')->unique()->count();

            if ($enrolled !== count($studentIds)) {
                throw LibraryException::invalid('بعض الطلاب غير مسجلين في هذه المادة.', 'not_enrolled');
            }
        }
    }

    private function assertAccess(LibraryActor $actor, int $subjectId, Model $target): void
    {
        if (! $actor->canAccessSubject($subjectId)) {
            throw LibraryException::forbidden();
        }

        if ($target instanceof SubjectResource && ! $actor->canSeeResource($target)) {
            throw LibraryException::forbidden();
        }
    }

    private function subjectIdOf(Model $target): int
    {
        $subjectId = match (true) {
            $target instanceof SubjectResource => $target->subject_id,
            $target instanceof EducationalUnit => $target->stage?->subject_id,
            $target instanceof EducationalLesson => $target->unit?->stage?->subject_id,
            default => null,
        };

        if (! $subjectId) {
            throw LibraryException::notFound();
        }

        return (int) $subjectId;
    }

    private function label(Model $target): string
    {
        return match (true) {
            $target instanceof SubjectResource => "«{$target->title}»",
            $target instanceof EducationalUnit => "الوحدة «{$target->name_ar}»",
            default => "الدرس «{$target->name_ar}»",
        };
    }
}
