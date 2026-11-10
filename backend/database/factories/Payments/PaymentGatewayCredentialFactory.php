<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Shared\Enums\EnvironmentMode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGatewayCredential>
 */
class PaymentGatewayCredentialFactory extends Factory
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
            'mode' => EnvironmentMode::Sandbox,
            'credentials' => ['api_key' => fake()->sha256()],
        ];
    }
}
