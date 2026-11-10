<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Models\AutomationWorkflow;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationWorkflow>
 */
class AutomationWorkflowFactory extends Factory
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
            'name' => fake()->words(2, true),
            'category' => fake()->lexify('????????????'),
            'is_enabled' => true,
        ];
    }
}
