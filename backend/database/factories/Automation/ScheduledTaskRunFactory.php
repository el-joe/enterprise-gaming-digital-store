<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\ScheduledTaskRunStatus;
use App\Domains\Automation\Models\ScheduledTaskRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScheduledTaskRun>
 */
class ScheduledTaskRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'command' => fake()->lexify('????????????'),
            'started_at' => now(),
            'status' => ScheduledTaskRunStatus::Running,
        ];
    }
}
