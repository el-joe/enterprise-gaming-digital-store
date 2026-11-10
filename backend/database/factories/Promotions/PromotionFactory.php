<?php

namespace Database\Factories\Promotions;

use App\Domains\Promotions\Enums\PromotionType;
use App\Domains\Promotions\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'type' => PromotionType::Percent,
            'value' => '10.000000',
            'starts_at' => now(),
            'is_active' => true,
        ];
    }
}
