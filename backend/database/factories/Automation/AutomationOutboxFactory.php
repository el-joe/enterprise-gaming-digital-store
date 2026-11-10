<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\AutomationOutboxStatus;
use App\Domains\Automation\Models\AutomationOutbox;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationOutbox>
 */
class AutomationOutboxFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_type' => fake()->lexify('????????????'),
            'payload' => ['key' => 'value'],
            'status' => AutomationOutboxStatus::Pending,
        ];
    }
}
