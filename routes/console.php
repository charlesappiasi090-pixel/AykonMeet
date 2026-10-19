<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the meeting occurrences generation to run daily at 6 AM
Schedule::command('meetings:generate-occurrences --days=30 --limit=20')
    ->dailyAt('06:00')
    ->withoutOverlapping()
    ->runInBackground();
