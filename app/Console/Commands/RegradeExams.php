<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Models\Student;
use App\Services\ExamRegrader;
use Illuminate\Console\Command;

class RegradeExams extends Command
{
    protected $signature = 'grades:regrade
        {exam? : exam id to regrade (omit and pass --all for every exam)}
        {--all : regrade every exam that has submissions}
        {--apply : actually save the new marks (without it the command only shows what would change)}';

    protected $description = 'Re-mark submitted exams against the current answer key (never deletes grades or answers)';

    public function handle(ExamRegrader $regrader): int
    {
        if (! $this->argument('exam') && ! $this->option('all')) {
            $this->error('Pass an exam id, or --all.');

            return self::FAILURE;
        }

        $dryRun = ! $this->option('apply');
        $exams = $this->argument('exam')
            ? Exam::whereKey((int) $this->argument('exam'))->get()
            : Exam::whereHas('grades')->orderBy('id')->get();

        if ($exams->isEmpty()) {
            $this->error('No matching exam.');

            return self::FAILURE;
        }

        $rows = [];
        $totals = ['checked' => 0, 'changed' => 0, 'skipped' => 0];

        foreach ($exams as $exam) {
            $report = $regrader->regradeExam($exam, $dryRun);
            foreach (['checked', 'changed', 'skipped'] as $k) {
                $totals[$k] += $report[$k];
            }
            $names = Student::whereIn('id', array_column($report['details'], 'student_id'))->pluck('full_name_ar', 'id');
            foreach ($report['details'] as $d) {
                $rows[] = [$exam->id, $d['grade_id'], $names[$d['student_id']] ?? $d['student_id'], $d['old'], $d['new'], $d['max'], $d['recovered']];
            }
        }

        if ($rows) {
            $this->table(['exam', 'grade', 'student', 'old score', 'new score', 'max', 'answers recovered'], $rows);
        }

        $this->info(($dryRun ? '[preview] would change' : 'Changed')." {$totals['changed']} of {$totals['checked']} grade(s); {$totals['skipped']} have no recorded answers and were left untouched.");
        if ($dryRun && $totals['changed'] > 0) {
            $this->line('Run again with --apply to save these marks.');
        }

        return self::SUCCESS;
    }
}
