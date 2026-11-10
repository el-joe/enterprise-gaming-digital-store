<?php

namespace App\Domains\Currency\Models;

use Database\Factories\Currency\ExchangeRateFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('exchange_rates')]
#[Fillable(['base_currency', 'quote_currency', 'manual_rate', 'is_manual_active', 'manual_expires_at', 'source', 'fetched_at'])]
#[UseFactory(ExchangeRateFactory::class)]
class ExchangeRate extends Model
{
    /** @use HasFactory<ExchangeRateFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider_rate' => 'decimal:12',
            'manual_rate' => 'decimal:12',
            'is_manual_active' => 'boolean',
            'manual_expires_at' => 'datetime',
            'effective_rate' => 'decimal:12',
            'fetched_at' => 'datetime',
            'pending_rate' => 'decimal:12',
            'pending_since' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function baseCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function quoteCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'quote_currency', 'code');
    }
}
