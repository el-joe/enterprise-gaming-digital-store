<?php

namespace Database\Seeders;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Seed Reloadly (inactive until credentials exist) and the two built-in fulfilment paths.
     */
    public function run(): void
    {
        $suppliers = [
            ['Reloadly Gift Cards', 'reloadly_giftcards', SupplierStatus::Inactive, 100],
            ['Internal Stock', 'internal_stock', SupplierStatus::Active, 50],
            ['Manual Fulfilment', 'manual_fulfillment', SupplierStatus::Active, 200],
        ];

        foreach ($suppliers as [$name, $adapterKey, $status, $priority]) {
            $supplier = Supplier::query()->firstOrNew(['name' => $name]);

            $supplier->forceFill([
                'adapter_key' => $adapterKey,
                'status' => $status,
                'mode' => EnvironmentMode::Sandbox,
                'priority' => $priority,
            ])->save();
        }
    }
}
