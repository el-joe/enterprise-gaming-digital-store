<?php

namespace Database\Factories\Currency;

use App\Domains\Currency\Models\ExchangeRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExchangeRate>
 */
class ExchangeRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'base_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'quote_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'effective_rate' => '1.000000000000',
            'source' => fake()->lexify('????????????'),
            'fetched_at' => now(),
        ];
    }
}
