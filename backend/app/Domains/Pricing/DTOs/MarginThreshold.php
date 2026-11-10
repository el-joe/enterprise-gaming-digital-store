<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;

final readonly class MarginThreshold
{
    public function __construct(
        public string $percent,
        public string $fixedBase,
        public PricingRuleBelowMarginPolicy $policy,
        public ?int $ruleId,
    ) {}
}
