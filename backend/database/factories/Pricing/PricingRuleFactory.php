<?php

namespace Database\Factories\Pricing;

use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingRule>
 */
class PricingRuleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'scope' => PricingRuleScope::Global,
            'kind' => PricingRuleKind::Markup,
        ];
    }
}
