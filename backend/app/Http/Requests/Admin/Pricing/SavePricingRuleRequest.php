<?php

namespace App\Http\Requests\Admin\Pricing;

use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Shared\Rules\DecimalString;
use App\Http\Requests\Admin\AdminSaveRequest;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SavePricingRuleRequest extends AdminSaveRequest
{
    private const array SCOPE_TABLES = [
        'category' => 'categories',
        'platform' => 'gaming_platforms',
        'product' => 'products',
        'variant' => 'product_variants',
    ];

    /**
     * Price range (owner request 2026-11-12): `price_from` (included) and `price_to` (not included)
     * are product cost in the base currency. They become the `cost_min` / `cost_max` conditions and
     * make the rule dynamic, so tiers such as 10-50, 50-100 and 100+ never overlap. Empty fields
     * remove the range; other conditions (availability) are kept.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->has('price_from') && ! $this->has('price_to')) {
            return;
        }

        $conditions = is_array($this->input('conditions')) ? $this->input('conditions') : [];
        unset($conditions['cost_min'], $conditions['cost_max']);

        foreach (['price_from' => 'cost_min', 'price_to' => 'cost_max'] as $field => $key) {
            $value = trim((string) $this->input($field, ''));

            if ($value !== '') {
                $conditions[$key] = $value;
            }
        }

        $merge = ['conditions' => $conditions === [] ? null : $conditions];

        if (isset($conditions['cost_min']) || isset($conditions['cost_max'])) {
            $merge['kind'] = PricingRuleKind::Dynamic->value;
        }

        $this->merge($merge);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [$this->presence(), 'string', 'max:120'],
            'scope' => [$this->presence(), Rule::enum(PricingRuleScope::class)],
            'scope_id' => ['nullable', 'integer', $this->scopeIdExists(...)],
            'kind' => [$this->presence(), Rule::enum(PricingRuleKind::class)],
            'country_code' => ['sometimes', 'nullable', 'string', 'size:2', Rule::exists('countries', 'code')],
            'currency' => ['sometimes', 'nullable', 'string', 'size:3', Rule::exists('currencies', 'code')],
            'fixed_markup' => ['sometimes', 'nullable', DecimalString::money()],
            'percent_markup' => ['sometimes', 'nullable', DecimalString::percent()],
            'min_margin_pct' => ['sometimes', 'nullable', DecimalString::percent(), $this->marginScopeAllowed(...)],
            'min_margin_fixed' => ['sometimes', 'nullable', DecimalString::money(), $this->marginScopeAllowed(...)],
            'below_margin_policy' => ['sometimes', 'nullable', Rule::enum(PricingRuleBelowMarginPolicy::class)],
            'override_reason' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'conditions' => [Rule::requiredIf($this->input('kind') === PricingRuleKind::Dynamic->value), 'nullable', 'array:availability,cost_min,cost_max'],
            'conditions.availability' => ['sometimes', 'array'],
            'conditions.availability.*' => ['string', Rule::in(['in_stock', 'limited', 'out_of_stock'])],
            'conditions.cost_min' => ['sometimes', DecimalString::money()],
            'conditions.cost_max' => ['sometimes', DecimalString::money()],
            'price_from' => ['sometimes', 'nullable', DecimalString::money()],
            'price_to' => ['sometimes', 'nullable', DecimalString::money(), function (string $attribute, mixed $value, Closure $fail): void {
                $from = trim((string) $this->input('price_from', ''));

                if ($value !== null && $value !== '' && $from !== '' && bccomp((string) $value, $from, 6) !== 1) {
                    $fail(__('admin.help.price_to_after_from'));
                }
            }],
            'priority' => ['sometimes', 'integer'],
            'starts_at' => ['sometimes', 'nullable', 'date'],
            'ends_at' => ['sometimes', 'nullable', 'date', 'after:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private function scopeIdExists(string $attribute, mixed $value, Closure $fail): void
    {
        $table = self::SCOPE_TABLES[(string) $this->input('scope')] ?? null;

        if ($table !== null && ! DB::table($table)->where('id', $value)->exists()) {
            $fail("The selected {$attribute} does not exist for this scope.");
        }
    }

    /**
     * Minimum margin resolves through variant, product, category and global only.
     */
    private function marginScopeAllowed(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->input('scope') === PricingRuleScope::Platform->value && $value !== null) {
            $fail('Minimum margin cannot be set on a platform rule.');
        }
    }
}
