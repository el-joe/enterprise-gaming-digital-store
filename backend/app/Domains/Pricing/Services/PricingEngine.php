<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Exceptions\ExchangeRateMissing;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Pricing\DTOs\PriceQuote;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Pricing\Enums\PriceSource;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Shared\ValueObjects\Percentage;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use LogicException;

/**
 * Computes the price of one unit of a variant following PROJECT_ARCHITECTURE.md section 11.5.
 */
class PricingEngine
{
    public function __construct(
        private PricingDataLoader $loader,
        private CostResolver $costs,
        private ExplicitPriceResolver $explicitPrices,
        private PricingRuleResolver $rules,
        private DiscountCalculator $discounts,
        private MarginGuard $guard,
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $rates,
    ) {}

    public function quote(ProductVariant $variant, PricingContext $context): PriceQuote
    {
        return $this->quoteMany(new EloquentCollection([$variant]), $context)->first();
    }

    /**
     * @param  Collection<int, ProductVariant>  $variants
     * @return Collection<int, PriceQuote> keyed by variant id
     */
    public function quoteMany(Collection $variants, PricingContext $context): Collection
    {
        $data = $this->loader->load($variants);

        return $variants->mapWithKeys(fn (ProductVariant $variant): array => [$variant->id => $this->quoteWith($variant, $context, $data)]);
    }

    public function quoteWith(ProductVariant $variant, PricingContext $context, PricingDataset $data): PriceQuote
    {
        try {
            $quote = $this->price($variant, $context, $data);
        } catch (ExchangeRateMissing) {
            return PriceQuote::blocked('rate_missing');
        }

        return $context->forCheckout && $quote->rate?->isStale === true
            ? PriceQuote::blocked('rate_stale', $quote->cost, $quote->floor)
            : $quote;
    }

    private function price(ProductVariant $variant, PricingContext $context, PricingDataset $data): PriceQuote
    {
        if ($variant->is_custom_amount) {
            return PriceQuote::blocked('custom_amount_unsupported');
        }

        $base = $this->rates->baseCurrency();
        $currency = strtoupper($context->currency ?? $base);
        $country = $context->country === null ? null : strtoupper($context->country);

        $cost = $this->costs->resolve($variant, $data);
        $explicit = $this->explicitPrices->resolve($variant, $country, $currency);

        $conversion = $this->involvesConversion($currency, $cost->costCurrency, $context->paymentCurrency);

        if ($explicit !== null) {
            return $this->priceExplicit($explicit, $variant, $country, $currency, $context, $data, $cost->baseCost, $conversion);
        }

        if ($cost->baseCost === null) {
            return PriceQuote::blocked((string) $cost->unavailableReason);
        }

        return $this->priceByMarkup($variant, $country, $currency, $context, $data, $cost->baseCost, $conversion);
    }

    /**
     * The margin floor for a variant, or null when no price can reach it or the cost is unknown.
     *
     * @return array{floor: ?Money, cost_known: bool}
     */
    public function floorFor(ProductVariant $variant, ?string $country, string $currency): array
    {
        $data = $this->loader->load(new EloquentCollection([$variant]));
        $cost = $this->costs->resolve($variant, $data);

        if ($cost->baseCost === null) {
            return ['floor' => null, 'cost_known' => false];
        }

        $threshold = $this->rules->marginThreshold($variant, $data, $country, $currency, $cost->baseCost);

        return [
            'floor' => $this->guard->floor($cost->baseCost, $threshold, $data->feeRules, $country, $currency, $this->involvesConversion($currency, $cost->costCurrency)),
            'cost_known' => true,
        ];
    }

    /**
     * The margin floor at a given base cost, for a supplier other than the primary mapping
     * (selection and fallback, architecture 7.3 and 7.4). Null floor: no price can reach the
     * margin. `allowsBelowMargin` reflects the variant's `allow` policy.
     *
     * @return array{floor: ?Money, allows_below_margin: bool}
     */
    public function floorAtCost(ProductVariant $variant, Money $baseCost, ?string $costCurrency, ?string $country, string $currency, ?string $paymentCurrency = null): array
    {
        $data = $this->loader->load(new EloquentCollection([$variant]));
        $threshold = $this->rules->marginThreshold($variant, $data, $country, $currency, $baseCost);

        return [
            'floor' => $this->guard->floor($baseCost, $threshold, $data->feeRules, $country, $currency, $this->involvesConversion($currency, $costCurrency, $paymentCurrency)),
            'allows_below_margin' => $threshold->policy === PricingRuleBelowMarginPolicy::Allow,
        ];
    }

    private function priceByMarkup(
        ProductVariant $variant,
        ?string $country,
        string $currency,
        PricingContext $context,
        PricingDataset $data,
        Money $cost,
        bool $conversion,
    ): PriceQuote {
        $rule = $this->rules->markupRule($variant, $data, $country, $currency, $cost);

        $markupPrice = $cost;

        if ($rule?->fixed_markup !== null) {
            $markupPrice = $markupPrice->plus(Money::of($rule->fixed_markup, $cost->currency));
        }

        if ($rule?->percent_markup !== null) {
            $markupPrice = $markupPrice->plus(Percentage::of($rule->percent_markup)->applyTo($cost));
        }

        [$price, $promotionIds] = $this->discounts->applyPromotions($markupPrice, $variant, $data);
        $promotionDiscount = $markupPrice->minus($price);

        $threshold = $this->rules->marginThreshold($variant, $data, $country, $currency, $cost);
        $floor = $this->guard->floor($cost, $threshold, $data->feeRules, $country, $currency, $conversion);

        if ($floor === null) {
            return PriceQuote::blocked('margin_unattainable', $cost);
        }

        $raised = false;

        if ($price->compareTo($floor) === -1) {
            if ($threshold->policy === PricingRuleBelowMarginPolicy::Block) {
                return PriceQuote::blocked('below_margin', $cost, $floor);
            }

            if ($threshold->policy === PricingRuleBelowMarginPolicy::Floor) {
                $price = $floor;
                $raised = true;
            }
        }

        [$price, $couponDiscount] = $this->applyCoupon($price, $floor, $context);

        $converted = $this->converter->toDisplay($price, $currency);

        return new PriceQuote(
            purchasable: true,
            baseAmount: $price,
            amount: $converted->money,
            source: $rule === null ? PriceSource::CostOnly : PriceSource::Markup,
            cost: $cost,
            floor: $floor,
            rate: $converted->rate,
            pricingRuleId: $rule?->id,
            marginRuleId: $threshold->ruleId,
            marginPolicy: $threshold->policy,
            raisedToFloor: $raised,
            promotionIds: $promotionIds,
            promotionDiscount: $promotionDiscount,
            couponDiscount: $couponDiscount,
            markupBase: $markupPrice,
        );
    }

    /**
     * Explicit prices skip markup, the margin guard and currency markup. Discounts still apply,
     * but may not take the price below the margin floor unless a coupon explicitly permits it.
     */
    private function priceExplicit(
        VariantPrice $explicit,
        ProductVariant $variant,
        ?string $country,
        string $currency,
        PricingContext $context,
        PricingDataset $data,
        ?Money $cost,
        bool $conversion,
    ): PriceQuote {
        $listed = Money::of($explicit->price, $explicit->currency);
        $listedRate = $this->rates->resolve($explicit->currency);
        $base = $this->converter->toBase($listed, $listedRate, withoutMarkup: true);

        $floor = null;

        if ($cost !== null) {
            $threshold = $this->rules->marginThreshold($variant, $data, $country, $currency, $cost);
            $floor = $this->guard->floor($cost, $threshold, $data->feeRules, $country, $currency, $conversion);
        }

        [$price, $promotionIds] = $this->discounts->applyPromotions($base, $variant, $data);

        if ($floor !== null && $price->compareTo($floor) === -1) {
            $price = $base->compareTo($floor) === -1 ? $base : $floor;
            $promotionIds = $price->equals($base) ? [] : $promotionIds;
        }

        $promotionDiscount = $base->minus($price);

        [$price, $couponDiscount] = $this->applyCoupon($price, $floor, $context);

        $rate = $this->rates->resolve($currency);

        $amount = $price->equals($base) && $explicit->currency === $currency
            ? $this->converter->roundForCurrency($listed)
            : $this->converter->convert($price, $rate, withoutMarkup: true)->money;

        return new PriceQuote(
            purchasable: true,
            baseAmount: $price,
            amount: $amount,
            source: PriceSource::Explicit,
            cost: $cost,
            floor: $floor,
            rate: $rate,
            promotionIds: $promotionIds,
            promotionDiscount: $promotionDiscount,
            couponDiscount: $couponDiscount,
        );
    }

    /**
     * The customer-facing amount of one unit whose base price was lowered by a cart-level
     * discount, converted the same way the quote itself was: explicit prices without currency
     * markup, markup prices with it. An undiscounted unit keeps the quoted amount exactly.
     */
    public function displayAmountFor(PriceQuote $quote, Money $netBase): Money
    {
        if ($quote->baseAmount === null || $quote->amount === null || $quote->rate === null) {
            throw new LogicException('Only a purchasable quote can be repriced.');
        }

        if ($netBase->equals($quote->baseAmount)) {
            return $quote->amount;
        }

        return $this->converter->convert($netBase, $quote->rate, withoutMarkup: $quote->source === PriceSource::Explicit)->money;
    }

    /**
     * @return array{0: Money, 1: Money|null}
     */
    private function applyCoupon(Money $price, ?Money $floor, PricingContext $context): array
    {
        if ($context->coupon === null) {
            return [$price, null];
        }

        $discounted = $price->minus($this->discounts->couponDiscount($price, $context->coupon));

        if (! $context->coupon->allowBelowMargin && $floor !== null && $discounted->compareTo($floor) === -1) {
            $discounted = $price->compareTo($floor) === -1 ? $price : $floor;
        }

        return [$discounted, $price->minus($discounted)];
    }

    /**
     * Conversion cost applies when the customer sees or pays a non-base currency, or the supplier
     * bills in one. The payment currency counts even when the display currency is the base
     * (for example an order shown in USD but charged in EGP).
     */
    private function involvesConversion(string $currency, ?string $costCurrency, ?string $paymentCurrency = null): bool
    {
        $base = $this->rates->baseCurrency();

        return $currency !== $base
            || ($costCurrency !== null && $costCurrency !== $base)
            || ($paymentCurrency !== null && strtoupper($paymentCurrency) !== $base);
    }
}
