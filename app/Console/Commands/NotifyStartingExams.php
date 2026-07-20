<?php

namespace App\Console\Commands;

use App\Jobs\NotifyStudentsExamStarting;
use App\Models\Exam;
use Illuminate\Console\Command;

class NotifyStartingExams extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exams:notify-starting';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify students in real time when a published exam\'s scheduled start time arrives';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $exams = Exam::where('status', 'published')
            ->whereNotNull('start_time')
            ->whereNull('start_alert_sent_at')
            ->whereBetween('start_time', [now()->subMinutes(10), now()])
            ->get();

        foreach ($exams as $exam) {
            NotifyStudentsExamStarting::dispatch($exam);
            $exam->update(['start_alert_sent_at' => now()]);
        }

        $this->info("Notified students for {$exams->count()} starting exam(s).");
    }
}
