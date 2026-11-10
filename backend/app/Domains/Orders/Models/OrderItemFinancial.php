<?php

namespace App\Domains\Orders\Models;

use App\Domains\Catalog\Models\Product;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Suppliers\Models\Supplier;
use Database\Factories\Orders\OrderItemFinancialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('order_item_financials')]
#[Fillable(['order_item_id', 'order_id', 'product_id', 'supplier_id', 'payment_gateway_id', 'country_code', 'currency', 'gateway_fee_is_estimate', 'recognized_at'])]
#[UseFactory(OrderItemFinancialFactory::class)]
class OrderItemFinancial extends Model
{
    /** @use HasFactory<OrderItemFinancialFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'revenue_base' => 'decimal:6',
            'supplier_cost_base' => 'decimal:6',
            'gateway_fee_base' => 'decimal:6',
            'gateway_fee_is_estimate' => 'boolean',
            'fx_cost_base' => 'decimal:6',
            'other_cost_base' => 'decimal:6',
            'refunded_base' => 'decimal:6',
            'gross_profit_base' => 'decimal:6',
            'net_profit_base' => 'decimal:6',
            'recognized_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
