<?php

namespace Database\Factories\Seo;

use App\Domains\Seo\Models\SeoMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMetadata>
 */
class SeoMetadataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'seoable_type' => 'product',
            'seoable_id' => fake()->numberBetween(1, 1000),
            'locale' => 'en',
        ];
    }
}
