<?php

namespace App\Domains\Payments\Models;

use App\Domains\Currency\Models\Currency;
use Database\Factories\Payments\PaymentGatewayCurrencyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_gateway_currencies')]
#[Fillable(['payment_gateway_id', 'currency', 'minor_exponent', 'min_amount', 'max_amount', 'is_enabled', 'integration_ref'])]
#[UseFactory(PaymentGatewayCurrencyFactory::class)]
class PaymentGatewayCurrency extends Model
{
    /** @use HasFactory<PaymentGatewayCurrencyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'minor_exponent' => 'integer',
            'min_amount' => 'decimal:6',
            'max_amount' => 'decimal:6',
            'is_enabled' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }
}
