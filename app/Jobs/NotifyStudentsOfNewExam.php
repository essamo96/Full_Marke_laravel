<?php

namespace App\Jobs;

use App\Models\Exam;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyStudentsOfNewExam implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $exam;

    /**
     * Create a new job instance.
     */
    public function __construct(Exam $exam)
    {
        $this->exam = $exam;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the group and its registered students
        $group = $this->exam->group;
        $excludedIds = $this->exam->excluded_student_ids ?? [];

        // Fetch registered active students
        $students = $group->registrations()
            ->whereIn('status', ['partially_paid', 'fully_paid'])
            ->with('student')
            ->get()
            ->pluck('student')
            ->filter(function ($student) use ($excludedIds) {
                return !in_array($student->id, $excludedIds);
            });

        foreach ($students as $student) {
            $student->notify(new \App\Notifications\NewExamPublishedNotification($this->exam, $student->id));
        }
    }
}
