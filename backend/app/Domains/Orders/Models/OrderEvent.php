<?php

namespace App\Domains\Orders\Models;

use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Orders\OrderEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('order_events')]
#[Fillable(['order_id', 'order_item_unit_id', 'type', 'actor_type', 'actor_id', 'message', 'metadata'])]
#[UseFactory(OrderEventFactory::class)]
class OrderEvent extends Model
{
    /** @use HasFactory<OrderEventFactory> */
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
            'from_status' => OrderStatus::class,
            'to_status' => OrderStatus::class,
            'actor_type' => OrderEventActorType::class,
            'actor_id' => 'integer',
            'metadata' => 'array',
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
     * @return BelongsTo<OrderItemUnit, $this>
     */
    public function orderItemUnit(): BelongsTo
    {
        return $this->belongsTo(OrderItemUnit::class, 'order_item_unit_id');
    }
}
