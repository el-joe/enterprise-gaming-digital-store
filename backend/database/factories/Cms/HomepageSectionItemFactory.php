<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\HomepageSectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HomepageSectionItem>
 */
class HomepageSectionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'homepage_section_id' => HomepageSection::factory(),
            'item_type' => 'product',
            'item_id' => fake()->numberBetween(1, 1000),
        ];
    }
}
