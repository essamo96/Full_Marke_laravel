<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Notifications\NewExamPublishedNotification;
use App\Services\ExamAudience;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tells every student of an exam's target group(s) that it was published,
 * excluding the students the teacher left out.
 */
class NotifyStudentsOfNewExam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exam;

    /**
     * @param  array<int>|null  $onlyGroupIds  notify just these groups (groups added to an already published exam)
     */
    public function __construct(Exam $exam, public ?array $onlyGroupIds = null)
    {
        $this->exam = $exam;
    }

    public function handle(ExamAudience $audience): void
    {
        $sent = 0;

        $audience->query($this->exam, $this->onlyGroupIds)
            ->chunkById(200, function ($students) use (&$sent) {
                foreach ($students as $student) {
                    // One student's failed push (offline Reverb, bad mail row...) must never
                    // stop the rest of the groups from being notified.
                    try {
                        $student->notify(new NewExamPublishedNotification($this->exam, $student->id));
                        $sent++;
                    } catch (Throwable $e) {
                        Log::warning('New-exam notification failed', [
                            'exam_id' => $this->exam->id,
                            'student_id' => $student->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }, 'students.id', 'id');

        Log::info('New-exam notifications sent', ['exam_id' => $this->exam->id, 'students' => $sent]);
    }
}
