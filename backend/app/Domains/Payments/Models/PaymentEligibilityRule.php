<?php

namespace App\Domains\Payments\Models;

use App\Domains\Customers\Enums\CustomerRiskLevel;
use App\Domains\Payments\Enums\PaymentEligibilityRuleEffect;
use Database\Factories\Payments\PaymentEligibilityRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_eligibility_rules')]
#[Fillable(['payment_gateway_id', 'effect', 'countries', 'ip_countries', 'require_ip_match', 'currencies', 'min_amount_base', 'max_amount_base', 'allow_guest', 'max_risk_level', 'priority', 'is_active'])]
#[UseFactory(PaymentEligibilityRuleFactory::class)]
class PaymentEligibilityRule extends Model
{
    /** @use HasFactory<PaymentEligibilityRuleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'effect' => PaymentEligibilityRuleEffect::class,
            'countries' => 'array',
            'ip_countries' => 'array',
            'require_ip_match' => 'boolean',
            'currencies' => 'array',
            'min_amount_base' => 'decimal:6',
            'max_amount_base' => 'decimal:6',
            'allow_guest' => 'boolean',
            'max_risk_level' => CustomerRiskLevel::class,
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
