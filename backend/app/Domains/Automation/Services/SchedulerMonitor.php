<?php

namespace App\Domains\Automation\Services;

use App\Console\TaskSchedule;
use App\Domains\Automation\Enums\AutomationTask;
use App\Domains\Automation\Enums\ScheduledTaskRunStatus;
use App\Domains\Automation\Models\ScheduledTaskRun;
use Carbon\CarbonImmutable;
use Cron\CronExpression;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Collection;

/**
 * Scheduled-task health from scheduled_task_runs heartbeats (F04 and the MCP get_system_health
 * scheduler check). Each task in TaskSchedule is compared with its own cron expression: a task is
 * `late` when it was due after its latest run started and still has not run a grace period later, `failing` when its
 * latest finished run failed, and `never_run` when no heartbeat exists. Tasks handed to n8n by
 * `settings.automation.task_drivers` are reported as `driven_by_n8n` and not judged.
 */
class SchedulerMonitor
{
    public function __construct(private AutomationTaskDrivers $drivers) {}

    /**
     * @return array{status: string, checked_at: string, grace_seconds: int, counts: array<string, int>, tasks: list<array<string, mixed>>}
     */
    public function report(): array
    {
        $now = CarbonImmutable::now();
        $grace = (int) config('automation.scheduler_grace_seconds');
        $events = $this->events();
        $latest = $this->latestRuns($events->map(static fn (Event $event): string => ScheduledTaskHeartbeat::commandName($event))->unique()->values()->all());
        $n8nCommands = $this->n8nDrivenCommands();

        $tasks = $events->map(function (Event $event) use ($now, $grace, $latest, $n8nCommands): array {
            $command = ScheduledTaskHeartbeat::commandName($event);
            $cron = new CronExpression($event->expression);
            $timezone = $event->timezone ?? 'UTC';
            $lastDue = CarbonImmutable::instance($cron->getPreviousRunDate($now->setTimezone($timezone), 0, true));
            $run = $latest->get($command);
            $dueAfterRun = $run === null ? null : CarbonImmutable::instance($cron->getNextRunDate($run->started_at->toImmutable()->setTimezone($timezone), 0, false));

            $state = match (true) {
                in_array($command, $n8nCommands, true) => 'driven_by_n8n',
                $run === null => $now->diffInSeconds($lastDue, true) > $grace ? 'never_run' : 'ok',
                $run->status === ScheduledTaskRunStatus::Failed => 'failing',
                $dueAfterRun !== null && $now->gt($dueAfterRun->addSeconds($grace)) => 'late',
                default => 'ok',
            };

            return [
                'command' => $command,
                'expression' => $event->expression,
                'last_due_at' => $lastDue->toIso8601String(),
                'state' => $state,
                'last_run' => $run === null ? null : [
                    'status' => $run->status->value,
                    'started_at' => $run->started_at->toIso8601String(),
                    'finished_at' => $run->finished_at?->toIso8601String(),
                    'duration_ms' => $run->duration_ms,
                    'error' => $run->error,
                ],
            ];
        })->values()->all();

        $counts = array_count_values(array_column($tasks, 'state'));
        $problems = ($counts['late'] ?? 0) + ($counts['failing'] ?? 0) + ($counts['never_run'] ?? 0);

        return [
            'status' => $problems === 0 ? 'ok' : 'degraded',
            'checked_at' => $now->toIso8601String(),
            'grace_seconds' => $grace,
            'counts' => $counts,
            'tasks' => $tasks,
        ];
    }

    /**
     * @return Collection<int, Event>
     */
    private function events(): Collection
    {
        $schedule = new Schedule(config('app.schedule_timezone'));
        TaskSchedule::register($schedule);

        return collect($schedule->events());
    }

    /**
     * The latest heartbeat per command, in one query.
     *
     * @param  list<string>  $commands
     * @return Collection<string, ScheduledTaskRun>
     */
    private function latestRuns(array $commands): Collection
    {
        if ($commands === []) {
            return collect();
        }

        return ScheduledTaskRun::query()
            ->whereIn('id', ScheduledTaskRun::query()->selectRaw('MAX(id)')->whereIn('command', $commands)->groupBy('command'))
            ->get()
            ->keyBy('command');
    }

    /**
     * @return list<string>
     */
    private function n8nDrivenCommands(): array
    {
        return array_values(array_map(
            static fn (AutomationTask $task): string => $task->command(),
            array_filter(AutomationTask::cases(), fn (AutomationTask $task): bool => ! $this->drivers->schedulerDrives($task)),
        ));
    }
}
