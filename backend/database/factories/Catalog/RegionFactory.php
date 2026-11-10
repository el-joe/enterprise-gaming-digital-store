<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
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
            'name' => fake()->words(2, true),
        ];
    }
}
