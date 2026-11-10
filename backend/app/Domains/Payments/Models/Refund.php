<?php

namespace App\Domains\Payments\Models;

use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundRequestedByType;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Support\Models\RefundRequest;
use Database\Factories\Payments\RefundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('refunds')]
#[Fillable(['order_id', 'payment_id', 'destination', 'reason', 'currency', 'provider_refund_id', 'requested_by_type', 'requested_by_id', 'note', 'failure_reason'])]
#[UseFactory(RefundFactory::class)]
class Refund extends Model
{
    /** @use HasFactory<RefundFactory> */
    use HasFactory, HasUlids;

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
            'destination' => RefundDestination::class,
            'status' => RefundStatus::class,
            'reason' => RefundReason::class,
            'amount' => 'decimal:6',
            'amount_base' => 'decimal:6',
            'requested_by_type' => RefundRequestedByType::class,
            'requested_by_id' => 'integer',
            'completed_at' => 'datetime',
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
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }

    /**
     * @return BelongsTo<Currency, $this>
     */
    public function currencyRecord(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency', 'code');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return HasMany<RefundRequest, $this>
     */
    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class, 'refund_id');
    }

    /**
     * @return BelongsToMany<OrderItemUnit, $this>
     */
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(OrderItemUnit::class, 'refund_units', 'refund_id', 'order_item_unit_id');
    }
}
