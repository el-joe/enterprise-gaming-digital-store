<?php

/**
 * Inherited gap from M9 and M16: every scheduled command writes scheduled_task_runs heartbeats,
 * so F04 (GET /monitor/scheduler) and the MCP get_system_health scheduler check are real.
 */

use App\Console\TaskSchedule;
use App\Domains\Automation\Enums\ScheduledTaskRunStatus;
use App\Domains\Automation\Models\ScheduledTaskRun;
use App\Domains\Automation\Services\ScheduledTaskHeartbeat;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Console\Scheduling\Event as ScheduledEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

function scheduledEvent(string $command): ScheduledEvent
{
    $schedule = new Schedule('UTC');
    TaskSchedule::register($schedule);

    return collect($schedule->events())->first(fn (ScheduledEvent $event): bool => ScheduledTaskHeartbeat::commandName($event) === $command)
        ?? throw new RuntimeException("{$command} is not scheduled");
}

/**
 * A completed heartbeat for every scheduled command, started now.
 */
function freshHeartbeats(): void
{
    $schedule = new Schedule('UTC');
    TaskSchedule::register($schedule);

    foreach ($schedule->events() as $event) {
        ScheduledTaskRun::factory()->create(['command' => ScheduledTaskHeartbeat::commandName($event), 'status' => ScheduledTaskRunStatus::Completed, 'started_at' => now(), 'finished_at' => now()]);
    }
}

it('names every scheduled command by its artisan name', function (): void {
    $schedule = new Schedule('UTC');
    TaskSchedule::register($schedule);

    $names = collect($schedule->events())->map(fn (ScheduledEvent $event): string => ScheduledTaskHeartbeat::commandName($event))->all();

    expect($names)->toContain('currency:fetch-rates', 'suppliers:sweep-stale-submissions', 'payments:reconcile-daily', 'mcp:expire-pending-actions', 'automation:flush-outbox')
        ->and($names)->each->toMatch('/^[a-z0-9-]+:[a-z0-9:-]+$/');
});

it('records a completed heartbeat with its duration through the scheduler events', function (): void {
    $event = scheduledEvent('orders:expire-unpaid');

    Event::dispatch(new ScheduledTaskStarting($event));
    $running = ScheduledTaskRun::query()->sole();
    expect($running->status)->toBe(ScheduledTaskRunStatus::Running)->and($running->command)->toBe('orders:expire-unpaid');

    $event->exitCode = 0;
    Event::dispatch(new ScheduledTaskFinished($event, 1.25));

    $run = ScheduledTaskRun::query()->sole();
    expect($run->status)->toBe(ScheduledTaskRunStatus::Completed)
        ->and($run->duration_ms)->toBe(1250)
        ->and($run->finished_at)->not->toBeNull()
        ->and($run->error)->toBeNull();
});

it('records a non-zero exit code as a failed run', function (): void {
    $event = scheduledEvent('currency:fetch-rates');

    Event::dispatch(new ScheduledTaskStarting($event));
    $event->exitCode = 1;
    Event::dispatch(new ScheduledTaskFinished($event, 0.4));

    $run = ScheduledTaskRun::query()->sole();
    expect($run->status)->toBe(ScheduledTaskRunStatus::Failed)->and($run->error)->toBe('exit code 1');
});

it('records an exception by class only, never its message', function (): void {
    $event = scheduledEvent('suppliers:health-check');

    Event::dispatch(new ScheduledTaskStarting($event));
    Event::dispatch(new ScheduledTaskFailed($event, new RuntimeException('provider said: secret-token-123'), 2.0));

    $run = ScheduledTaskRun::query()->sole();
    expect($run->status)->toBe(ScheduledTaskRunStatus::Failed)
        ->and($run->error)->toBe('exception RuntimeException')
        ->and($run->error)->not->toContain('secret-token-123');
});

it('skips tasks handed to n8n in the scheduler and runs them otherwise', function (): void {
    $fetchRates = scheduledEvent('currency:fetch-rates');
    expect($fetchRates->filtersPass(app()))->toBeTrue();

    app(SettingsService::class)->set('automation', 'task_drivers', ['exchange_rates' => 'n8n'], SettingType::Json);

    expect(scheduledEvent('currency:fetch-rates')->filtersPass(app()))->toBeFalse()
        ->and(scheduledEvent('suppliers:sync-catalog')->filtersPass(app()))->toBeTrue()
        ->and(scheduledEvent('payments:reconcile-daily')->filtersPass(app()))->toBeTrue();
});

it('reports the scheduler as ok when every task ran since it was last due', function (): void {
    enableAutomationWorkflows(['F04']);
    ['token' => $token] = automationCredential();
    $this->travelTo(now()->setTime(10, 2));
    freshHeartbeats();

    automationGet($token, 'monitor/scheduler', ['workflow' => 'F04'])
        ->assertOk()
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.alert', false);

    ['token' => $mcp] = mcpToken();
    mcpGet($mcp, 'operations/system-health')
        ->assertOk()
        ->assertJsonPath('data.checks.scheduler.status', 'ok')
        ->assertJsonPath('data.checks.scheduler.problems', []);
});

it('reports late, failing and never-run tasks and excludes tasks n8n drives', function (): void {
    enableAutomationWorkflows(['F04']);
    ['token' => $token] = automationCredential();
    $this->travelTo(now()->setTime(10, 2));
    freshHeartbeats();
    ScheduledTaskRun::factory()->create(['command' => 'suppliers:health-check', 'status' => ScheduledTaskRunStatus::Failed, 'started_at' => now(), 'error' => 'exit code 1']);
    ScheduledTaskRun::query()->where('command', 'wallet:verify-ledger')->delete();
    app(SettingsService::class)->set('automation', 'task_drivers', ['catalog_sync' => 'n8n'], SettingType::Json);
    ScheduledTaskRun::query()->where('command', 'suppliers:sync-catalog')->delete();

    $this->travel(20)->minutes();

    $tasks = collect(automationGet($token, 'monitor/scheduler', ['workflow' => 'F04'])
        ->assertOk()
        ->assertJsonPath('data.status', 'degraded')
        ->assertJsonPath('data.alert', true)
        ->json('data.tasks'))->keyBy('command');

    expect($tasks['suppliers:sweep-stale-submissions']['state'])->toBe('late')
        ->and($tasks['suppliers:health-check']['state'])->toBe('failing')
        ->and($tasks['wallet:verify-ledger']['state'])->toBe('never_run')
        ->and($tasks['suppliers:sync-catalog']['state'])->toBe('driven_by_n8n')
        ->and($tasks['orders:prune-idempotency-keys']['state'])->toBe('ok');

    ['token' => $mcp] = mcpToken();
    $problems = collect(mcpGet($mcp, 'operations/system-health')->assertOk()->assertJsonPath('data.checks.scheduler.status', 'degraded')->json('data.checks.scheduler.problems'))->pluck('state', 'command');

    expect($problems['suppliers:health-check'])->toBe('failing')
        ->and($problems)->not->toHaveKey('suppliers:sync-catalog');
});
