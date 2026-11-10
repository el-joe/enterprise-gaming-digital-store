<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Enums\SupplierSyncRunStatus;
use App\Domains\Suppliers\Enums\SupplierSyncRunType;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierSyncRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierSyncRun>
 */
class SupplierSyncRunFactory extends Factory
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
            'type' => SupplierSyncRunType::Catalog,
            'triggered_by' => fake()->lexify('????????????'),
            'status' => SupplierSyncRunStatus::Running,
            'started_at' => now(),
        ];
    }
}
