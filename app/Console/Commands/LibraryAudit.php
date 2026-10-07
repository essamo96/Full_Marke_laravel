<?php

namespace App\Console\Commands;

use App\Models\Admin;
use App\Models\Subject;
use App\Services\ResourceLibrary\LibraryActor;
use App\Services\ResourceLibrary\LibraryException;
use App\Services\ResourceLibrary\LibraryHealth;
use Illuminate\Console\Command;

/**
 * "Extract the errors" from the command line: the same scan the quality-check panel runs.
 *
 *   php artisan library:audit                    report for every subject
 *   php artisan library:audit --subject=9        one subject
 *   php artisan library:audit --subject=9 --fix=place_general
 *                                                apply one automatic fix (journaled, so it can be undone)
 */
class LibraryAudit extends Command
{
    protected $signature = 'library:audit
        {--subject= : only this subject id}
        {--fix= : apply one automatic fix (place_general, deactivate_missing, share_with_my_groups, activate_containers, share_container_with_group, clean_links, clean_exclusions, merge_duplicates)}';

    protected $description = 'Scan the resource library for content that is invisible, duplicated, broken or mis-shared';

    public function handle(LibraryHealth $health): int
    {
        // the command acts as a system admin: it reaches every subject and group
        $system = new Admin;
        $system->id = 0;
        $system->name = 'CLI';
        $actor = LibraryActor::admin($system);

        $subjects = Subject::query()
            ->when($this->option('subject'), fn ($q, $id) => $q->whereKey($id))
            ->orderBy('id')
            ->get();

        if ($subjects->isEmpty()) {
            $this->warn('No subject found.');

            return self::FAILURE;
        }

        $totals = ['error' => 0, 'warning' => 0, 'info' => 0];
        $orphans = null;

        foreach ($subjects as $subject) {
            if ($fix = $this->option('fix')) {
                try {
                    $result = $health->fix($actor, $subject, $fix);
                    $this->line("[{$subject->id}] {$subject->name_ar}: {$result->message}");
                } catch (LibraryException $e) {
                    $this->error("[{$subject->id}] {$subject->name_ar}: {$e->getMessage()}");
                }

                continue;
            }

            // orphan uploads belong to no subject: reported once, at the end
            $issues = collect($health->scan($actor, $subject)['issues']);
            $orphans ??= $issues->firstWhere('code', 'orphan_uploads');
            $issues = $issues->reject(fn ($issue) => $issue['code'] === 'orphan_uploads');

            if ($issues->isEmpty()) {
                continue;
            }

            $this->newLine();
            $this->info("[{$subject->id}] {$subject->name_ar}");

            foreach ($issues as $issue) {
                $this->printIssue($issue, "php artisan library:audit --subject={$subject->id} --fix={$issue['fix']}");
                $totals[$issue['severity']] += $issue['count'];
            }
        }

        if (! $this->option('fix')) {
            if ($orphans) {
                $this->newLine();
                $this->info('[incoming/] uploads not attached to any resource');
                $this->printIssue($orphans, null);
                $this->line('           link / adopt / delete them from the quality-check panel, or: php artisan library:cleanup-incoming --dry-run');
                $totals[$orphans['severity']] += $orphans['count'];
            }

            $this->newLine();
            $this->info("Total: {$totals['error']} errors, {$totals['warning']} warnings, {$totals['info']} notes.");
        }

        return self::SUCCESS;
    }

    /** @param  array<string, mixed>  $issue */
    private function printIssue(array $issue, ?string $fixCommand): void
    {
        $this->line(sprintf('  %-8s %s (%d)', strtoupper($issue['severity']), $issue['title'], $issue['count']));
        foreach (array_slice($issue['items'], 0, 5) as $item) {
            $this->line('           - '.$item['label'].(! empty($item['detail']) ? ' — '.$item['detail'] : ''));
        }
        if ($issue['count'] > 5) {
            $this->line('           … and '.($issue['count'] - 5).' more');
        }
        if ($issue['fix'] && $fixCommand) {
            $this->line("           fix: {$fixCommand}");
        }
    }
}
