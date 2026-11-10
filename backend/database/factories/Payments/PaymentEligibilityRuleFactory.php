<?php

namespace Database\Factories\Payments;

use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentEligibilityRule>
 */
class PaymentEligibilityRuleFactory extends Factory
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
            'effect' => PaymentEligibilityRuleEffect::Allow,
        ];
    }
}
