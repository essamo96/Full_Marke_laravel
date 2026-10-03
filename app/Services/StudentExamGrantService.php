<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Student;
use App\Models\StudentExamGrant;
use Illuminate\Support\Facades\DB;

class StudentExamGrantService
{
    /**
     * Keep old-group exams visible to a student after transferring to a new group.
     * Does not re-publish exams to the whole new group — personal grants only.
     */
    public function grantPreviousGroupExamsOnTransfer(
        Student|int $student,
        int $subjectId,
        ?int $fromGroupId,
        int $toGroupId,
        string $source = StudentExamGrant::SOURCE_TRANSFER
    ): int {
        $studentId = $student instanceof Student ? $student->id : (int) $student;

        if (! $fromGroupId || $fromGroupId === $toGroupId) {
            return 0;
        }

        return DB::transaction(function () use ($studentId, $subjectId, $fromGroupId, $source) {
            $exams = Exam::query()
                ->where('subject_id', $subjectId)
                ->where('group_id', $fromGroupId)
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('audience')
                        ->orWhereIn('audience', ['students', 'both']);
                })
                ->get(['id', 'excluded_student_ids']);

            $now = now();
            $rows = [];

            foreach ($exams as $exam) {
                $excluded = $exam->excluded_student_ids ?? [];
                if (in_array($studentId, $excluded, true) || in_array((string) $studentId, array_map('strval', $excluded), true)) {
                    continue;
                }

                $rows[] = [
                    'student_id' => $studentId,
                    'exam_id' => $exam->id,
                    'subject_id' => $subjectId,
                    'from_group_id' => $fromGroupId,
                    'source' => $source,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($rows === []) {
                return 0;
            }

            StudentExamGrant::query()->upsert(
                $rows,
                ['student_id', 'exam_id'],
                ['updated_at', 'from_group_id', 'source']
            );

            return count($rows);
        });
    }

    public function studentCanAccessExam(Exam $exam, int $studentId, iterable $currentGroupIds): bool
    {
        if ($exam->status !== 'published') {
            return false;
        }

        if (! $exam->allowsStudents() && $exam->audience !== null) {
            return false;
        }

        $excluded = $exam->excluded_student_ids ?? [];
        if (in_array($studentId, $excluded, true) || in_array((string) $studentId, array_map('strval', $excluded), true)) {
            return false;
        }

        $groupIds = collect($currentGroupIds)->filter()->map(fn ($id) => (int) $id)->all();
        if (in_array((int) $exam->group_id, $groupIds, true)) {
            return true;
        }

        return StudentExamGrant::query()
            ->where('student_id', $studentId)
            ->where('exam_id', $exam->id)
            ->exists();
    }

    /**
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function grantedExamIdsForStudent(int $studentId): \Illuminate\Support\Collection
    {
        return StudentExamGrant::query()
            ->where('student_id', $studentId)
            ->pluck('exam_id')
            ->map(fn ($id) => (int) $id);
    }
}
