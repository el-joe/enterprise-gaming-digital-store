<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Enums\ScheduledTaskRunStatus;
use App\Domains\Automation\Models\ScheduledTaskRun;
use Illuminate\Console\Scheduling\Event;
use Throwable;

/**
 * Writes scheduled_task_runs heartbeats for every task the scheduler runs: a `running` row when
 * the task starts, closed as `completed` or `failed` (non-zero exit code or exception) when it
 * ends. Only the exception class is stored, never its message, which may carry provider data.
 * Runs are tracked per process because `schedule:run` runs its tasks in that process.
 */
class ScheduledTaskHeartbeat
{
    /** @var array<int, int> spl_object_id(event) => scheduled_task_runs.id */
    private array $open = [];

    /**
     * The artisan command name of a scheduled event (`currency:fetch-rates`), or its summary for
     * a non-artisan event.
     */
    public static function commandName(Event $event): string
    {
        $command = (string) $event->command;

        if (preg_match("/(?:^|\\s)['\"]?artisan['\"]?\\s+(\\S+)/", $command, $match) === 1) {
            return mb_substr(trim($match[1], "'\""), 0, 120);
        }

        return mb_substr($event->getSummaryForDisplay(), 0, 120);
    }

    public function started(Event $event): void
    {
        $run = new ScheduledTaskRun;
        $run->forceFill([
            'command' => self::commandName($event),
            'started_at' => now(),
            'status' => ScheduledTaskRunStatus::Running,
        ])->save();

        $this->open[spl_object_id($event)] = $run->id;
    }

    public function finished(Event $event, float $runtimeSeconds): void
    {
        $failed = $event->exitCode !== null && $event->exitCode !== 0;

        $this->close($event, $failed ? ScheduledTaskRunStatus::Failed : ScheduledTaskRunStatus::Completed, $runtimeSeconds, $failed ? "exit code {$event->exitCode}" : null);
    }

    public function failed(Event $event, Throwable $exception, float $runtimeSeconds): void
    {
        $this->close($event, ScheduledTaskRunStatus::Failed, $runtimeSeconds, 'exception '.$exception::class);
    }

    private function close(Event $event, ScheduledTaskRunStatus $status, float $runtimeSeconds, ?string $error): void
    {
        $id = $this->open[spl_object_id($event)] ?? null;
        unset($this->open[spl_object_id($event)]);

        $values = [
            'status' => $status,
            'finished_at' => now(),
            'duration_ms' => max(0, (int) round($runtimeSeconds * 1000)),
            'error' => $error === null ? null : mb_substr($error, 0, 500),
        ];

        if ($id !== null && ScheduledTaskRun::query()->whereKey($id)->update($values) > 0) {
            return;
        }

        $run = new ScheduledTaskRun;
        $run->forceFill(['command' => self::commandName($event), 'started_at' => now()->subMilliseconds($values['duration_ms']), ...$values])->save();
    }
}
