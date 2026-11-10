<?php

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Models\ExchangeRateHistory;
use App\Domains\Payments\Models\PaymentGatewayFeeRule;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Pricing\DTOs\PriceQuote;
use App\Domains\Pricing\DTOs\PricingContext;
use App\Domains\Pricing\Enums\PriceSource;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Pricing\Services\PricingEngine;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Enums\PromotionTargetTargetType;
use App\Domains\Promotions\Enums\PromotionType;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Promotions\Models\PromotionTarget;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierProductMappingRole;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use Database\Factories\Currency\CurrencyFactory;

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    DB::table('countries')->insertOrIgnore([
        ['code' => 'EG', 'name' => 'Egypt', 'is_active' => true],
        ['code' => 'DE', 'name' => 'Germany', 'is_active' => true],
    ]);
});

function quote(ProductVariant $variant, ?string $country = null, ?string $currency = null, ?CouponTerms $coupon = null, bool $forCheckout = false): PriceQuote
{
    return app(PricingEngine::class)->quote($variant->fresh(), new PricingContext($country, $currency, $coupon, $forCheckout));
}

/**
 * @param  array<string, mixed>  $attributes
 */
function markupRule(string $percent, array $attributes = []): PricingRule
{
    return PricingRule::factory()->create(['percent_markup' => $percent, 'is_active' => true, ...$attributes]);
}

function explicitPrice(ProductVariant $variant, string $price, string $currency = 'USD', string $country = '*', array $attributes = []): VariantPrice
{
    return VariantPrice::factory()->create(['product_variant_id' => $variant->id, 'price' => $price, 'currency' => $currency, 'country_code' => $country, ...$attributes]);
}

function promotion(PromotionType $type, string $value, ProductVariant $variant, array $attributes = []): Promotion
{
    $promotion = Promotion::factory()->create(['type' => $type, 'value' => $value, 'starts_at' => now()->subDay(), 'is_active' => true, ...$attributes]);

    PromotionTarget::factory()->create(['promotion_id' => $promotion->id, 'target_type' => PromotionTargetTargetType::Variant, 'target_id' => $variant->id]);

    return $promotion;
}

describe('step 1: cost', function (): void {
    it('uses the manual cost when there is no supplier mapping', function (): void {
        expect(quote(variantCosting('10'))->cost->amount)->toBe('10.000000');
    });

    it('uses the latest supplier cost of the primary mapping over the manual cost', function (): void {
        $variant = variantCosting('10');
        $mapping = SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'supplier_unit_price' => '25.000000', 'is_active' => true]);
        SupplierProductPrice::factory()->create(['supplier_product_id' => $mapping->supplier_product_id, 'denomination' => '25', 'cost' => '20', 'cost_currency' => 'USD', 'captured_at' => now()->subDay()]);
        SupplierProductPrice::factory()->create(['supplier_product_id' => $mapping->supplier_product_id, 'denomination' => '25', 'cost' => '22', 'cost_currency' => 'USD', 'captured_at' => now()]);

        expect(quote($variant)->cost->amount)->toBe('22.000000');
    });

    it('prefers a mapping cost override', function (): void {
        $variant = variantCosting('10');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'override_cost' => '15', 'override_cost_currency' => 'USD', 'is_active' => true]);

        expect(quote($variant)->cost->amount)->toBe('15.000000');
    });

    it('ignores secondary and inactive mappings', function (): void {
        $variant = variantCosting('10');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'override_cost' => '15', 'override_cost_currency' => 'USD', 'is_active' => true, 'role' => SupplierProductMappingRole::Secondary]);
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'override_cost' => '17', 'override_cost_currency' => 'USD', 'is_active' => false]);

        expect(quote($variant)->cost->amount)->toBe('10.000000');
    });

    it('converts a foreign cost at the rate without markup', function (): void {
        CurrencyFactory::ensure('EGP');
        Currency::query()->where('code', 'EGP')->update(['markup_pct' => '10']);
        givenRate('EGP', '50', ['effective_rate' => '55']);
        $variant = variantCosting('10');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'override_cost' => '500', 'override_cost_currency' => 'EGP', 'is_active' => true]);

        expect(quote($variant)->cost->amount)->toBe('10.000000');
    });

    it('is not purchasable with no cost', function (): void {
        $quote = quote(ProductVariant::factory()->create());

        expect($quote->purchasable)->toBeFalse()->and($quote->blockReason)->toBe('cost_unavailable');
    });

    it('is not purchasable when the cost currency rate is stale or missing', function (): void {
        CurrencyFactory::ensure('EGP');
        $variant = variantCosting('10');
        SupplierProductMapping::factory()->create(['product_variant_id' => $variant->id, 'override_cost' => '500', 'override_cost_currency' => 'EGP', 'is_active' => true]);

        expect(quote($variant)->blockReason)->toBe('cost_rate_missing');

        givenRate('EGP', '50', ['fetched_at' => now()->subDays(3)]);

        expect(quote($variant)->blockReason)->toBe('cost_rate_stale');
    });

    it('does not price custom amount variants', function (): void {
        expect(quote(variantCosting('10', ['is_custom_amount' => true]))->blockReason)->toBe('custom_amount_unsupported');
    });
});

describe('step 2: explicit price', function (): void {
    it('beats any markup rule', function (): void {
        $variant = variantCosting('10');
        markupRule('50');
        explicitPrice($variant, '17');

        $quote = quote($variant);

        expect($quote->baseAmount->amount)->toBe('17.000000')->and($quote->source)->toBe(PriceSource::Explicit)->and($quote->pricingRuleId)->toBeNull();
    });

    it('country and currency beats currency beats country', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50');
        $variant = variantCosting('1');
        explicitPrice($variant, '600', 'EGP', 'EG');
        explicitPrice($variant, '700', 'EGP', '*');
        explicitPrice($variant, '20', 'USD', 'EG');

        expect(quote($variant, 'EG', 'EGP')->amount->amount)->toBe('600.000000')
            ->and(quote($variant, 'DE', 'EGP')->amount->amount)->toBe('700.000000');

        VariantPrice::query()->whereIn('price', ['600', '700'])->delete();

        expect(quote($variant, 'EG', 'EGP')->amount->amount)->toBe('1000.000000');
    });

    it('does not apply currency markup to a converted explicit price', function (): void {
        CurrencyFactory::ensure('EGP');
        Currency::query()->where('code', 'EGP')->update(['markup_pct' => '10']);
        givenRate('EGP', '50', ['effective_rate' => '55']);
        $variant = variantCosting('1');
        explicitPrice($variant, '20', 'USD', 'EG');

        expect(quote($variant, 'EG', 'EGP')->amount->amount)->toBe('1000.000000');
    });

    it('charges exactly the explicit amount, rounded only to precision', function (): void {
        Currency::factory()->create(['code' => 'CHF', 'rounding_rule' => ['mode' => 'step', 'step' => '0.05']]);
        givenRate('CHF', '0.9');
        $variant = variantCosting('1');
        explicitPrice($variant, '19.99', 'CHF');

        expect(quote($variant, null, 'CHF')->amount->amount)->toBe('19.990000');
    });

    it('honours the validity window', function (): void {
        $variant = variantCosting('10');
        markupRule('50');
        explicitPrice($variant, '17', attributes: ['starts_at' => now()->addDay()]);
        explicitPrice($variant, '18', attributes: ['ends_at' => now()->subDay()]);

        expect(quote($variant)->source)->toBe(PriceSource::Markup);
    });

    it('skips the margin guard: a deliberate explicit price below cost is sold as set', function (): void {
        $variant = variantCosting('10');
        explicitPrice($variant, '5');

        $quote = quote($variant);

        expect($quote->purchasable)->toBeTrue()->and($quote->baseAmount->amount)->toBe('5.000000')->and($quote->floor->amount)->toBe('10.000000');
    });

    it('works without a known cost', function (): void {
        $variant = ProductVariant::factory()->create();
        explicitPrice($variant, '5');

        expect(quote($variant)->purchasable)->toBeTrue();
    });
});

describe('step 3: markup rule precedence', function (): void {
    beforeEach(function (): void {
        $this->platform = GamingPlatform::factory()->create();
        $this->category = Category::factory()->create();
        $this->product = Product::factory()->create(['gaming_platform_id' => $this->platform->id, 'primary_category_id' => $this->category->id]);
        $this->variant = variantCosting('100', ['product_id' => $this->product->id]);

        $this->scopes = fn (): array => [
            [PricingRuleScope::Global, null, '1'],
            [PricingRuleScope::Category, $this->category->id, '2'],
            [PricingRuleScope::Platform, $this->platform->id, '3'],
            [PricingRuleScope::Product, $this->product->id, '4'],
            [PricingRuleScope::Variant, $this->variant->id, '5'],
        ];
    });

    it('takes the most specific scope, one level at a time', function (): void {
        foreach (($this->scopes)() as [$scope, $id, $percent]) {
            markupRule($percent, ['scope' => $scope, 'scope_id' => $id]);

            expect(quote($this->variant)->baseAmount->amount)->toBe(bcadd('100', $percent, 6));
        }
    });

    it('does not stack rules', function (): void {
        markupRule('10');
        markupRule('10', ['scope' => PricingRuleScope::Product, 'scope_id' => $this->product->id]);

        expect(quote($this->variant)->baseAmount->amount)->toBe('110.000000');
    });

    it('matches a rule on a secondary category', function (): void {
        $secondary = Category::factory()->create();
        $this->product->categories()->attach($secondary);
        markupRule('7', ['scope' => PricingRuleScope::Category, 'scope_id' => $secondary->id]);

        expect(quote($this->variant)->baseAmount->amount)->toBe('107.000000');
    });

    it('applies fixed and percent together', function (): void {
        markupRule('10', ['fixed_markup' => '2.5']);

        expect(quote($this->variant)->baseAmount->amount)->toBe('112.500000');
    });

    it('within a scope prefers country and currency, then country, then currency, then neither', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50');

        markupRule('1');
        expect(quote($this->variant, 'EG', 'EGP')->baseAmount->amount)->toBe('101.000000');

        markupRule('2', ['currency' => 'EGP']);
        expect(quote($this->variant, 'EG', 'EGP')->baseAmount->amount)->toBe('102.000000');

        markupRule('3', ['country_code' => 'EG']);
        expect(quote($this->variant, 'EG', 'EGP')->baseAmount->amount)->toBe('103.000000');

        markupRule('4', ['country_code' => 'EG', 'currency' => 'EGP']);
        expect(quote($this->variant, 'EG', 'EGP')->baseAmount->amount)->toBe('104.000000');
    });

    it('ignores rules for another country or currency', function (): void {
        markupRule('1');
        markupRule('9', ['country_code' => 'DE']);

        expect(quote($this->variant, 'EG')->baseAmount->amount)->toBe('101.000000');
    });

    it('breaks ties by lower priority number, then newest', function (): void {
        markupRule('5', ['priority' => 50]);
        markupRule('6', ['priority' => 10]);
        expect(quote($this->variant)->baseAmount->amount)->toBe('106.000000');

        markupRule('7', ['priority' => 10]);
        expect(quote($this->variant)->baseAmount->amount)->toBe('107.000000');
    });

    it('ignores inactive and out-of-window rules', function (): void {
        markupRule('1');
        markupRule('8', ['scope' => PricingRuleScope::Variant, 'scope_id' => $this->variant->id, 'is_active' => false]);
        markupRule('9', ['scope' => PricingRuleScope::Variant, 'scope_id' => $this->variant->id, 'starts_at' => now()->addDay()]);
        markupRule('9', ['scope' => PricingRuleScope::Variant, 'scope_id' => $this->variant->id, 'ends_at' => now()->subDay()]);

        expect(quote($this->variant)->baseAmount->amount)->toBe('101.000000');
    });

    it('does not let a margin-only rule shadow a broader markup', function (): void {
        markupRule('10');
        PricingRule::factory()->create(['scope' => PricingRuleScope::Variant, 'scope_id' => $this->variant->id, 'min_margin_pct' => '1', 'is_active' => true]);

        expect(quote($this->variant)->baseAmount->amount)->toBe('110.000000');
    });

    it('reports cost only when no rule matches', function (): void {
        expect(quote($this->variant)->source)->toBe(PriceSource::CostOnly);
    });
});

describe('step 4: dynamic rules', function (): void {
    function dynamicRule(string $percent, array $conditions, array $attributes = []): PricingRule
    {
        return markupRule($percent, ['kind' => PricingRuleKind::Dynamic, 'conditions' => $conditions, ...$attributes]);
    }

    it('applies when conditions hold, at its own scope position', function (): void {
        $variant = variantCosting('100', ['availability' => ProductVariantAvailability::Limited]);
        markupRule('1');
        dynamicRule('20', ['availability' => ['limited']], ['scope' => PricingRuleScope::Variant, 'scope_id' => $variant->id]);

        expect(quote($variant)->baseAmount->amount)->toBe('120.000000');
    });

    it('is skipped when conditions fail, falling through to the next rule', function (): void {
        $variant = variantCosting('100', ['availability' => ProductVariantAvailability::InStock]);
        markupRule('1');
        dynamicRule('20', ['availability' => ['limited']], ['scope' => PricingRuleScope::Variant, 'scope_id' => $variant->id]);

        expect(quote($variant)->baseAmount->amount)->toBe('101.000000');
    });

    it('evaluates a supplier cost band', function (string $cost, string $expected): void {
        $variant = variantCosting($cost);
        markupRule('1');
        dynamicRule('20', ['cost_min' => '50', 'cost_max' => '100'], ['scope' => PricingRuleScope::Variant, 'scope_id' => $variant->id]);

        expect(quote($variant)->baseAmount->amount)->toBe($expected);
    })->with([
        'below band' => ['49', '49.490000'],
        'at bottom' => ['50', '60.000000'],
        'at top' => ['100', '120.000000'],
        'above band' => ['101', '102.010000'],
    ]);

    it('fails closed on an unknown condition or none', function (array $conditions): void {
        $variant = variantCosting('100');
        markupRule('1');
        dynamicRule('20', $conditions, ['scope' => PricingRuleScope::Variant, 'scope_id' => $variant->id]);

        expect(quote($variant)->baseAmount->amount)->toBe('101.000000');
    })->with([
        'unknown key' => [['moon_phase' => 'full']],
        'empty' => [[]],
    ]);
});

describe('step 5: promotion', function (): void {
    it('applies the best single promotion', function (): void {
        $variant = variantCosting('50');
        markupRule('100');
        promotion(PromotionType::Percent, '10', $variant);
        promotion(PromotionType::Percent, '20', $variant);
        promotion(PromotionType::Fixed, '5', $variant);

        expect(quote($variant)->baseAmount->amount)->toBe('80.000000');
    });

    it('stacks only promotions marked stackable', function (): void {
        $variant = variantCosting('50');
        markupRule('100');
        promotion(PromotionType::Percent, '20', $variant, ['stackable' => true]);
        promotion(PromotionType::Percent, '10', $variant, ['stackable' => true]);
        promotion(PromotionType::Percent, '5', $variant);

        expect(quote($variant)->baseAmount->amount)->toBe('72.000000');
    });

    it('does not combine a best non-stackable promotion with stackable ones', function (): void {
        $variant = variantCosting('50');
        markupRule('100');
        promotion(PromotionType::Percent, '30', $variant);
        promotion(PromotionType::Percent, '10', $variant, ['stackable' => true]);

        expect(quote($variant)->baseAmount->amount)->toBe('70.000000');
    });

    it('supports fixed price promotions and targets by category and platform', function (): void {
        $variant = variantCosting('50');
        $product = $variant->product;
        markupRule('100');
        $promotion = Promotion::factory()->create(['type' => PromotionType::FixedPrice, 'value' => '60', 'starts_at' => now()->subDay(), 'is_active' => true]);
        PromotionTarget::factory()->create(['promotion_id' => $promotion->id, 'target_type' => PromotionTargetTargetType::Platform, 'target_id' => $product->gaming_platform_id]);

        expect(quote($variant)->baseAmount->amount)->toBe('60.000000');
    });

    it('does not apply expired or future promotions', function (): void {
        $variant = variantCosting('50');
        markupRule('100');
        promotion(PromotionType::Percent, '50', $variant, ['ends_at' => now()->subHour()]);
        promotion(PromotionType::Percent, '50', $variant, ['starts_at' => now()->addHour()]);

        expect(quote($variant)->promotionIds)->toBe([]);
    });

    it('applies to explicit prices but never below the margin floor', function (): void {
        $variant = variantCosting('10');
        explicitPrice($variant, '12');
        promotion(PromotionType::Percent, '50', $variant);

        expect(quote($variant)->baseAmount->amount)->toBe('10.000000');
    });
});

describe('step 6: coupon', function (): void {
    function percentCoupon(string $value, bool $belowMargin = false, ?string $max = null): CouponTerms
    {
        return new CouponTerms(CouponType::Percent, $value, $max === null ? null : Money::of($max, 'USD'), $belowMargin);
    }

    it('applies after the promotion', function (): void {
        $variant = variantCosting('10');
        markupRule('100');
        promotion(PromotionType::Percent, '10', $variant);

        $quote = quote($variant, coupon: percentCoupon('50'));

        expect($quote->baseAmount->amount)->toBe('10.000000')
            ->and($quote->couponDiscount->amount)->toBe('8.000000')
            ->and($quote->promotionDiscount->amount)->toBe('2.000000');
    });

    it('caps at the maximum discount and supports fixed coupons', function (): void {
        $variant = variantCosting('10');
        markupRule('100');

        expect(quote($variant, coupon: percentCoupon('20', max: '1'))->baseAmount->amount)->toBe('19.000000')
            ->and(quote($variant, coupon: new CouponTerms(CouponType::Fixed, '3'))->baseAmount->amount)->toBe('17.000000');
    });

    it('stops at the margin floor unless the coupon allows going below', function (): void {
        $variant = variantCosting('10');
        markupRule('100');

        expect(quote($variant, coupon: percentCoupon('90'))->baseAmount->amount)->toBe('10.000000')
            ->and(quote($variant, coupon: percentCoupon('90', belowMargin: true))->baseAmount->amount)->toBe('2.000000');
    });

    it('stops at the floor on an explicit price too', function (): void {
        $variant = variantCosting('10');
        explicitPrice($variant, '12');

        expect(quote($variant, coupon: percentCoupon('90'))->baseAmount->amount)->toBe('10.000000');
    });
});

describe('step 7: margin guard', function (): void {
    function marginRule(string $percent, ?PricingRuleBelowMarginPolicy $policy, array $attributes = []): PricingRule
    {
        return PricingRule::factory()->create(['min_margin_pct' => $percent, 'below_margin_policy' => $policy, 'is_active' => true, ...$attributes]);
    }

    it('floor raises the price to the floor', function (): void {
        $variant = variantCosting('100');
        markupRule('1');
        marginRule('20', PricingRuleBelowMarginPolicy::Floor);

        $quote = quote($variant);

        expect($quote->baseAmount->amount)->toBe('125.000000')->and($quote->raisedToFloor)->toBeTrue()->and($quote->purchasable)->toBeTrue();
    });

    it('block makes it not purchasable', function (): void {
        $variant = variantCosting('100');
        markupRule('1');
        marginRule('20', PricingRuleBelowMarginPolicy::Block);

        $quote = quote($variant);

        expect($quote->purchasable)->toBeFalse()->and($quote->blockReason)->toBe('below_margin')->and($quote->floor->amount)->toBe('125.000000');
    });

    it('allow sells below the floor', function (): void {
        $variant = variantCosting('100');
        markupRule('1');
        marginRule('20', PricingRuleBelowMarginPolicy::Allow);

        $quote = quote($variant);

        expect($quote->purchasable)->toBeTrue()->and($quote->baseAmount->amount)->toBe('101.000000')->and($quote->raisedToFloor)->toBeFalse();
    });

    it('defaults to block at break-even when nothing is configured', function (): void {
        $variant = variantCosting('100');

        expect(quote($variant)->baseAmount->amount)->toBe('100.000000')->and(quote($variant)->purchasable)->toBeTrue();

        PaymentGatewayFeeRule::factory()->create(['percent_fee' => '2', 'fixed_fee' => '0', 'is_active' => true]);

        expect(quote($variant)->blockReason)->toBe('below_margin');
    });

    it('allows exactly the floor', function (): void {
        $variant = variantCosting('100');
        markupRule('25');
        marginRule('20', PricingRuleBelowMarginPolicy::Block);

        expect(quote($variant)->purchasable)->toBeTrue();
    });

    it('accounts for gateway fees and conversion cost, using the most expensive gateway', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50');
        app(SettingsService::class)->set('pricing', 'conversion_cost_pct', '1', SettingType::Decimal);
        PaymentGatewayFeeRule::factory()->create(['percent_fee' => '2', 'fixed_fee' => '0.30', 'is_active' => true]);
        PaymentGatewayFeeRule::factory()->create(['percent_fee' => '4', 'fixed_fee' => '0', 'is_active' => true]);
        $variant = variantCosting('100');
        marginRule('10', PricingRuleBelowMarginPolicy::Floor);

        // Worst gateway 4% + margin 10%: 100 / 0.86, and in EGP also 1% conversion: 100 / 0.85.
        expect(quote($variant, null, 'EGP')->floor->amount)->toBe('117.647059')
            ->and(quote($variant)->floor->amount)->toBe('116.279070');
    });

    it('requires both a percent and a fixed margin when both are set', function (): void {
        $variant = variantCosting('100');
        marginRule('1', PricingRuleBelowMarginPolicy::Floor, ['min_margin_fixed' => '30']);

        expect(quote($variant)->floor->amount)->toBe('130.000000');
    });

    it('uses the most specific margin, ignoring platform scope, and inherits the policy', function (): void {
        $variant = variantCosting('100');
        marginRule('50', PricingRuleBelowMarginPolicy::Floor);
        marginRule('20', null, ['scope' => PricingRuleScope::Product, 'scope_id' => $variant->product_id]);

        $quote = quote($variant);

        expect($quote->floor->amount)->toBe('125.000000')->and($quote->marginPolicy)->toBe(PricingRuleBelowMarginPolicy::Floor);
    });

    it('blocks when no price can reach the margin', function (): void {
        $variant = variantCosting('100');
        marginRule('100', PricingRuleBelowMarginPolicy::Floor);

        expect(quote($variant)->blockReason)->toBe('margin_unattainable');
    });
});

describe('step 8: conversion', function (): void {
    it('converts at the effective rate with markup and applies currency rounding', function (): void {
        Currency::factory()->create(['code' => 'CHF', 'rounding_rule' => ['mode' => 'step', 'step' => '0.05'], 'markup_pct' => '2']);
        givenRate('CHF', '0.9', ['effective_rate' => '0.918']);
        $variant = variantCosting('10');
        markupRule('100');

        $quote = quote($variant, null, 'CHF');

        expect($quote->baseAmount->amount)->toBe('20.000000')
            ->and($quote->amount->amount)->toBe('18.350000')
            ->and($quote->amount->currency)->toBe('CHF');
    });

    it('reports a missing rate rather than inventing one', function (): void {
        CurrencyFactory::ensure('EGP');

        expect(quote(variantCosting('10'), null, 'EGP')->blockReason)->toBe('rate_missing');
    });

    it('shows a stale rate for display but blocks it for checkout', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50', ['fetched_at' => now()->subDays(3)]);
        $variant = variantCosting('10');

        expect(quote($variant, null, 'EGP')->rate->isStale)->toBeTrue()
            ->and(quote($variant, null, 'EGP')->purchasable)->toBeTrue()
            ->and(quote($variant, null, 'EGP', forCheckout: true)->blockReason)->toBe('rate_stale');
    });

    it('carries the history row for the order snapshot', function (): void {
        CurrencyFactory::ensure('EGP');
        givenRate('EGP', '50');
        $history = ExchangeRateHistory::factory()->create(['quote_currency' => 'EGP', 'base_currency' => 'USD', 'effective_rate' => '50']);
        Cache::flush();

        expect(quote(variantCosting('10'), null, 'EGP')->rate->historyId)->toBe($history->id);
    });
});

it('prices many variants with the same number of queries as few', function (): void {
    $count = function (int $variants): int {
        $models = ProductVariant::factory()->count($variants)->create(['manual_cost_base' => '10']);
        Cache::flush();
        DB::flushQueryLog();
        DB::enableQueryLog();

        app(PricingEngine::class)->quoteMany(ProductVariant::query()->whereKey($models->pluck('id'))->get(), new PricingContext);

        return count(DB::getQueryLog());
    };

    expect($count(25))->toBe($count(2));
});

it('exposes a price simulation without changing anything', function (): void {
    actingAsStaffWith(['pricing.view']);
    $variant = variantCosting('10');
    markupRule('50');

    $this->postJson('/admin/api/pricing-rules/simulate', ['variant' => $variant->public_id])
        ->assertOk()
        ->assertJsonPath('data.base_amount', '15.000000')
        ->assertJsonPath('data.source', 'markup');

    expect($variant->fresh()->price_base)->toBeNull();
});

it('serves variant offers in the display currency without leaking cost', function (): void {
    CurrencyFactory::ensure('EGP');
    givenRate('EGP', '50');
    Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
    Cache::flush();
    $variant = variantCosting('10', ['availability' => ProductVariantAvailability::InStock]);
    $variant->product->forceFill(['status' => ProductStatus::Active])->save();
    markupRule('100');

    $response = $this->withCredentials()->withUnencryptedCookie('currency', 'EGP')
        ->getJson("/api/v1/storefront/catalog/products/{$variant->product->slug}/variants?country=EG")
        ->assertOk()
        ->assertJsonPath('meta.display_currency', 'EGP')
        ->assertJsonPath('data.variants.0.price.amount', '1000.00')
        ->assertJsonPath('data.variants.0.purchasable', true);

    expect($response->getContent())->not->toContain('floor')->not->toContain('cost');
});

it('hides variant offers of an unpublished product', function (): void {
    $variant = variantCosting('10');

    $this->getJson("/api/v1/storefront/catalog/products/{$variant->product->slug}/variants")->assertNotFound();
});
