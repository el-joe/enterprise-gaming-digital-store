<?php

namespace App\Domains\Payments\Models;

use Database\Factories\Payments\PaymentGatewayFeeRuleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_gateway_fee_rules')]
#[Fillable(['payment_gateway_id', 'payment_method_code', 'country_code', 'currency', 'percent_fee', 'fixed_fee', 'fixed_fee_currency', 'is_active'])]
#[UseFactory(PaymentGatewayFeeRuleFactory::class)]
class PaymentGatewayFeeRule extends Model
{
    /** @use HasFactory<PaymentGatewayFeeRuleFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'percent_fee' => 'decimal:4',
            'fixed_fee' => 'decimal:6',
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
