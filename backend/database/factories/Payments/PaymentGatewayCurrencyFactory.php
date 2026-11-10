<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Payments\Models\PaymentGatewayCurrency;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentGatewayCurrency>
 */
class PaymentGatewayCurrencyFactory extends Factory
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
            'currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'minor_exponent' => 1,
            'is_enabled' => true,
        ];
    }
}
