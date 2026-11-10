<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentWebhook;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentWebhook>
 */
class PaymentWebhookFactory extends Factory
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
            'provider_event_id' => fake()->unique()->lexify('????????????????'),
            'event_type' => fake()->lexify('????????????'),
            'signature_valid' => false,
            'payload' => fake()->sha256(),
            'status' => WebhookStatus::Received,
        ];
    }
}
