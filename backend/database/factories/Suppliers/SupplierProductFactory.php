<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierProduct>
 */
class SupplierProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'external_id' => fake()->unique()->lexify('????????????????'),
            'name' => fake()->words(2, true),
        ];
    }
}
