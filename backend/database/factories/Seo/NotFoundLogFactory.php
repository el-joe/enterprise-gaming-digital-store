<?php

namespace Database\Factories\Seo;

use App\Domains\Seo\Models\NotFoundLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotFoundLog>
 */
class NotFoundLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path_hash' => fake()->unique()->sha256(),
            'path' => '/'.fake()->slug(3),
            'last_seen_at' => now(),
        ];
    }
}
