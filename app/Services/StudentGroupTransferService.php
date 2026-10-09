<?php

namespace App\Services;

use App\Models\Grade;
use App\Models\Group;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class StudentGroupTransferService
{
    public function __construct(private StudentContentGrantService $grantService)
    {
    }

    /**
     * Move a student registration to another group of the same subject.
     *
     * Payments stay on the same registration, so they follow the new group
     * automatically. Grades and exam submissions for the previous group are
     * reassigned to the destination group. Published exams/content from the
     * previous group remain visible to the student via personal grants.
     */
    public function transfer(Registration $registration, Group $destination): Registration
    {
        if ((int) $destination->subject_id !== (int) $registration->subject_id) {
            throw new InvalidArgumentException('المجموعة المختارة لا تتبع نفس المادة.');
        }

        if ($registration->group_id && (int) $registration->group_id === (int) $destination->id) {
            throw new InvalidArgumentException('الطالب مسجل بالفعل في هذه المجموعة.');
        }

        $oldGroupId = $registration->group_id ? (int) $registration->group_id : null;
        $studentId = (int) $registration->student_id;
        $subjectId = (int) $registration->subject_id;

        return DB::transaction(function () use ($registration, $destination, $oldGroupId, $studentId, $subjectId) {
            if ($oldGroupId) {
                $this->grantService->grantPreviousGroupContentOnTransfer(
                    $studentId,
                    $subjectId,
                    $oldGroupId,
                    (int) $destination->id
                );

                Grade::query()
                    ->where('student_id', $studentId)
                    ->where('group_id', $oldGroupId)
                    ->update(['group_id' => $destination->id]);

                Group::query()
                    ->where('id', $oldGroupId)
                    ->where('current_count', '>', 0)
                    ->decrement('current_count');
            }

            $registration->update(['group_id' => $destination->id]);
            $destination->increment('current_count');

            return $registration->refresh();
        });
    }
}
