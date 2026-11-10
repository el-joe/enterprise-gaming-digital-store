<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Support\Facades\DB;

/**
 * Moves an order to the status its units imply, when the transition table allows it for the
 * actor. Some implied moves are deliberately not in the table:
 *
 * - `supplier_pending` back to `processing` (a fallback unit queued while others wait): the order
 *   simply stays `supplier_pending` until the units settle.
 * - Leaving `manual_review` needs staff. A staff actor whose own permission does not cover the
 *   implied row releases the order to `processing` with `orders.review` first, if held. Otherwise
 *   the order stays in review and a `status_held` event records why.
 */
class OrderProgress
{
    public function __construct(private OrderStatusAggregator $aggregator, private OrderStateMachine $machine) {}

    public function advance(Order|int $order, OrderActor $actor): ?OrderStatus
    {
        $orderId = $order instanceof Order ? $order->id : $order;

        return DB::transaction(function () use ($orderId, $actor): ?OrderStatus {
            $locked = Order::query()->lockForUpdate()->findOrFail($orderId);
            $units = OrderItemUnit::query()->where('order_id', $orderId)->pluck('status')
                ->map(static fn (OrderItemUnitStatus|string $status): OrderItemUnitStatus => $status instanceof OrderItemUnitStatus ? $status : OrderItemUnitStatus::from($status));

            if ($units->isEmpty()) {
                return null;
            }

            $derived = $this->aggregator->derive($units);

            if ($derived === $locked->status) {
                return null;
            }

            if ($this->machine->canTransition($locked, $derived, $actor)) {
                return $this->machine->transition($locked, $derived, $actor, 'Derived from unit statuses.')->status;
            }

            if ($locked->status === OrderStatus::ManualReview && $actor->type === OrderEventActorType::Staff
                && $this->machine->canTransition($locked, OrderStatus::Processing, $actor)) {
                $released = $this->machine->transition($locked, OrderStatus::Processing, $actor, 'Released from review: no unit needs staff action.');

                return $derived === OrderStatus::Processing ? $released->status : ($this->advance($orderId, OrderActor::system()) ?? $released->status);
            }

            if (! ($locked->status === OrderStatus::SupplierPending && $derived === OrderStatus::Processing)) {
                $this->held($locked, $derived, $actor);
            }

            return null;
        }, 3);
    }

    private function held(Order $order, OrderStatus $derived, OrderActor $actor): void
    {
        (new OrderEvent)->forceFill([
            'order_id' => $order->id,
            'type' => 'status_held',
            'from_status' => $order->status,
            'to_status' => $derived,
            'actor_type' => $actor->type,
            'actor_id' => $actor->id(),
            'message' => "Units imply {$derived->value}; the order stays {$order->status->value} until staff act.",
        ])->save();
    }
}
