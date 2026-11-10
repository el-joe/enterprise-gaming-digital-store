<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location' => fake()->unique()->lexify('????????????'),
            'name' => fake()->words(2, true),
        ];
    }
}
