<?php

namespace Database\Seeders;

use App\Domains\Automation\Models\AutomationWorkflow;
use Illuminate\Database\Seeder;

class AutomationWorkflowSeeder extends Seeder
{
    /**
     * Register the n8n workflow inventory, all disabled until imported and activated in n8n.
     */
    public function run(): void
    {
        /** @var array<string, array{0: string, 1: string, 2: string|null}> $workflows */
        $workflows = require database_path('seeders/data/automation_workflows.php');

        foreach ($workflows as $workflowId => [$name, $category, $schedule]) {
            AutomationWorkflow::query()->firstOrCreate(['workflow_id' => $workflowId], [
                'name' => $name,
                'category' => $category,
                'schedule' => $schedule,
                'is_enabled' => false,
            ]);
        }
    }
}
