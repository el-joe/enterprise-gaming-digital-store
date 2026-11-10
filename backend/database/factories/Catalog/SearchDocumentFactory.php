<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\SearchDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchDocument>
 */
class SearchDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'searchable_type' => 'product',
            'searchable_id' => fake()->numberBetween(1, 1000),
            'locale' => 'en',
            'title' => fake()->words(2, true),
            'keywords' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
