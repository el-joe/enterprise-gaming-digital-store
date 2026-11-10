<?php

namespace Database\Factories\Automation;

use App\Domains\Automation\Enums\CustomerMessageStatus;
use App\Domains\Automation\Models\AutomationCustomerMessage;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AutomationCustomerMessage>
 */
class AutomationCustomerMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workflow_id' => 'D07',
            'user_id' => User::factory(),
            'channel' => 'sms',
            'category' => NotificationPreferenceCategory::Marketing,
            'dedupe_key' => 'test:'.fake()->unique()->uuid(),
            'status' => CustomerMessageStatus::Sent,
        ];
    }
}
