<?php

namespace Database\Seeders;

use App\Domains\Catalog\Models\Region;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Seed the commercial regions and their member countries.
     */
    public function run(): void
    {
        $regions = [
            'GLOBAL' => ['Global', true, []],
            'US' => ['United States', false, ['US']],
            'UK' => ['United Kingdom', false, ['GB']],
            'EU' => ['European Union', false, ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE']],
            'MENA' => ['Middle East and North Africa', false, ['AE', 'BH', 'DZ', 'EG', 'IQ', 'JO', 'KW', 'LB', 'LY', 'MA', 'OM', 'PS', 'QA', 'SA', 'TN', 'YE']],
        ];

        foreach ($regions as $code => [$name, $isGlobal, $countries]) {
            $region = Region::query()->updateOrCreate(['code' => $code], ['name' => $name, 'is_global' => $isGlobal]);
            $region->countries()->syncWithoutDetaching($countries);
        }
    }
}
