<?php

namespace Database\Factories\Seo;

use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Models\SeoTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoTemplate>
 */
class SeoTemplateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_type' => SeoTemplatePageType::Home,
            'locale' => 'en',
            'title_template' => fake()->words(2, true),
            'description_template' => fake()->sentence(4),
        ];
    }
}
