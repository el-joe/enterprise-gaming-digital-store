<?php

namespace Database\Factories\Pricing;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Models\PricingOverrideLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingOverrideLog>
 */
class PricingOverrideLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_id' => User::factory(),
            'product_variant_id' => ProductVariant::factory(),
            'new_price_base' => '10.000000',
            'reason' => fake()->sentence(),
        ];
    }
}
