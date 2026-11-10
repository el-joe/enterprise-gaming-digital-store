<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemDeliveryValue>
 */
class OrderItemDeliveryValueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_id' => OrderItem::factory(),
            'field_code' => fake()->unique()->lexify('????????????'),
            'field_label' => fake()->words(2, true),
            'value' => fake()->sha256(),
        ];
    }
}
