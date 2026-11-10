<?php

namespace Database\Factories\Orders;

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'order_id' => Order::factory(),
            'product_id' => fn (array $attributes): int => ProductVariant::query()->findOrFail($attributes['product_variant_id'])->product_id,
            'name_snapshot' => fake()->words(2, true),
            'sku_snapshot' => strtoupper(fake()->bothify('SKU-####-????')),
            'delivery_type' => DeliveryType::InstantCode,
            'quantity' => 1,
            'unit_price_base' => '10.000000',
            'line_total_base' => '10.000000',
            'pricing_breakdown' => ['key' => 'value'],
        ];
    }
}
