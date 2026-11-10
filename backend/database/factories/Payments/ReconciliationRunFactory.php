<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Enums\ReconciliationRunKind;
use App\Domains\Payments\Enums\ReconciliationRunStatus;
use App\Domains\Payments\Models\ReconciliationRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReconciliationRun>
 */
class ReconciliationRunFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kind' => ReconciliationRunKind::Payments,
            'subject_key' => fake()->unique()->lexify('????????????????'),
            'window_start' => now(),
            'window_end' => now(),
            'status' => ReconciliationRunStatus::Running,
            'started_at' => now(),
        ];
    }
}
