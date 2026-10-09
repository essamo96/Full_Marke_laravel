<?php

namespace App\Services;

use App\Jobs\NotifyStudentsOfNewExam;
use App\Models\Exam;

/**
 * Decides who has to hear about an exam after it is created / edited:
 *  - a newly published exam -> every student of all its target groups;
 *  - groups added to an already published exam -> only the students of the
 *    new groups, so students of the old groups are not alerted a second time.
 */
class ExamNotifier
{
    /**
     * @param  string|null  $oldStatus  status before the save (null when the exam was just created)
     * @param  array<int>  $oldGroupIds  target groups before the save
     * @param  bool  $sync  run immediately instead of on the queue
     */
    public function afterSave(Exam $exam, ?string $oldStatus, array $oldGroupIds = [], bool $sync = false): void
    {
        if ($exam->status !== 'published') {
            return;
        }

        $exam->unsetRelation('groups');

        if ($oldStatus !== 'published') {
            $job = new NotifyStudentsOfNewExam($exam);
        } else {
            $added = array_values(array_diff($exam->allGroupIds(), $oldGroupIds));
            if ($added === []) {
                return;
            }
            $job = new NotifyStudentsOfNewExam($exam, $added);
        }

        $sync ? dispatch_sync($job) : dispatch($job);
    }
}
