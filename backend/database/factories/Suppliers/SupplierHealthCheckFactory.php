<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierHealthCheck;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierHealthCheck>
 */
class SupplierHealthCheckFactory extends Factory
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
            'reachable' => false,
        ];
    }
}
