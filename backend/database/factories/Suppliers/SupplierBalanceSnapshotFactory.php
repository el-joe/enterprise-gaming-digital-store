<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierBalanceSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierBalanceSnapshot>
 */
class SupplierBalanceSnapshotFactory extends Factory
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
            'balance' => '10.000000',
            'currency' => 'USD',
            'captured_at' => now(),
        ];
    }
}
