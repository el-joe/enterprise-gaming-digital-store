<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentDispute>
 */
class PaymentDisputeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_id' => Payment::factory(),
            'provider_dispute_id' => fake()->unique()->lexify('????????????'),
            'status' => PaymentDisputeStatus::Open,
            'amount' => '10.000000',
            'currency' => 'USD',
        ];
    }
}
