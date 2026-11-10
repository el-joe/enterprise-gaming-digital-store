<?php

namespace App\Domains\Currency\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Currency\ExchangeRateHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('exchange_rate_history')]
#[Fillable(['base_currency', 'quote_currency', 'source', 'is_manual', 'changed_by', 'captured_at'])]
#[UseFactory(ExchangeRateHistoryFactory::class)]
class ExchangeRateHistory extends Model
{
    /** @use HasFactory<ExchangeRateHistoryFactory> */
    use AppendOnly, HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider_rate' => 'decimal:12',
            'effective_rate' => 'decimal:12',
            'is_manual' => 'boolean',
            'captured_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'exchange_rate_history_id');
    }
}
