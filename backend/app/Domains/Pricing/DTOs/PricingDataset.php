<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Collection;

/**
 * Everything the engine needs beyond the variant itself, loaded once per batch so that pricing
 * many variants costs the same number of queries as pricing one.
 */
final readonly class PricingDataset
{
    /**
     * @param  Collection<int, PricingRule>  $rules
     * @param  Collection<int, Promotion>  $promotions
     * @param  Collection<int, PaymentGatewayFeeRule>  $feeRules
     * @param  array<string, Money>  $latestCosts  keyed by "supplierProductId|denomination"
     */
    public function __construct(
        public Collection $rules,
        public Collection $promotions,
        public Collection $feeRules,
        public array $latestCosts,
    ) {}
}
