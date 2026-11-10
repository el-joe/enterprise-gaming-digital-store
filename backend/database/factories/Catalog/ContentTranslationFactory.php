<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Models\ContentTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentTranslation>
 */
class ContentTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'translatable_type' => 'product',
            'translatable_id' => fake()->numberBetween(1, 1000),
            'locale' => 'en',
            'field' => fake()->unique()->lexify('????????????'),
            'value' => fake()->sentence(),
        ];
    }
}
