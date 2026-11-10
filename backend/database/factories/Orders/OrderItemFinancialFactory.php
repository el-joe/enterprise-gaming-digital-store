<?php

namespace Database\Factories\Orders;

use App\Domains\Catalog\Models\Product;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemFinancial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItemFinancial>
 */
class OrderItemFinancialFactory extends Factory
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
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'country_code' => 'US',
            'currency' => 'USD',
            'revenue_base' => '10.000000',
            'supplier_cost_base' => '10.000000',
            'gateway_fee_base' => '10.000000',
            'gateway_fee_is_estimate' => false,
            'fx_cost_base' => '10.000000',
            'other_cost_base' => '10.000000',
            'gross_profit_base' => '10.000000',
            'net_profit_base' => '10.000000',
            'recognized_at' => now(),
        ];
    }
}
