<?php

namespace Database\Factories\Shared;

use App\Domains\Shared\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('??')),
            'name' => fake()->country(),
        ];
    }

    /**
     * Ensure a country row exists and return its code.
     */
    public static function ensure(string $code = 'US'): string
    {
        Country::query()->firstOrCreate(['code' => $code], ['name' => $code]);

        return $code;
    }
}
