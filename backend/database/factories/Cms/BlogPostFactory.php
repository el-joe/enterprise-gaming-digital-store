<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\BlogPostStatus;
use App\Domains\Cms\Models\BlogPost;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BlogPost>
 */
class BlogPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(3),
            'body' => fake()->sentence(),
            'status' => BlogPostStatus::Draft,
        ];
    }
}
