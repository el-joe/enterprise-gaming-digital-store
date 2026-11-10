<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemUnit>
 */
class OrderItemUnitFactory extends Factory
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
            'order_id' => fn (array $attributes): int => OrderItem::query()->findOrFail($attributes['order_item_id'])->order_id,
            'status' => OrderItemUnitStatus::Pending,
        ];
    }
}
