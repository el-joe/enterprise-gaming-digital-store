<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Mcp\Services\PiiMasker;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Order details for MCP: items, units and supplier attempt statuses. Customer identifiers are
 * masked; delivery values (player ids), digital codes and their masks, payment references and
 * supplier references are never included.
 *
 * @mixin Order
 */
class McpOrderDetailResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $masker = app(PiiMasker::class);
        $base = $this->base_currency;
        $units = $this->orderItemUnits->groupBy('order_item_id');

        return [
            ...McpOrderResource::make($this->resource)->toArray($request),
            'customer' => [
                'type' => $this->user_id === null ? 'guest' : 'registered',
                'email' => $masker->email($this->contact_email),
                'phone' => $masker->phone($this->contact_phone),
                'name' => $masker->name($this->customer_name),
            ],
            'subtotal_base' => McpFormat::money($this->subtotal_base, $base),
            'discount_base' => McpFormat::money($this->discount_base, $base),
            'risk_score' => $this->risk_score,
            'has_open_dispute' => $this->has_open_dispute,
            'items' => $this->orderItems->map(static fn (OrderItem $item): array => [
                'name' => $item->name_snapshot,
                'sku' => $item->sku_snapshot,
                'delivery_type' => $item->delivery_type?->value,
                'quantity' => $item->quantity,
                'unit_price_base' => McpFormat::money($item->unit_price_base, $base),
                'discount_base' => McpFormat::money($item->discount_base, $base),
                'line_total_base' => McpFormat::money($item->line_total_base, $base),
                'units' => $units->get($item->id, collect())->map(static fn (OrderItemUnit $unit): array => [
                    'id' => $unit->public_id,
                    'status' => $unit->status->value,
                    'delivered_at' => McpFormat::iso($unit->delivered_at),
                    'refunded_at' => McpFormat::iso($unit->refunded_at),
                    'supplier_attempts' => $unit->supplierTransactions->map(static fn (SupplierTransaction $attempt): array => [
                        'attempt_no' => $attempt->attempt_no,
                        'status' => $attempt->status->value,
                        'outcome_reason' => $attempt->outcome_reason,
                        'is_fallback' => $attempt->is_fallback,
                        'submitted_at' => McpFormat::iso($attempt->submitted_at),
                        'completed_at' => McpFormat::iso($attempt->completed_at),
                    ])->values()->all(),
                ])->values()->all(),
            ])->values()->all(),
        ];
    }
}
