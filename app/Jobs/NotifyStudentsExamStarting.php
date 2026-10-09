<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Notifications\ExamStartingNowNotification;
use App\Services\ExamAudience;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * "Your exam starts now" alert, sent to every student of every target group
 * of the exam (minus the excluded ones) when its scheduled start arrives.
 */
class NotifyStudentsExamStarting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function handle(ExamAudience $audience): void
    {
        $sent = 0;

        $audience->query($this->exam)
            ->chunkById(200, function ($students) use (&$sent) {
                foreach ($students as $student) {
                    try {
                        $student->notify(new ExamStartingNowNotification($this->exam, $student->id));
                        $sent++;
                    } catch (Throwable $e) {
                        Log::warning('Exam-starting notification failed', [
                            'exam_id' => $this->exam->id,
                            'student_id' => $student->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }, 'students.id', 'id');

        Log::info('Exam-starting notifications sent', ['exam_id' => $this->exam->id, 'students' => $sent]);
    }
}
