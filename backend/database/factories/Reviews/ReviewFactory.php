<?php

namespace Database\Factories\Reviews;

use App\Domains\Catalog\Models\Product;
use App\Domains\Reviews\Enums\ReviewStatus;
use App\Domains\Reviews\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'status' => ReviewStatus::Pending,
        ];
    }
}
