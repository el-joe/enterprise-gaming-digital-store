<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->lexify('????????????????'),
            'contact_email' => fake()->safeEmail(),
            'status' => OrderStatus::PendingPayment,
            'status_changed_at' => now(),
            'country_code' => fn (): string => CountryFactory::ensure('US'),
            'locale' => 'en',
            'base_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'subtotal_base' => '10.000000',
            'total_base' => '10.000000',
            'display_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'display_rate' => '1.000000000000',
            'display_total' => '10.000000',
        ];
    }
}
