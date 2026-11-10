<?php

namespace App\Domains\Orders\Models;

use Database\Factories\Orders\OrderItemDeliveryValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('order_item_delivery_values')]
#[Fillable(['order_item_id', 'field_code', 'field_label', 'value'])]
#[UseFactory(OrderItemDeliveryValueFactory::class)]
#[Hidden(['value'])]
class OrderItemDeliveryValue extends Model
{
    /** @use HasFactory<OrderItemDeliveryValueFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}
