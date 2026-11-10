<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Models\IdempotencyKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IdempotencyKey>
 */
class IdempotencyKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scope' => fake()->unique()->lexify('????????????'),
            'key' => fake()->unique()->lexify('????????????????'),
            'owner_hash' => fake()->unique()->sha256(),
            'request_hash' => fake()->sha256(),
            'expires_at' => now(),
        ];
    }
}
