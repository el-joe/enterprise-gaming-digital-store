<?php

namespace Database\Factories\Fulfillment;

use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManualFulfillmentTask>
 */
class ManualFulfillmentTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_unit_id' => OrderItemUnit::factory(),
            'status' => ManualFulfillmentTaskStatus::Open,
        ];
    }
}
