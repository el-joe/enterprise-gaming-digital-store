<?php

namespace Database\Factories\Currency;

use App\Domains\Currency\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'symbol' => '$',
            'exponent' => 2,
            'is_active' => true,
        ];
    }

    /**
     * Ensure a currency row exists and return its code.
     */
    public static function ensure(string $code = 'USD'): string
    {
        Currency::query()->firstOrCreate(
            ['code' => $code],
            ['name' => $code, 'symbol' => $code, 'exponent' => 2, 'is_active' => true],
        );

        return $code;
    }
}
