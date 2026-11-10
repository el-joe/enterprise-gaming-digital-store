<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Models\FaqGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqGroup>
 */
class FaqGroupFactory extends Factory
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
        ];
    }
}
