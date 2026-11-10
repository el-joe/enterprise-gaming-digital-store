<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Models\Cart;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Cart>
 */
class CartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'display_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'last_activity_at' => now(),
        ];
    }
}
