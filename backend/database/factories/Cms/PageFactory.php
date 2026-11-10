<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\PageStatus;
use App\Domains\Cms\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
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
            'status' => PageStatus::Draft,
        ];
    }
}
