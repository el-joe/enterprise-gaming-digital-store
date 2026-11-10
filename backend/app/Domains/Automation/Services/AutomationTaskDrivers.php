<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Enums\AutomationTask;
use App\Domains\Settings\Services\SettingsService;

/**
 * ADR-014: the Laravel scheduler drives catalog sync, exchange rates and daily payment
 * reconciliation unless `settings.automation.task_drivers.{task}` is `n8n`. Exactly one driver
 * runs each task: the schedule entry skips when n8n drives it, and the automation job endpoint
 * refuses when the scheduler does.
 */
class AutomationTaskDrivers
{
    public const string SCHEDULER = 'scheduler';

    public const string N8N = 'n8n';

    public function __construct(private SettingsService $settings) {}

    public function driver(AutomationTask $task): string
    {
        $drivers = $this->settings->get('automation', 'task_drivers', []);

        return is_array($drivers) && ($drivers[$task->value] ?? null) === self::N8N ? self::N8N : self::SCHEDULER;
    }

    public function schedulerDrives(AutomationTask $task): bool
    {
        return $this->driver($task) === self::SCHEDULER;
    }

    /**
     * @return array<string, string> task => driver
     */
    public function all(): array
    {
        $drivers = [];

        foreach (AutomationTask::cases() as $task) {
            $drivers[$task->value] = $this->driver($task);
        }

        return $drivers;
    }
}
