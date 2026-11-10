<?php

namespace Database\Factories\Currency;

use App\Domains\Currency\Models\ExchangeRateHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRateHistory>
 */
class ExchangeRateHistoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'base_currency' => 'USD',
            'quote_currency' => 'USD',
            'effective_rate' => '1.000000000000',
            'source' => fake()->lexify('????????????'),
            'is_manual' => false,
            'captured_at' => now(),
        ];
    }
}
