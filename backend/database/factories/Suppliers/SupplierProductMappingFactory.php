<?php

namespace Database\Factories\Suppliers;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProductMapping>
 */
class SupplierProductMappingFactory extends Factory
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
            'supplier_id' => Supplier::factory(),
            'supplier_product_id' => fn (array $attributes): SupplierProductFactory => SupplierProduct::factory()->state(['supplier_id' => $attributes['supplier_id']]),
            'role' => SupplierProductMappingRole::Primary,
            'supplier_unit_price' => '10.000000',
            'supplier_unit_currency' => 'USD',
        ];
    }
}
