<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderEvent>
 */
class OrderEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'type' => fake()->lexify('????????????'),
            'actor_type' => OrderEventActorType::System,
        ];
    }
}
