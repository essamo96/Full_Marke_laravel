<?php

namespace App\Jobs;

use App\Models\Exam;
use App\Notifications\ExamStartingNowNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyStudentsExamStarting implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exam;

    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    public function handle(): void
    {
        $group = $this->exam->group;
        $excludedIds = $this->exam->excluded_student_ids ?? [];

        $students = $group->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid'])
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter(fn ($student) => $student && ! in_array($student->id, $excludedIds));

        foreach ($students as $student) {
            $student->notify(new ExamStartingNowNotification($this->exam, $student->id));
        }
    }
}
