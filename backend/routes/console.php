<?php

use App\Console\TaskSchedule;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Every scheduled command is defined in App\Console\TaskSchedule, which the scheduled-task
// monitor reads as well.
TaskSchedule::register(app(Schedule::class));
