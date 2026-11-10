<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProductPrice>
 */
class SupplierProductPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_product_id' => SupplierProduct::factory(),
            'denomination' => '10.000000',
            'cost' => '10.000000',
            'cost_currency' => 'USD',
            'captured_at' => now(),
        ];
    }
}
