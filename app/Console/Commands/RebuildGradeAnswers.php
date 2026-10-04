<?php

namespace App\Console\Commands;

use App\Models\Grade;
use App\Services\GradeAnswerRebuilder;
use Illuminate\Console\Command;

class RebuildGradeAnswers extends Command
{
    protected $signature = 'grades:rebuild-answers {--dry-run : only report what would be rebuilt}';

    protected $description = 'Restore answer details for full-mark grades that have none';

    public function handle(): int
    {
        $done = $skipped = 0;

        Grade::whereDoesntHave('answers')->whereNotNull('exam_id')->with('exam')->each(function (Grade $g) use (&$done, &$skipped) {
            if (! GradeAnswerRebuilder::canRebuild($g)) {
                $skipped++;

                return;
            }
            if (! $this->option('dry-run')) {
                GradeAnswerRebuilder::ensure($g);
            }
            $done++;
        });

        $this->info(($this->option('dry-run') ? 'Would rebuild' : 'Rebuilt')." {$done} grade(s); {$skipped} cannot be inferred (partial score / essay / no questions).");

        return self::SUCCESS;
    }
}
