<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Process due reminders daily at 8:00 AM
Schedule::command('reminders:process')->dailyAt('08:00');

// Send weekly digest emails every Monday at 9:00 AM
Schedule::job(new \App\Jobs\SendWeeklyDigestJob())->weeklyOn(1, '09:00');
