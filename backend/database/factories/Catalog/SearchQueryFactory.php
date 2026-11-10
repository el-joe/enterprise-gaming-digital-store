<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\SearchQuery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchQuery>
 */
class SearchQueryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'term' => fake()->unique()->lexify('????????????????'),
            'locale' => 'en',
            'last_searched_at' => now(),
        ];
    }
}
