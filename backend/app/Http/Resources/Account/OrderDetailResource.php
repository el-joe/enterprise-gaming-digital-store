<?php

namespace App\Http\Resources\Account;

use App\Domains\Catalog\Services\ProductImages;
use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Fulfillment\Services\CodeRevealService;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\Refund;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Http\Request;

/**
 * The order detail of the account and of the guest order route (M-A5, gap G4). It lists the
 * unit public ids the reveal routes take, with a `revealable` hint (paid order, delivered unit
 * holding a code; the reveal itself still checks the viewer, recent authentication and audits).
 *
 * Never included: the code, PIN or redemption URL (only `masked_code`, the last characters
 * stored for display), provider identifiers and secrets of payment attempts, the pricing
 * breakdown (cost, floor, margin), delivery values, staff notes and event messages.
 * Needs the relations of CustomerOrderRepository::loadDetail.
 *
 * @mixin Order
 */
class OrderDetailResource extends OrderSummaryResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $converter = app(CurrencyConverter::class);
        $money = static fn (mixed $amount, ?string $currency): ?array => $amount === null || $currency === null ? null : $converter->present(Money::of($amount, $currency));
        $images = app(ProductImages::class);
        $images->load($this->orderItems->pluck('product'));

        return [
            ...parent::toArray($request),
            'currency' => ['display' => $this->display_currency, 'payment' => $this->payment_currency],
            'completed_at' => $this->completed_at?->toIso8601String(),
            'items' => $this->orderItems->map(fn (OrderItem $item): array => [
                'id' => $item->id,
                'name' => $item->name_snapshot,
                'product' => $item->product === null ? null : ['slug' => $item->product->slug, ...$images->fields($item->product)],
                'delivery_type' => $item->delivery_type->value,
                'quantity' => $item->quantity,
                'unit_price' => $money($item->pricing_breakdown['display_unit_amount'] ?? null, $this->display_currency),
                'line_total' => $money($item->pricing_breakdown['display_line_total'] ?? null, $this->display_currency),
                'units' => $item->orderItemUnits->map(fn (OrderItemUnit $unit): array => [
                    'public_id' => $unit->public_id,
                    'status' => $unit->status->value,
                    'delivery_type' => $item->delivery_type->value,
                    'revealable' => CodeRevealService::isRevealable($this->resource, $unit, $unit->digitalCode !== null),
                    'masked_code' => $unit->digitalCode?->masked_code,
                    'delivered_at' => $unit->delivered_at?->toIso8601String(),
                    'first_revealed_at' => $unit->first_revealed_at?->toIso8601String(),
                    'refunded_at' => $unit->refunded_at?->toIso8601String(),
                ])->values()->all(),
            ])->values()->all(),
            'payments' => $this->payments->map(static fn (Payment $payment): array => [
                'public_id' => $payment->public_id,
                'attempt' => $payment->attempt_no,
                'provider' => $payment->paymentGateway?->key,
                'method' => $payment->method_code,
                'status' => $payment->status->value,
                'amount' => $money($payment->amount, $payment->currency),
                'failure_code' => $payment->failure_code,
                'created_at' => $payment->created_at?->toIso8601String(),
            ])->values()->all(),
            'refunds' => $this->refunds->map(static fn (Refund $refund): array => [
                'public_id' => $refund->public_id,
                'status' => $refund->status->value,
                'destination' => $refund->destination->value,
                'amount' => $money($refund->amount, $refund->currency),
                'created_at' => $refund->created_at?->toIso8601String(),
                'completed_at' => $refund->completed_at?->toIso8601String(),
            ])->values()->all(),
            'refund_state' => $this->refundState(),
            'timeline' => $this->orderEvents->map(static fn (OrderEvent $event): array => [
                'status' => $event->to_status?->value,
                'at' => $event->created_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    /**
     * `none`, `pending` (a refund is requested or running), `partial` or `full`, from the order
     * status and its refunds.
     */
    private function refundState(): string
    {
        return match (true) {
            $this->status === OrderStatus::Refunded => 'full',
            $this->status === OrderStatus::PartiallyRefunded => 'partial',
            $this->status === OrderStatus::RefundPending || $this->refunds->contains(static fn (Refund $refund): bool => in_array($refund->status, [RefundStatus::Requested, RefundStatus::Pending, RefundStatus::Unknown], true)) => 'pending',
            $this->refunds->contains(static fn (Refund $refund): bool => $refund->status === RefundStatus::Succeeded) => 'partial',
            default => 'none',
        };
    }
}
