<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSection>
 */
class HomepageSectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->lexify('????????????????'),
            'source' => HomepageSectionSource::Manual,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 1000),
        ];
    }
}
