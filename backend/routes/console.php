<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('currency:fetch-rates')->hourlyAt(5)->withoutOverlapping()->onOneServer();
Schedule::command('pricing:recalculate-dirty')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('orders:expire-unpaid')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('orders:prune-idempotency-keys')->daily()->withoutOverlapping()->onOneServer();

// Supplier engine (PROJECT_ARCHITECTURE.md section 5)
Schedule::command('suppliers:sweep-stale-submissions')->everyMinute()->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:verify-pending')->everyMinute()->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:reconcile-daily')->dailyAt('02:30')->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:refresh-balances')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:health-check')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:sync-catalog')->dailyAt('03:00')->withoutOverlapping()->onOneServer();
Schedule::command('suppliers:sync-pricing')->everySixHours()->withoutOverlapping()->onOneServer();

// Payments (PROJECT_ARCHITECTURE.md 9.5)
Schedule::command('payments:reconcile-open')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
Schedule::command('payments:reconcile-daily')->dailyAt('02:45')->withoutOverlapping()->onOneServer();
