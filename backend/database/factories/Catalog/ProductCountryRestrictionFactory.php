<?php

namespace Database\Factories\Catalog;

use App\Domains\Catalog\Enums\ProductCountryRestrictionMode;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductCountryRestriction;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCountryRestriction>
 */
class ProductCountryRestrictionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'country_code' => fn (): string => CountryFactory::ensure('US'),
            'mode' => ProductCountryRestrictionMode::Allow,
        ];
    }
}
