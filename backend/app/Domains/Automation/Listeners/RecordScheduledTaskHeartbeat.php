<?php

namespace App\Domains\Automation\Listeners;

use App\Domains\Automation\Services\ScheduledTaskHeartbeat;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

/**
 * Turns the scheduler's own task events into scheduled_task_runs heartbeats.
 */
class RecordScheduledTaskHeartbeat
{
    public function __construct(private ScheduledTaskHeartbeat $heartbeat) {}

    public function handle(ScheduledTaskStarting|ScheduledTaskFinished|ScheduledTaskFailed $event): void
    {
        match (true) {
            $event instanceof ScheduledTaskStarting => $this->heartbeat->started($event->task),
            $event instanceof ScheduledTaskFinished => $this->heartbeat->finished($event->task, (float) $event->runtime),
            $event instanceof ScheduledTaskFailed => $this->heartbeat->failed($event->task, $event->exception, (float) $event->runtime),
        };
    }
}
