<?php

namespace App\Domains\Payments\Listeners;

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Log;

/**
 * BD4 default: undelivered value is refunded automatically. A partially fulfilled order moves to
 * `refund_pending` (architecture 6.2, "automatic refund of undelivered units"), and an order in
 * `refund_pending` gets a refund for every failed unit not yet refunded. Delivered units are
 * refunded only by staff.
 */
class RefundUndeliveredUnits
{
    public function __construct(private OrderStateMachine $machine, private RefundService $refunds) {}

    public function handle(OrderStatusChanged $event): void
    {
        if ($event->to === OrderStatus::PartiallyFulfilled) {
            $this->machine->transition($event->orderId, OrderStatus::RefundPending, OrderActor::system(), 'Automatic refund of undelivered units.');

            return;
        }

        if ($event->to !== OrderStatus::RefundPending) {
            return;
        }

        try {
            $this->refunds->refundUndelivered($event->orderId);
        } catch (ApplicationException $exception) {
            Log::alert('payments.automatic_refund_refused', ['order_id' => $event->orderId, 'code' => $exception->errorCode]);
        }
    }
}
