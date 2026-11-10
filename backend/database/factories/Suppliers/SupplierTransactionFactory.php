<?php

namespace Database\Factories\Suppliers;

use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierTransaction>
 */
class SupplierTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_item_unit_id' => OrderItemUnit::factory(),
            'supplier_id' => Supplier::factory(),
            'attempt_no' => 1,
            'client_reference' => fake()->unique()->lexify('u????????????s1'),
            'status' => SupplierTransactionStatus::Submitting,
            'requested_amount' => '10.000000',
            'requested_currency' => 'USD',
        ];
    }
}
