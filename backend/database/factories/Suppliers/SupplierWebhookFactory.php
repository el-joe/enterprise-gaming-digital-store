<?php

namespace Database\Factories\Suppliers;

use App\Domains\Suppliers\Enums\WebhookStatus;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierWebhook>
 */
class SupplierWebhookFactory extends Factory
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
            'provider_event_id' => fake()->unique()->lexify('????????????????'),
            'payload' => fake()->sha256(),
            'status' => WebhookStatus::Received,
        ];
    }
}
