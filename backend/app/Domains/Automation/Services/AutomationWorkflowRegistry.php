<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Collection;

/**
 * The workflow inventory seeded into automation_workflows. Only enabled workflows may call the
 * automation API, so disabling a row is the kill switch for one workflow.
 */
class AutomationWorkflowRegistry
{
    public function assertEnabled(string $workflowId): AutomationWorkflow
    {
        $workflow = AutomationWorkflow::query()->where('workflow_id', $workflowId)->first();

        if ($workflow === null) {
            throw new ApplicationException('This workflow is not registered.', 'workflow_unknown', 403);
        }

        if (! $workflow->is_enabled) {
            throw new ApplicationException('This workflow is disabled.', 'workflow_disabled', 403);
        }

        return $workflow;
    }

    /**
     * @param  list<string>  $workflowIds  empty means every workflow
     * @return int rows changed
     */
    public function setEnabled(array $workflowIds, bool $enabled): int
    {
        $query = AutomationWorkflow::query();

        if ($workflowIds !== []) {
            $unknown = array_values(array_diff($workflowIds, AutomationWorkflow::query()->whereIn('workflow_id', $workflowIds)->pluck('workflow_id')->all()));

            if ($unknown !== []) {
                throw new ApplicationException('Unknown workflow: '.implode(', ', $unknown).'.', 'not_found', 404);
            }

            $query->whereIn('workflow_id', $workflowIds);
        }

        return $query->update(['is_enabled' => $enabled]);
    }

    /**
     * @return Collection<int, AutomationWorkflow>
     */
    public function all(): Collection
    {
        return AutomationWorkflow::query()->orderBy('workflow_id')->get();
    }

    public function recordOutcome(string $workflowId, bool $succeeded): void
    {
        AutomationWorkflow::query()->where('workflow_id', $workflowId)->update([$succeeded ? 'last_success_at' : 'last_failure_at' => now()]);
    }
}
