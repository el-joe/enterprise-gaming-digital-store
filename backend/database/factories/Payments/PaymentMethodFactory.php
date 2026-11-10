<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_gateway_id' => PaymentGateway::factory(),
            'code' => fake()->unique()->lexify('????????????????'),
            'label' => fake()->words(2, true),
            'supports_refund' => false,
            'is_active' => true,
        ];
    }
}
