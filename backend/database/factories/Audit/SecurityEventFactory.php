<?php

namespace Database\Factories\Audit;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Models\SecurityEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SecurityEvent>
 */
class SecurityEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->lexify('????????????'),
            'severity' => SecurityEventSeverity::Info,
        ];
    }
}
