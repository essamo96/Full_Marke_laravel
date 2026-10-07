<?php

namespace App\Services;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Registration;
use App\Models\ResourcePlacement;
use App\Models\StudentContentExclusion;
use App\Models\Subject;
use App\Models\SubjectResource;
use Illuminate\Support\Collection;

/**
 * The ONE door between students and the resource library.
 *
 * Every student-facing surface asks this class - the resources page, the group page, the
 * registration page and every direct URL (stream, file, link, download, embed) - so what a
 * student can list and what a student can open can never drift apart again. The rules are
 * the SQL in App\Support\Library\StudentVisibility; this class adds the "which registration /
 * which groups" lookup and builds the lesson tree from the result.
 */
class StudentContentGate
{
    /** Registration statuses that give access to a subject's resources. */
    public const ACTIVE_STATUSES = ['pending', 'partially_paid', 'fully_paid'];

    /**
     * The groups the student belongs to in a subject, through their active registrations.
     *
     * @return array<int>
     */
    public function groupIds(int $studentId, int $subjectId): array
    {
        return Registration::query()
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->whereNotNull('group_id')
            ->pluck('group_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    public function isEnrolled(int $studentId, int $subjectId): bool
    {
        return Registration::query()
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->exists();
    }

    /**
     * Query of the resources a student may see in a subject (the same predicate as canAccess()).
     *
     * @return \Illuminate\Database\Eloquent\Builder<SubjectResource>
     */
    public function visibleResources(int $studentId, int $subjectId)
    {
        return SubjectResource::query()
            ->where('subject_id', $subjectId)
            ->visibleToStudent($studentId, $this->groupIds($studentId, $subjectId));
    }

    /** May the student open this resource right now? */
    public function canAccess(SubjectResource $resource, int $studentId): bool
    {
        if ($resource->trashed() || ! $this->isEnrolled($studentId, (int) $resource->subject_id)) {
            return false;
        }

        return SubjectResource::query()
            ->whereKey($resource->getKey())
            ->visibleToStudent($studentId, $this->groupIds($studentId, (int) $resource->subject_id))
            ->exists();
    }

    /**
     * Abort the request unless the student may open the resource.
     * 404 when the resource is switched off or deleted, 403 for everything else.
     */
    public function authorize(SubjectResource $resource, int $studentId): void
    {
        abort_if($resource->trashed() || ! $resource->is_active, 404);
        abort_unless($this->isEnrolled($studentId, (int) $resource->subject_id), 403);
        abort_unless($this->canAccess($resource, $studentId), 403);
    }

    /**
     * What the student sees of one subject, as a tree.
     *
     * Returns:
     *   units   Collection<EducationalUnit>   each with ->lessons (only lessons that show something,
     *           each with ->resources) and ->directResources (resources placed straight under the unit)
     *   general Collection<SubjectResource>   resources outside any unit
     *
     * @param  array<int>|null  $groupIds  null = look them up from the student's registrations
     * @return array{units: Collection<int, EducationalUnit>, general: Collection<int, SubjectResource>}
     */
    public function tree(int $studentId, Subject $subject, ?array $groupIds = null): array
    {
        $groupIds ??= $this->groupIds($studentId, $subject->id);

        $visible = SubjectResource::query()
            ->where('subject_id', $subject->id)
            ->visibleToStudent($studentId, $groupIds)
            ->with(['placements' => fn ($placements) => $placements->where('is_active', true)])
            ->get();

        if ($visible->isEmpty()) {
            return ['units' => collect(), 'general' => collect()];
        }

        $stageIds = EducationalStage::where('subject_id', $subject->id)->where('is_active', true)->pluck('id');
        $units = EducationalUnit::whereIn('educational_stage_id', $stageIds)->where('is_active', true)->get()->keyBy('id');
        $lessons = EducationalLesson::whereIn('educational_unit_id', $units->keys())->where('is_active', true)->get()->keyBy('id');

        $excludedUnits = $this->excludedIds($studentId, EducationalUnit::class, $units->keys());
        $excludedLessons = $this->excludedIds($studentId, EducationalLesson::class, $lessons->keys());

        $unitOpen = fn (?int $unitId) => $unitId && $units->has($unitId) && ! isset($excludedUnits[$unitId]);
        $lessonOpen = fn (?int $lessonId) => $lessonId
            && $lessons->has($lessonId)
            && ! isset($excludedLessons[$lessonId])
            && $unitOpen((int) $lessons[$lessonId]->educational_unit_id);

        $general = collect();
        $inLesson = [];
        $inUnit = [];

        foreach ($visible as $resource) {
            foreach ($resource->placements as $placement) {
                $entry = [$placement->sort_order, $resource];

                if ($placement->isGeneral()) {
                    $general->push($entry);
                } elseif ($placement->educational_lesson_id) {
                    if ($lessonOpen((int) $placement->educational_lesson_id)) {
                        $inLesson[$placement->educational_lesson_id][] = $entry;
                    }
                } elseif ($unitOpen((int) $placement->educational_unit_id)) {
                    $inUnit[$placement->educational_unit_id][] = $entry;
                }
            }
        }

        $sorted = fn (array|Collection $entries) => collect($entries)->sortBy(fn ($e) => $e[0])->map(fn ($e) => $e[1])->values();

        $tree = $units->sortBy('sort_order')->map(function (EducationalUnit $unit) use ($lessons, $inLesson, $inUnit, $sorted) {
            $unitLessons = $lessons->where('educational_unit_id', $unit->id)->sortBy('sort_order')
                ->filter(fn ($lesson) => ! empty($inLesson[$lesson->id]))
                ->map(function (EducationalLesson $lesson) use ($inLesson, $sorted) {
                    $lesson = clone $lesson;
                    $lesson->setRelation('resources', $sorted($inLesson[$lesson->id]));

                    return $lesson;
                })->values();

            $unit = clone $unit;
            $unit->setRelation('lessons', $unitLessons);
            $unit->setRelation('directResources', $sorted($inUnit[$unit->id] ?? []));

            return $unit;
        })->filter(fn ($unit) => $unit->lessons->isNotEmpty() || $unit->directResources->isNotEmpty())->values();

        return ['units' => $tree, 'general' => $sorted($general)];
    }

    /**
     * @param  Collection<int, int>  $ids
     * @return array<int, true>
     */
    private function excludedIds(int $studentId, string $class, Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return StudentContentExclusion::query()
            ->where('student_id', $studentId)
            ->where('excludable_type', (new $class)->getMorphClass())
            ->whereIn('excludable_id', $ids)
            ->pluck('excludable_id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }
}
