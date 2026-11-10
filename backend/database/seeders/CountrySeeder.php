<?php

namespace Database\Seeders;

use App\Domains\Currency\Models\Currency;
use App\Domains\Shared\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Seed ISO 3166-1 countries. The default currency has no foreign key, so it is validated here.
     */
    public function run(): void
    {
        /** @var array<string, array{0: string, 1: string|null, 2: string|null}> $countries */
        $countries = require database_path('seeders/data/countries.php');

        $knownCurrencies = Currency::query()->pluck('code')->flip();

        foreach ($countries as $code => [$name, $dialCode, $currency]) {
            Country::query()->updateOrCreate(['code' => $code], [
                'name' => $name,
                'dial_code' => $dialCode,
                'default_currency' => $knownCurrencies->has($currency) ? $currency : null,
                'is_active' => true,
            ]);
        }
    }
}
