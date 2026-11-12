<?php

namespace App\Http\Support\AdminScreens\Screens;

use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Pricing\Models\PricingRule;
use App\Http\Support\AdminScreens\Field;
use App\Http\Support\AdminScreens\Options;
use App\Http\Support\AdminScreens\ResourceScreen;
use Illuminate\Database\Eloquent\Model;

/**
 * Pricing and markup rules (spec Phase 8 and 9.7): scope, markup, country and currency, minimum margin and its policy.
 */
class PricingRuleScreen extends ResourceScreen
{
    public static function section(): string
    {
        return 'pricing';
    }

    public static function slug(): string
    {
        return 'pricing-rules';
    }

    public static function viewPermission(): string
    {
        return 'pricing.view';
    }

    public static function managePermission(): string
    {
        return 'pricing.manage';
    }

    public static function model(): string
    {
        return PricingRule::class;
    }

    public static function table(): string
    {
        return 'pricing-rules';
    }

    protected function apiPath(): string
    {
        return '/admin/api/pricing-rules';
    }

    public function fields(?Model $record): array
    {
        return [
            'rule' => [
                Field::text('name', $this->l('name'))->required()->max(120),
                Field::select('kind', $this->l('kind'), Options::enum('pricing_kind', PricingRuleKind::cases()))->required(),
                Field::select('scope', $this->l('scope'), Options::enum('pricing_scope', PricingRuleScope::cases()))->required(),
                Field::number('scope_id', $this->l('scope_id'))->nullable()->help(__('admin.help.scope_id')),
                Field::select('country_code', $this->l('country_code'), Options::countries())->nullable(),
                Field::select('currency', $this->l('currency'), Options::currencies())->nullable(),
                Field::number('priority', $this->l('priority'))->default(0),
                Field::toggle('is_active', $this->l('is_active'))->default(true),
            ],
            'markup' => [
                Field::decimal('fixed_markup', $this->l('fixed_markup'))->nullable()->help(__('admin.help.base_money')),
                Field::decimal('percent_markup', $this->l('percent_markup'))->nullable()->help(__('admin.help.percent')),
                Field::decimal('min_margin_pct', $this->l('min_margin_pct'))->nullable()->help(__('admin.help.percent')),
                Field::decimal('min_margin_fixed', $this->l('min_margin_fixed'))->nullable()->help(__('admin.help.base_money')),
                Field::select('below_margin_policy', $this->l('below_margin_policy'), Options::enum('margin_policy', PricingRuleBelowMarginPolicy::cases()))->nullable(),
                Field::text('override_reason', $this->l('override_reason'))->nullable()->max(1000)->wide()->help(__('admin.help.override_reason')),
                Field::decimal('price_from', $this->l('price_from'))->nullable()->help(__('admin.help.price_from'))
                    ->using(static fn (Model $rule): ?string => $rule->conditions['cost_min'] ?? null),
                Field::decimal('price_to', $this->l('price_to'))->nullable()->help(__('admin.help.price_to'))
                    ->using(static fn (Model $rule): ?string => $rule->conditions['cost_max'] ?? null),
                Field::json('conditions', $this->l('conditions'))->nullable()->help(__('admin.help.conditions_json')),
            ],
            'schedule' => [
                Field::datetime('starts_at', $this->l('starts_at')),
                Field::datetime('ends_at', $this->l('ends_at')),
            ],
        ];
    }
}
