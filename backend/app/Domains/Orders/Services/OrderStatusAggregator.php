<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Derives an order's status from its units after payment (architecture 6.3) and applies it
 * through the state machine.
 */
class OrderStatusAggregator
{
    public function __construct(private OrderStateMachine $machine) {}

    /**
     * @param  iterable<Unit>  $units
     */
    public function derive(iterable $units): OrderStatus
    {
        $statuses = [];

        foreach ($units as $unit) {
            $statuses[$unit->value] = true;
        }

        if ($statuses === []) {
            throw new InvalidArgumentException('An order without units has no derived status.');
        }

        $any = static function (Unit ...$candidates) use ($statuses): bool {
            foreach ($candidates as $candidate) {
                if (isset($statuses[$candidate->value])) {
                    return true;
                }
            }

            return false;
        };

        return match (true) {
            $any(Unit::PendingVerification) => OrderStatus::PendingVerification,
            $any(Unit::ManualReview, Unit::ManualTask) => OrderStatus::ManualReview,
            $any(Unit::SupplierPending) => OrderStatus::SupplierPending,
            $any(Unit::Pending, Unit::Queued, Unit::Submitting) => OrderStatus::Processing,
            count($statuses) === 1 && $any(Unit::Delivered) => OrderStatus::Completed,
            $any(Unit::Delivered) => OrderStatus::PartiallyFulfilled,
            default => OrderStatus::RefundPending,
        };
    }

    /**
     * Move the order to the status its units imply. Returns the new status, or null when the
     * order already has it. An implied move the transition table forbids throws.
     */
    public function sync(Order|int $order, OrderActor $actor): ?OrderStatus
    {
        $orderId = $order instanceof Order ? $order->id : $order;

        return DB::transaction(function () use ($orderId, $actor): ?OrderStatus {
            $locked = Order::query()->lockForUpdate()->findOrFail($orderId);

            $derived = $this->derive(
                OrderItemUnit::query()->where('order_id', $orderId)->pluck('status')
                    ->map(static fn (Unit|string $status): Unit => $status instanceof Unit ? $status : Unit::from($status)),
            );

            if ($derived === $locked->status) {
                return null;
            }

            $this->machine->transition($locked, $derived, $actor, 'Derived from unit statuses.');

            return $derived;
        });
    }
}
