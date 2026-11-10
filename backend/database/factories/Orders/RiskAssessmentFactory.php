<?php

namespace Database\Factories\Orders;

use App\Domains\Orders\Enums\RiskAssessmentDecision;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\RiskAssessment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RiskAssessment>
 */
class RiskAssessmentFactory extends Factory
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
            'score' => 10,
            'decision' => RiskAssessmentDecision::Pass,
            'signals' => ['key' => 'value'],
        ];
    }
}
