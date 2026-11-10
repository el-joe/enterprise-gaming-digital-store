<?php

namespace App\Domains\Payments\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\PaymentClient;
use App\Domains\Payments\Enums\PaymentStatus;
use Database\Factories\Payments\PaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('payments')]
#[Fillable(['order_id', 'payment_gateway_id', 'attempt_no', 'method_code', 'client', 'provider_payment_id', 'provider_order_id', 'provider_capture_id', 'provider_reference', 'base_currency', 'currency', 'fee_currency', 'fee_is_estimate', 'settled_currency', 'failure_code', 'failure_message', 'card_country', 'card_fingerprint_hash', 'verified_at', 'next_check_at', 'expires_at'])]
#[UseFactory(PaymentFactory::class)]
#[Hidden(['card_fingerprint_hash'])]
class Payment extends Model
{
    /** @use HasFactory<PaymentFactory> */
    use HasFactory, HasUlids;

    /**
     * The column default, so an attempt built in memory reads the same as one read back (M-A6).
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['client' => 'web'];

    /**
     * Get the columns that receive a ULID on creation.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempt_no' => 'integer',
            'status' => PaymentStatus::class,
            'client' => PaymentClient::class,
            'base_amount' => 'decimal:6',
            'rate' => 'decimal:12',
            'amount' => 'decimal:6',
            'fee_amount' => 'decimal:6',
            'fee_is_estimate' => 'boolean',
            'settled_amount' => 'decimal:6',
            'verified_at' => 'datetime',
            'next_check_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
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
    public function baseCurrencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency', 'code');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    /**
     * @return HasOne<Order, $this>
     */
    public function orderRelated(): HasOne
    {
        return $this->hasOne(Order::class, 'primary_payment_id');
    }

    /**
     * @return HasMany<PaymentTransaction, $this>
     */
    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class, 'payment_id');
    }

    /**
     * @return HasMany<PaymentWebhook, $this>
     */
    public function paymentWebhooks(): HasMany
    {
        return $this->hasMany(PaymentWebhook::class, 'payment_id');
    }

    /**
     * @return HasMany<Refund, $this>
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class, 'payment_id');
    }

    /**
     * @return HasMany<PaymentDispute, $this>
     */
    public function paymentDisputes(): HasMany
    {
        return $this->hasMany(PaymentDispute::class, 'payment_id');
    }
}
