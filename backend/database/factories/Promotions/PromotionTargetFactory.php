<?php

namespace Database\Factories\Promotions;

use App\Domains\Promotions\Enums\PromotionTargetTargetType;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PromotionTarget>
 */
class PromotionTargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'promotion_id' => Promotion::factory(),
            'target_type' => PromotionTargetTargetType::Product,
            'target_id' => fake()->numberBetween(1, 1000),
        ];
    }
}
