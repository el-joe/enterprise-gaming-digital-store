<?php

namespace App\Domains\Pricing\DTOs;

use App\Domains\Currency\DTOs\ResolvedRate;
use App\Domains\Pricing\Enums\PriceSource;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Shared\ValueObjects\Money;

/**
 * The result of pricing one unit of a variant. When not purchasable, `blockReason` says why.
 */
final readonly class PriceQuote
{
    /**
     * @param  list<int>  $promotionIds
     */
    public function __construct(
        public bool $purchasable,
        public ?string $blockReason = null,
        public ?Money $baseAmount = null,
        public ?Money $amount = null,
        public ?PriceSource $source = null,
        public ?Money $cost = null,
        public ?Money $floor = null,
        public ?ResolvedRate $rate = null,
        public ?int $pricingRuleId = null,
        public ?int $marginRuleId = null,
        public ?PricingRuleBelowMarginPolicy $marginPolicy = null,
        public bool $raisedToFloor = false,
        public array $promotionIds = [],
        public ?Money $promotionDiscount = null,
        public ?Money $couponDiscount = null,
        public ?Money $markupBase = null,
    ) {}

    public static function blocked(string $reason, ?Money $cost = null, ?Money $floor = null): self
    {
        return new self(purchasable: false, blockReason: $reason, cost: $cost, floor: $floor);
    }

    /**
     * @return array<string, mixed>
     */
    public function breakdown(): array
    {
        return [
            'purchasable' => $this->purchasable,
            'block_reason' => $this->blockReason,
            'source' => $this->source?->value,
            'base_amount' => $this->baseAmount?->amount,
            'amount' => $this->amount?->amount,
            'currency' => $this->amount?->currency,
            'cost_base' => $this->cost?->amount,
            'floor_base' => $this->floor?->amount,
            'markup_price_base' => $this->markupBase?->amount,
            'promotion_discount_base' => $this->promotionDiscount?->amount,
            'coupon_discount_base' => $this->couponDiscount?->amount,
            'promotion_ids' => $this->promotionIds,
            'pricing_rule_id' => $this->pricingRuleId,
            'margin_rule_id' => $this->marginRuleId,
            'margin_policy' => $this->marginPolicy?->value,
            'raised_to_floor' => $this->raisedToFloor,
            'rate' => $this->rate?->rate->value,
            'rate_source' => $this->rate?->source,
            'rate_is_stale' => $this->rate?->isStale ?? false,
            'exchange_rate_history_id' => $this->rate?->historyId,
        ];
    }
}
