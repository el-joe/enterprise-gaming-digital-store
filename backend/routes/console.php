<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('currency:fetch-rates')->hourlyAt(5)->withoutOverlapping()->onOneServer();
Schedule::command('pricing:recalculate-dirty')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
