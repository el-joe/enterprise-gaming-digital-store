<?php

namespace Database\Factories\Analytics;

use App\Domains\Analytics\Enums\DailySalesSummaryDimension;
use App\Domains\Analytics\Models\DailySalesSummary;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailySalesSummary>
 */
class DailySalesSummaryFactory extends Factory
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
            'dimension' => DailySalesSummaryDimension::Total,
            'dimension_key' => fake()->unique()->lexify('????????????'),
            'orders_count' => fake()->numberBetween(1, 1000),
            'units_count' => fake()->numberBetween(1, 1000),
            'revenue_base' => '10.000000',
            'refunds_base' => '10.000000',
            'supplier_cost_base' => '10.000000',
            'gateway_fee_base' => '10.000000',
            'gross_profit_base' => '10.000000',
            'net_profit_base' => '10.000000',
        ];
    }
}
