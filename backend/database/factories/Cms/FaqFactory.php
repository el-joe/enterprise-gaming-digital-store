<?php

namespace Database\Factories\Cms;

use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\FaqGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faq_group_id' => FaqGroup::factory(),
            'question' => fake()->sentence(4),
            'answer' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
