<?php

namespace App\Domains\Orders\Services;

use App\Domains\Catalog\Models\ProductDeliveryField;
use App\Domains\Orders\DTOs\CartQuote;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemDeliveryValue;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Support\Str;

/**
 * Writes the item, unit and delivery-value snapshot of a new order. Each item keeps the engine's
 * pricing breakdown and the per-unit coupon allocation keyed by unit public id, so refunds and
 * reports never need to recompute a price.
 */
class OrderItemWriter
{
    /**
     * One order item per line, one unit row per purchased unit, and the delivery values.
     */
    public function write(Order $order, CartQuote $quote): void
    {
        foreach ($quote->lines as $line) {
            $variant = $line->item->productVariant;
            $product = $variant->product;
            $unitIds = array_map(static fn (): string => (new OrderItemUnit)->newUniqueId(), $line->units);

            $item = (new OrderItem)->forceFill([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'name_snapshot' => Str::limit($product->name.' - '.$variant->title, 252),
                'sku_snapshot' => $variant->sku,
                'delivery_type' => $product->delivery_type,
                'quantity' => $line->item->quantity,
                'unit_price_base' => $line->quote->baseAmount->amount,
                'compare_at_base' => $variant->compare_at_base,
                'discount_base' => $line->discountBase($quote->baseCurrency)->amount,
                'line_total_base' => $line->totalBase($quote->baseCurrency)->amount,
                'estimated_unit_cost_base' => $line->quote->cost?->amount,
                'custom_amount' => null,
                'pricing_breakdown' => [
                    ...$line->quote->breakdown(),
                    'display_unit_amount' => $line->quote->amount->amount,
                    'display_line_total' => $line->totalDisplay($quote->displayCurrency)->amount,
                    'coupon' => $quote->coupon === null ? null : ['coupon_id' => $quote->coupon->couponId, 'code' => $quote->coupon->code, 'allocation' => 'proportional_by_unit_price'],
                    'units' => array_map(static fn ($unit, string $publicId): array => [
                        'unit_public_id' => $publicId,
                        'gross_base' => $unit->grossBase->amount,
                        'discount_base' => $unit->discountBase->amount,
                        'net_base' => $unit->netBase->amount,
                        'net_display' => $unit->netDisplay->amount,
                    ], $line->units, $unitIds),
                ],
            ]);
            $item->save();

            foreach ($unitIds as $publicId) {
                (new OrderItemUnit)->forceFill([
                    'public_id' => $publicId,
                    'order_id' => $order->id,
                    'order_item_id' => $item->id,
                    'status' => OrderItemUnitStatus::Pending,
                ])->save();
            }

            $this->copyDeliveryValues($item, $line->item->delivery_values ?? [], $product->productDeliveryFields);
        }
    }

    /**
     * @param  array<string, string>  $values
     * @param  iterable<ProductDeliveryField>  $fields
     */
    private function copyDeliveryValues(OrderItem $item, array $values, iterable $fields): void
    {
        foreach ($fields as $field) {
            $definition = $field->deliveryFieldDefinition;

            if (! isset($values[$definition->code])) {
                continue;
            }

            (new OrderItemDeliveryValue)->forceFill([
                'order_item_id' => $item->id,
                'field_code' => $definition->code,
                'field_label' => $definition->label,
                'value' => $values[$definition->code],
            ])->save();
        }
    }
}
