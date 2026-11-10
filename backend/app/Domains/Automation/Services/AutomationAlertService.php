<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\DTOs\AutomationCaller;
use App\Domains\Shared\Services\SensitiveDataRedactor;
use Illuminate\Support\Facades\Log;

/**
 * POST /alerts from the error workflow (Z01) or a monitoring workflow. The alert is written to the
 * application log at its severity (so it reaches the same log channels as Laravel's own alerts),
 * the failed workflow's last failure time is set and, when the failed execution is still marked
 * running, it is closed as failed (by key, or the newest running one when n8n cannot give the key). Nothing else changes.
 */
class AutomationAlertService
{
    public function __construct(
        private AutomationExecutionService $executions,
        private AutomationWorkflowRegistry $workflows,
        private SensitiveDataRedactor $redactor,
    ) {}

    /**
     * @param  array{severity: string, title: string, message?: string|null, failed_workflow_id?: string|null, failed_execution_key?: string|null, n8n_execution_id?: string|null, node?: string|null, context?: array<string, mixed>|null}  $alert
     * @return array{recorded: bool, severity: string, execution_closed: bool}
     */
    public function record(AutomationCaller $caller, array $alert): array
    {
        $failedWorkflow = $alert['failed_workflow_id'] ?? null;
        $closed = false;

        if ($failedWorkflow !== null) {
            $this->workflows->recordOutcome($failedWorkflow, false);

            $closed = $this->executions->failFromAlert($failedWorkflow, $alert['failed_execution_key'] ?? null, (string) ($alert['message'] ?? $alert['title']));
        }

        $level = match ($alert['severity']) {
            'critical' => 'critical',
            'warning' => 'warning',
            default => 'info',
        };

        Log::log($level, 'automation.alert', $this->redactor->redact([
            'reported_by' => $caller->workflowId,
            'title' => $alert['title'],
            'message' => $alert['message'] ?? null,
            'failed_workflow_id' => $failedWorkflow,
            'failed_execution_key' => $alert['failed_execution_key'] ?? null,
            'n8n_execution_id' => $alert['n8n_execution_id'] ?? null,
            'node' => $alert['node'] ?? null,
            'context' => $alert['context'] ?? null,
        ]));

        return ['recorded' => true, 'severity' => $alert['severity'], 'execution_closed' => $closed];
    }
}
