<?php

namespace Database\Factories\Pricing;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\Models\VariantPrice;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VariantPrice>
 */
class VariantPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => ProductVariant::factory(),
            'country_code' => 'US',
            'currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'price' => '10.000000',
        ];
    }
}
