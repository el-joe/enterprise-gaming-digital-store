<?php

namespace Database\Factories\Payments;

use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentGateway;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_gateway_id' => PaymentGateway::factory(),
            'attempt_no' => 1,
            'status' => PaymentStatus::Created,
            'base_amount' => '10.000000',
            'base_currency' => fn (): string => CurrencyFactory::ensure('USD'),
            'rate' => '1.000000000000',
            'amount' => '10.000000',
            'currency' => fn (): string => CurrencyFactory::ensure('USD'),
        ];
    }
}
