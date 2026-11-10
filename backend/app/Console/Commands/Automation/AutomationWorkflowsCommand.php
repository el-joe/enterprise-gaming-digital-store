<?php

namespace App\Console\Commands\Automation;

use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Automation\Services\AutomationWorkflowRegistry;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('automation:workflows
    {action : list, enable or disable}
    {ids?* : Workflow ids such as A01 Z01; enable and disable need ids or --all}
    {--all : Apply to every workflow}')]
#[Description('List, enable or disable n8n workflows; only enabled workflows may call the automation API')]
class AutomationWorkflowsCommand extends Command
{
    public function handle(AutomationWorkflowRegistry $workflows): int
    {
        $action = (string) $this->argument('action');

        if ($action === 'list') {
            $this->table(['id', 'name', 'category', 'enabled', 'last success', 'last failure'], $workflows->all()->map(static fn (AutomationWorkflow $workflow): array => [
                $workflow->workflow_id,
                $workflow->name,
                $workflow->category,
                $workflow->is_enabled ? 'yes' : 'no',
                $workflow->last_success_at?->toIso8601String() ?? '-',
                $workflow->last_failure_at?->toIso8601String() ?? '-',
            ])->all());

            return self::SUCCESS;
        }

        if (! in_array($action, ['enable', 'disable'], true)) {
            $this->error('Action must be list, enable or disable.');

            return self::FAILURE;
        }

        $ids = array_values(array_map('strtoupper', (array) $this->argument('ids')));

        if ($ids === [] && ! $this->option('all')) {
            $this->error('Give workflow ids or --all.');

            return self::FAILURE;
        }

        try {
            $changed = $workflows->setEnabled($ids, $action === 'enable');
        } catch (ApplicationException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info(ucfirst($action)."d {$changed} workflow(s).");

        return self::SUCCESS;
    }
}
