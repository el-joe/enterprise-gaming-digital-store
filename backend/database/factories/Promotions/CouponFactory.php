<?php

namespace Database\Factories\Promotions;

use App\Domains\Promotions\Enums\CouponCreatedByType;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->lexify('????????????????'),
            'type' => CouponType::Percent,
            'value' => '10.000000',
            'created_by_type' => CouponCreatedByType::Staff,
        ];
    }
}
