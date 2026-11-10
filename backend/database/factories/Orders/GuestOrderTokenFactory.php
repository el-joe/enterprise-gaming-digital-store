<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Enums\GuestOrderTokenSource;
use App\Domains\Orders\Models\GuestOrderToken;
use App\Domains\Orders\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GuestOrderToken>
 */
class GuestOrderTokenFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory()->state(['user_id' => null]),
            'token_hash' => fake()->unique()->sha256(),
            'source' => GuestOrderTokenSource::Placement,
            'expires_at' => now()->addDay(),
        ];
    }
}
