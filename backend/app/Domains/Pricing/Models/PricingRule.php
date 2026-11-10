<?php

namespace App\Domains\Pricing\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Pricing\Enums\PricingRuleBelowMarginPolicy;
use App\Domains\Pricing\Enums\PricingRuleKind;
use App\Domains\Pricing\Enums\PricingRuleScope;
use App\Domains\Shared\Models\Country;
use Database\Factories\Pricing\PricingRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('pricing_rules')]
#[Fillable(['name', 'scope', 'scope_id', 'kind', 'country_code', 'currency', 'fixed_markup', 'percent_markup', 'min_margin_pct', 'min_margin_fixed', 'below_margin_policy', 'conditions', 'priority', 'starts_at', 'ends_at', 'is_active'])]
#[UseFactory(PricingRuleFactory::class)]
class PricingRule extends Model
{
    /** @use HasFactory<PricingRuleFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scope' => PricingRuleScope::class,
            'scope_id' => 'integer',
            'kind' => PricingRuleKind::class,
            'fixed_markup' => 'decimal:6',
            'percent_markup' => 'decimal:4',
            'min_margin_pct' => 'decimal:4',
            'min_margin_fixed' => 'decimal:6',
            'below_margin_policy' => PricingRuleBelowMarginPolicy::class,
            'conditions' => 'array',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }
}
