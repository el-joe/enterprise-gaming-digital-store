<?php

namespace Database\Factories\Analytics;

use App\Domains\Analytics\Enums\SearchConsoleMetricDimension;
use App\Domains\Analytics\Models\SearchConsoleMetric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SearchConsoleMetric>
 */
class SearchConsoleMetricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'date' => now()->toDateString(),
            'dimension' => SearchConsoleMetricDimension::Query,
            'key_hash' => fake()->unique()->sha256(),
            'key_value' => fake()->sentence(4),
            'clicks' => fake()->numberBetween(1, 1000),
            'impressions' => fake()->numberBetween(1, 1000),
            'ctr' => '1.00',
            'position' => '1.00',
        ];
    }
}
