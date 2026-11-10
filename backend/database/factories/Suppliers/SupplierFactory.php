<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->lexify('????????????????'),
            'adapter_key' => fake()->lexify('adapter_????????'),
        ];
    }
}
