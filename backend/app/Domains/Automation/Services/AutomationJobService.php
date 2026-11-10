<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Automation\Enums\AutomationTask;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

/**
 * POST /jobs/*: n8n asks Laravel to run a task that n8n drives (A09, A10, E02). The work is the
 * same artisan command the scheduler would run, queued on the `default` queue; n8n never does the
 * work itself. A task runs at most once per execution: the running execution row records the
 * queued job, and a repeated call for the same execution returns that record.
 */
class AutomationJobService
{
    public function __construct(private AutomationTaskDrivers $drivers, private AutomationExecutionService $executions) {}

    /**
     * @return array{task: string, command: string, status: string, queued_at: string, repeated: bool}
     */
    public function run(AutomationCaller $caller, AutomationTask $task): array
    {
        if ($caller->workflowId !== $task->workflowId()) {
            throw new ApplicationException("Only workflow {$task->workflowId()} may run this task.", 'forbidden', 403);
        }

        if ($this->drivers->schedulerDrives($task)) {
            throw new ApplicationException("The Laravel scheduler drives {$task->value}; set settings.automation.task_drivers.{$task->value} to n8n first.", 'task_driver_mismatch', 409);
        }

        return DB::transaction(function () use ($caller, $task): array {
            $execution = $this->executions->lockRunning($caller);
            $summary = $execution->summary ?? [];

            if (isset($summary['job']) && is_array($summary['job'])) {
                return [...$summary['job'], 'repeated' => true];
            }

            $job = ['task' => $task->value, 'command' => $task->command(), 'status' => 'queued', 'queued_at' => now()->toIso8601String()];

            Artisan::queue($task->command())->onQueue('default')->afterCommit();

            $execution->forceFill(['summary' => [...$summary, 'job' => $job]])->save();

            return [...$job, 'repeated' => false];
        });
    }
}
