<?php

namespace App\Domains\Pricing\Services;

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Pricing\DTOs\MarginThreshold;
use App\Domains\Pricing\DTOs\PricingDataset;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Support\Collection;

/**
 * Selects the single markup rule (steps 3 and 4) and the minimum margin that applies to a variant.
 */
class PricingRuleResolver
{
    private const array MARKUP_SCOPES = [PricingRuleScope::Variant, PricingRuleScope::Product, PricingRuleScope::Platform, PricingRuleScope::Category, PricingRuleScope::Global];

    private const array MARGIN_SCOPES = [PricingRuleScope::Variant, PricingRuleScope::Product, PricingRuleScope::Category, PricingRuleScope::Global];

    public function __construct(private SettingsService $settings) {}

    public function markupRule(ProductVariant $variant, PricingDataset $data, ?string $country, string $currency, ?Money $cost): ?PricingRule
    {
        foreach (self::MARKUP_SCOPES as $scope) {
            $rule = $this->best($this->matching($data->rules, $scope, $variant, $country, $currency, $cost)
                ->filter(static fn (PricingRule $rule): bool => $rule->fixed_markup !== null || $rule->percent_markup !== null));

            if ($rule !== null) {
                return $rule;
            }
        }

        return null;
    }

    public function marginThreshold(ProductVariant $variant, PricingDataset $data, ?string $country, string $currency, ?Money $cost): MarginThreshold
    {
        $source = null;
        $policy = null;

        foreach (self::MARGIN_SCOPES as $scope) {
            $candidates = $this->matching($data->rules, $scope, $variant, $country, $currency, $cost);

            $source ??= $this->best($candidates->filter(static fn (PricingRule $rule): bool => $rule->min_margin_pct !== null || $rule->min_margin_fixed !== null));

            $policy ??= $this->best($candidates->filter(static fn (PricingRule $rule): bool => $rule->below_margin_policy !== null))?->below_margin_policy;
        }

        return new MarginThreshold(
            percent: (string) ($source?->min_margin_pct ?? $this->settings->get('pricing', 'default_min_margin_pct', '0')),
            fixedBase: (string) ($source?->min_margin_fixed ?? $this->settings->get('pricing', 'default_min_margin_fixed', '0')),
            policy: $policy ?? PricingRuleBelowMarginPolicy::from((string) $this->settings->get('pricing', 'default_below_margin_policy', PricingRuleBelowMarginPolicy::Block->value)),
            ruleId: $source?->id,
        );
    }

    /**
     * @param  Collection<int, PricingRule>  $rules
     * @return Collection<int, PricingRule>
     */
    private function matching(Collection $rules, PricingRuleScope $scope, ProductVariant $variant, ?string $country, string $currency, ?Money $cost): Collection
    {
        return $rules->filter(fn (PricingRule $rule): bool => $rule->scope === $scope
            && $this->targets($rule, $variant)
            && ($rule->country_code === null || $rule->country_code === $country)
            && ($rule->currency === null || $rule->currency === $currency)
            && ($rule->kind !== PricingRuleKind::Dynamic || $this->conditionsHold($rule, $variant, $cost)));
    }

    private function targets(PricingRule $rule, ProductVariant $variant): bool
    {
        return match ($rule->scope) {
            PricingRuleScope::Global => true,
            PricingRuleScope::Variant => $rule->scope_id === $variant->id,
            PricingRuleScope::Product => $rule->scope_id === $variant->product_id,
            PricingRuleScope::Platform => $rule->scope_id === $variant->product->gaming_platform_id,
            PricingRuleScope::Category => $rule->scope_id === $variant->product->primary_category_id
                || $variant->product->categories->contains('id', $rule->scope_id),
        };
    }

    /**
     * Country and currency first, then country, then currency, then neither; then lower priority
     * number; then newest.
     *
     * @param  Collection<int, PricingRule>  $candidates
     */
    private function best(Collection $candidates): ?PricingRule
    {
        return $candidates->sort(static function (PricingRule $left, PricingRule $right): int {
            $specificity = static fn (PricingRule $rule): int => match (true) {
                $rule->country_code !== null && $rule->currency !== null => 0,
                $rule->country_code !== null => 1,
                $rule->currency !== null => 2,
                default => 3,
            };

            return [$specificity($left), $left->priority, $right->id] <=> [$specificity($right), $right->priority, $left->id];
        })->first();
    }

    /**
     * Supported conditions: `availability` (list), `cost_min` and `cost_max` (decimal strings).
     * Any other key, or no keys at all, fails closed.
     */
    private function conditionsHold(PricingRule $rule, ProductVariant $variant, ?Money $cost): bool
    {
        $conditions = $rule->conditions ?? [];

        if ($conditions === [] || array_diff(array_keys($conditions), ['availability', 'cost_min', 'cost_max']) !== []) {
            return false;
        }

        if (isset($conditions['availability']) && ! in_array($variant->availability->value, (array) $conditions['availability'], true)) {
            return false;
        }

        foreach (['cost_min' => -1, 'cost_max' => 1] as $key => $outside) {
            if (! isset($conditions[$key])) {
                continue;
            }

            if ($cost === null || bccomp($cost->amount, (string) $conditions[$key], Money::SCALE) === $outside) {
                return false;
            }
        }

        return true;
    }
}
