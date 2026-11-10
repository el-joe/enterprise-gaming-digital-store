<?php

namespace Database\Seeders;

use App\Domains\Currency\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Seed ISO 4217 currencies. Only the base currency starts active, because the others have
     * no exchange rate until the first provider fetch or an admin override supplies one.
     */
    public function run(): void
    {
        /** @var array<string, array{0: string, 1: string, 2: int}> $currencies */
        $currencies = require database_path('seeders/data/currencies.php');

        foreach ($currencies as $code => [$name, $symbol, $exponent]) {
            Currency::query()->firstOrCreate(['code' => $code], [
                'name' => $name,
                'symbol' => $symbol,
                'exponent' => $exponent,
                'is_active' => $code === 'USD',
            ]);
        }
    }
}
