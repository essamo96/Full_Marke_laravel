<?php

namespace App\Services;

use App\Models\Student;
use App\Models\StudentContentGrant;
use App\Models\SubjectResource;
use Illuminate\Support\Facades\DB;

class StudentContentGrantService
{
    /**
     * Keep what a student could see in their previous group after moving to another one.
     *
     * Works on the same rules the student sees (SubjectResource::visibleToStudent): every resource
     * visible in the old group but NOT in the new one is granted to the student personally. Units
     * and lessons need no grants of their own - they are shown whenever one of their resources is.
     *
     * Exclusions still win: a resource the student was excluded from is never granted, and a
     * grant never overrides an exclusion afterwards.
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
            $visibleIn = fn (int $groupId) => SubjectResource::query()
                ->where('subject_id', $subjectId)
                ->visibleToStudent($studentId, [$groupId])
                ->pluck('subject_resources.id')
                ->map(fn ($id) => (int) $id);

            $toGrant = $visibleIn($fromGroupId)->diff($visibleIn($toGroupId))->values();

            return $this->upsertGrants($studentId, $subjectId, SubjectResource::class, $toGrant->all(), $source, $fromGroupId);
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
        return $resource->isVisibleToStudent($studentId, $groupId ? [$groupId] : []);
    }
}
