<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CleanupUnverifiedAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:cleanup-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove unverified student accounts that are older than 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subHours(48);

        $deletedCount = Student::whereNull('email_verified_at')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        if ($deletedCount > 0) {
            $this->info("Deleted {$deletedCount} unverified student accounts.");
            Log::info("Deleted {$deletedCount} unverified student accounts during cleanup.");
        } else {
            $this->info("No unverified student accounts found older than 48 hours.");
        }
    }
}
