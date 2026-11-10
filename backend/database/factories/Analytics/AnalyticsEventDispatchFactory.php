<?php

namespace Database\Factories\Analytics;

use App\Domains\Analytics\Enums\AnalyticsEventDispatchEventName;
use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnalyticsEventDispatch>
 */
class AnalyticsEventDispatchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_name' => AnalyticsEventDispatchEventName::Purchase,
            'order_id' => Order::factory(),
            'dedupe_key' => fake()->unique()->lexify('????????????????'),
            'payload' => ['key' => 'value'],
            'status' => AnalyticsEventDispatchStatus::Pending,
        ];
    }
}
