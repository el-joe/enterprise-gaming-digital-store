<?php

namespace Database\Factories\Suppliers;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierCredential>
 */
class SupplierCredentialFactory extends Factory
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
            'mode' => EnvironmentMode::Sandbox,
            'credentials' => ['api_key' => fake()->sha256()],
        ];
    }
}
