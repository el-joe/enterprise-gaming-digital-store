<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\GamingPlatform;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GamingPlatform>
 */
class GamingPlatformFactory extends Factory
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
            'slug' => fake()->unique()->slug(3),
            'is_active' => true,
        ];
    }
}
