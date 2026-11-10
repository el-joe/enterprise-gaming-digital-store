<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AutomationLogStatus;
use App\Domains\Automation\Models\AutomationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationLog>
 */
class AutomationLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => fake()->unique()->bothify('A##??'),
            'execution_key' => fake()->unique()->lexify('????????????????'),
            'status' => AutomationLogStatus::Started,
            'started_at' => now(),
        ];
    }
}
