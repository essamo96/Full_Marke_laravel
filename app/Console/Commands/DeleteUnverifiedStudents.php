<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Student;
use Carbon\Carbon;

class DeleteUnverifiedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'students:delete-unverified';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete students who have not verified their email within 24 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subHours(24);
        
        $count = Student::whereNull('email_verified_at')
                        ->where('created_at', '<', $cutoff)
                        ->delete();
                        
        $this->info("Deleted {$count} unverified students.");
    }
}
