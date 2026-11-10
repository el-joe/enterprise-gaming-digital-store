<?php

namespace Database\Factories\Orders;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'quantity' => 1,
        ];
    }
}
