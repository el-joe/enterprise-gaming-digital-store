<?php

namespace Database\Factories\Seo;

use App\Domains\Seo\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_path' => '/'.fake()->slug(3),
            'source_hash' => fake()->unique()->sha256(),
            'status_code' => 301,
            'is_active' => true,
        ];
    }
}
