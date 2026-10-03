<?php

namespace App\Services;

use App\Models\EducationalLesson;
use App\Models\EducationalStage;
use App\Models\EducationalUnit;
use App\Models\Student;
use App\Models\StudentContentGrant;
use App\Models\SubjectResource;
use Illuminate\Support\Facades\DB;

class StudentContentGrantService
{
    /**
     * Snapshot content visible in the old group but not in the new one,
     * and grant it personally so the student does not lose access after transfer.
     */
    public function grantPreviousGroupContentOnTransfer(
        Student|int $student,
        int $subjectId,
        ?int $fromGroupId,
        int $toGroupId,
        string $source = StudentContentGrant::SOURCE_TRANSFER
    ): int {
        $studentId = $student instanceof Student ? $student->id : (int) $student;

        if (! $fromGroupId || $fromGroupId === $toGroupId) {
            return 0;
        }

        $contentGranted = DB::transaction(function () use ($studentId, $subjectId, $fromGroupId, $toGroupId, $source) {
            $stageIds = EducationalStage::where('subject_id', $subjectId)->pluck('id');

            $oldUnitIds = EducationalUnit::query()
                ->whereIn('educational_stage_id', $stageIds)
                ->forGroup($fromGroupId)
                ->pluck('id');

            $newUnitIds = EducationalUnit::query()
                ->whereIn('educational_stage_id', $stageIds)
                ->forGroup($toGroupId)
                ->pluck('id');

            $unitsToGrant = $oldUnitIds->diff($newUnitIds)->values();

            $unitIdsInSubject = EducationalUnit::query()
                ->whereIn('educational_stage_id', $stageIds)
                ->pluck('id');

            $oldLessonIds = EducationalLesson::query()
                ->whereIn('educational_unit_id', $unitIdsInSubject)
                ->forGroup($fromGroupId)
                ->pluck('id');

            $newLessonIds = EducationalLesson::query()
                ->whereIn('educational_unit_id', $unitIdsInSubject)
                ->forGroup($toGroupId)
                ->pluck('id');

            $lessonsToGrant = $oldLessonIds->diff($newLessonIds)->values();

            $oldResourceIds = SubjectResource::query()
                ->where('subject_id', $subjectId)
                ->where('is_active', true)
                ->forGroup($fromGroupId)
                ->pluck('id');

            $newResourceIds = SubjectResource::query()
                ->where('subject_id', $subjectId)
                ->where('is_active', true)
                ->forGroup($toGroupId)
                ->pluck('id');

            $resourcesToGrant = $oldResourceIds->diff($newResourceIds)->values();

            // Ensure ancestors exist in the tree when a resource/lesson is granted.
            if ($resourcesToGrant->isNotEmpty()) {
                $resources = SubjectResource::with('lesson')
                    ->whereIn('id', $resourcesToGrant)
                    ->get();

                foreach ($resources as $resource) {
                    if (! $resource->lesson) {
                        continue;
                    }

                    if (! $newLessonIds->contains($resource->educational_lesson_id)
                        && ! $lessonsToGrant->contains($resource->educational_lesson_id)
                    ) {
                        $lessonsToGrant->push($resource->educational_lesson_id);
                    }

                    $unitId = $resource->lesson->educational_unit_id;
                    if ($unitId
                        && ! $newUnitIds->contains($unitId)
                        && ! $unitsToGrant->contains($unitId)
                    ) {
                        $unitsToGrant->push($unitId);
                    }
                }
            }

            if ($lessonsToGrant->isNotEmpty()) {
                $lessons = EducationalLesson::whereIn('id', $lessonsToGrant)->get(['id', 'educational_unit_id']);
                foreach ($lessons as $lesson) {
                    if (! $newUnitIds->contains($lesson->educational_unit_id)
                        && ! $unitsToGrant->contains($lesson->educational_unit_id)
                    ) {
                        $unitsToGrant->push($lesson->educational_unit_id);
                    }
                }
            }

            $granted = 0;
            $granted += $this->upsertGrants($studentId, $subjectId, EducationalUnit::class, $unitsToGrant->unique()->all(), $source, $fromGroupId);
            $granted += $this->upsertGrants($studentId, $subjectId, EducationalLesson::class, $lessonsToGrant->unique()->all(), $source, $fromGroupId);
            $granted += $this->upsertGrants($studentId, $subjectId, SubjectResource::class, $resourcesToGrant->unique()->all(), $source, $fromGroupId);

            return $granted;
        });

        // Also retain published exams from the previous group for this student.
        $examSource = $source === StudentContentGrant::SOURCE_MANUAL
            ? \App\Models\StudentExamGrant::SOURCE_MANUAL
            : \App\Models\StudentExamGrant::SOURCE_TRANSFER;

        $examsGranted = app(StudentExamGrantService::class)->grantPreviousGroupExamsOnTransfer(
            $studentId,
            $subjectId,
            $fromGroupId,
            $toGroupId,
            $examSource
        );

        return $contentGranted + $examsGranted;
    }

    /**
     * @param  array<int>  $ids
     */
    protected function upsertGrants(
        int $studentId,
        int $subjectId,
        string $type,
        array $ids,
        string $source,
        ?int $fromGroupId
    ): int {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if ($ids === []) {
            return 0;
        }

        $now = now();
        $rows = [];
        foreach ($ids as $id) {
            $rows[] = [
                'student_id' => $studentId,
                'subject_id' => $subjectId,
                'grantable_type' => $type,
                'grantable_id' => $id,
                'source' => $source,
                'from_group_id' => $fromGroupId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        StudentContentGrant::query()->upsert(
            $rows,
            ['student_id', 'grantable_type', 'grantable_id'],
            ['updated_at', 'from_group_id', 'source']
        );

        return count($rows);
    }

    public function studentCanAccessResource(SubjectResource $resource, int $studentId, ?int $groupId): bool
    {
        return $resource->isVisibleToStudent($studentId, $groupId);
    }
}
