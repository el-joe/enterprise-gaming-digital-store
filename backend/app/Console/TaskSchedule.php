<?php

namespace App\Console;

use App\Domains\Automation\Enums\AutomationTask;
use App\Domains\Automation\Services\AutomationTaskDrivers;
use Illuminate\Console\Scheduling\Schedule;

/**
 * Every scheduled command (PROJECT_ARCHITECTURE.md section 5), in one place so that the scheduler
 * (routes/console.php) and the scheduled-task monitor (GET /monitor/scheduler, MCP
 * get_system_health) read the same definitions. Each run writes a scheduled_task_runs heartbeat
 * (RecordScheduledTaskHeartbeat).
 *
 * Catalog sync, exchange rates and daily payment reconciliation skip when
 * `settings.automation.task_drivers` hands them to n8n (ADR-014).
 */
final class TaskSchedule
{
    public static function register(Schedule $schedule): void
    {
        $schedulerDrives = static fn (AutomationTask $task): \Closure => static fn (): bool => app(AutomationTaskDrivers::class)->schedulerDrives($task);

        $schedule->command('currency:fetch-rates')->hourlyAt(5)->when($schedulerDrives(AutomationTask::ExchangeRates))->withoutOverlapping()->onOneServer();
        $schedule->command('pricing:recalculate-dirty')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
        $schedule->command('orders:expire-unpaid')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
        $schedule->command('orders:prune-idempotency-keys')->daily()->withoutOverlapping()->onOneServer();

        // Supplier engine (PROJECT_ARCHITECTURE.md section 5)
        $schedule->command('suppliers:sweep-stale-submissions')->everyMinute()->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:verify-pending')->everyMinute()->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:reconcile-daily')->dailyAt('02:30')->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:refresh-balances')->everyFifteenMinutes()->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:health-check')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:sync-catalog')->dailyAt('03:00')->when($schedulerDrives(AutomationTask::CatalogSync))->withoutOverlapping()->onOneServer();
        $schedule->command('suppliers:sync-pricing')->everySixHours()->withoutOverlapping()->onOneServer();

        // Payments (PROJECT_ARCHITECTURE.md 9.5)
        $schedule->command('payments:reconcile-open')->everyFiveMinutes()->withoutOverlapping()->onOneServer();
        $schedule->command('payments:reconcile-daily')->dailyAt('02:45')->when($schedulerDrives(AutomationTask::PaymentReconciliation))->withoutOverlapping()->onOneServer();

        // Wallet (M11)
        $schedule->command('wallet:verify-ledger')->dailyAt('03:30')->withoutOverlapping()->onOneServer();

        // MCP (M16)
        $schedule->command('mcp:expire-pending-actions')->everyFiveMinutes()->withoutOverlapping()->onOneServer();

        // Automation (M17)
        $schedule->command('automation:flush-outbox')->everyMinute()->withoutOverlapping()->onOneServer();
    }
}
