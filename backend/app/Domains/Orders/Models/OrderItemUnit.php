<?php

namespace App\Domains\Orders\Models;

use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Database\Factories\Orders\OrderItemUnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Table('order_item_units')]
#[Fillable(['order_id', 'order_item_id', 'selection_snapshot', 'delivered_at', 'first_revealed_at', 'refunded_at'])]
#[UseFactory(OrderItemUnitFactory::class)]
class OrderItemUnit extends Model
{
    /** @use HasFactory<OrderItemUnitFactory> */
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
            'status' => OrderItemUnitStatus::class,
            'selection_snapshot' => 'array',
            'delivered_at' => 'datetime',
            'first_revealed_at' => 'datetime',
            'refunded_at' => 'datetime',
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
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * @return HasMany<SupplierTransaction, $this>
     */
    public function supplierTransactions(): HasMany
    {
        return $this->hasMany(SupplierTransaction::class, 'order_item_unit_id');
    }

    /**
     * @return HasMany<OrderEvent, $this>
     */
    public function orderEvents(): HasMany
    {
        return $this->hasMany(OrderEvent::class, 'order_item_unit_id');
    }

    /**
     * @return HasOne<ManualFulfillmentTask, $this>
     */
    public function manualFulfillmentTask(): HasOne
    {
        return $this->hasOne(ManualFulfillmentTask::class, 'order_item_unit_id');
    }

    /**
     * @return HasOne<DigitalCode, $this>
     */
    public function digitalCode(): HasOne
    {
        return $this->hasOne(DigitalCode::class, 'order_item_unit_id');
    }

    /**
     * @return BelongsToMany<Refund, $this>
     */
    public function refunds(): BelongsToMany
    {
        return $this->belongsToMany(Refund::class, 'refund_units', 'order_item_unit_id', 'refund_id');
    }
}
