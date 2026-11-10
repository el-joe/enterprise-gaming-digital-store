<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierApiLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierApiLog>
 */
class SupplierApiLogFactory extends Factory
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
            'operation' => fake()->lexify('????????????'),
            'method' => 'GET',
            'url' => fake()->url(),
            'succeeded' => false,
        ];
    }
}
