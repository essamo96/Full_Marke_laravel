<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('students:delete-unverified')->hourly();
Schedule::command('exams:notify-starting')->everyMinute();
Schedule::command('library:cleanup-incoming')->dailyAt('03:30')->withoutOverlapping();
