<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Registration;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Single source of truth for "which students does this exam belong to":
 * everyone registered (with an access status) in ANY of the exam's target
 * groups, once each, minus the students the teacher explicitly excluded.
 *
 * Used by the live notifications and the teacher grading / results lists so
 * they can never disagree about who the exam is for.
 */
class ExamAudience
{
    /**
     * @param  array<int>|null  $onlyGroupIds  restrict to these groups (e.g. groups just added to a published exam)
     */
    public function query(Exam $exam, ?array $onlyGroupIds = null): Builder
    {
        // Guest-only exams are not shown to registered students at all.
        if ($exam->audience !== null && ! $exam->allowsStudents()) {
            return Student::query()->whereRaw('1 = 0');
        }

        $groupIds = $onlyGroupIds ?? $exam->allGroupIds();

        $query = Student::query()->whereIn(
            'students.id',
            Registration::query()
                ->select('student_id')
                ->whereIn('group_id', $groupIds)
                ->whereIn('status', Exam::ACCESS_REGISTRATION_STATUSES)
        );

        if ($excluded = $exam->excludedStudentIds()) {
            $query->whereNotIn('students.id', $excluded);
        }

        return $query;
    }

    /**
     * Rows for the "exclude students" picker of the exam form: every student
     * registered in the chosen groups, once each, with the group(s) they are in.
     *
     * @param  array<int>  $groupIds
     * @return Collection<int, array{id:int, full_name_ar:?string, full_name_en:?string, group_ids:array<int>, groups:string}>
     */
    public function pickerRows(array $groupIds): Collection
    {
        if ($groupIds === []) {
            return collect();
        }

        return Registration::query()
            ->with(['student:id,full_name_ar,full_name_en', 'group:id,name'])
            ->whereIn('group_id', $groupIds)
            ->whereIn('status', Exam::ACCESS_REGISTRATION_STATUSES)
            ->get()
            ->filter(fn ($registration) => $registration->student)
            ->groupBy('student_id')
            ->map(fn ($rows) => [
                'id' => (int) $rows->first()->student_id,
                'full_name_ar' => $rows->first()->student->full_name_ar,
                'full_name_en' => $rows->first()->student->full_name_en,
                'group_ids' => $rows->pluck('group_id')->map(fn ($id) => (int) $id)->unique()->values()->all(),
                'groups' => $rows->pluck('group.name')->filter()->unique()->implode('، '),
            ])
            ->sortBy(fn ($row) => $row['full_name_ar'] ?: $row['full_name_en'])
            ->values();
    }

    /** @return Collection<int, Student> */
    public function students(Exam $exam, ?array $onlyGroupIds = null): Collection
    {
        return $this->query($exam, $onlyGroupIds)->get();
    }
}
