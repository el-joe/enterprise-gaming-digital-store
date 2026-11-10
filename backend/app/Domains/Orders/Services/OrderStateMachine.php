<?php

namespace App\Domains\Orders\Services;

use App\Domains\Automation\Services\AutomationOutboxWriter;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderEventActorType;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Exceptions\IllegalOrderTransition;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * The only writer of orders.status (architecture section 6). Each transition locks the order,
 * checks the transition table, the actor and verified payment, writes the status and an
 * order event in one transaction, and raises OrderStatusChanged after commit. Status changes
 * that n8n subscribes to are written to automation_outbox in the same transaction (M17).
 */
class OrderStateMachine
{
    public function __construct(private OrderTransitionTable $table, private CheckoutCoupons $coupons, private AutomationOutboxWriter $outbox) {}

    /**
     * Bring a new order into existence in pending_payment. Callers run this inside their own
     * transaction together with the order's items.
     *
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>  $metadata  IDs and non-sensitive facts only
     */
    public function open(array $attributes, OrderActor $actor, array $metadata = []): Order
    {
        $order = (new Order)->forceFill([
            ...$attributes,
            'status' => OrderStatus::PendingPayment,
            'status_changed_at' => now(),
        ]);
        $order->save();

        $this->record($order, 'order.placed', null, OrderStatus::PendingPayment, $actor, 'Order placed.', $metadata);
        $this->outbox->recordOrderStatus($order, null, OrderStatus::PendingPayment);

        OrderStatusChanged::dispatch($order->id, null, OrderStatus::PendingPayment);

        return $order;
    }

    /**
     * @param  array<string, mixed>  $metadata  IDs and non-sensitive facts only
     *
     * @throws IllegalOrderTransition
     */
    public function transition(Order|int $order, OrderStatus $to, OrderActor $actor, ?string $message = null, array $metadata = [], ?int $unitId = null): Order
    {
        $orderId = $order instanceof Order ? $order->id : $order;

        return DB::transaction(function () use ($orderId, $to, $actor, $message, $metadata, $unitId): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($orderId);
            $from = $locked->status;

            $this->assertAllowed($locked, $from, $to, $actor);

            $changes = ['status' => $to->value, 'status_changed_at' => now()];

            if ($to === OrderStatus::Paid && $locked->paid_at === null) {
                $changes['paid_at'] = now();
            }

            if ($to === OrderStatus::Completed) {
                $changes['completed_at'] = now();
            }

            if ($to->isClosed()) {
                $changes['placement_hash'] = null;
            }

            Order::query()->whereKey($locked->id)->update($changes);

            if (in_array($to, [OrderStatus::Cancelled, OrderStatus::Failed], true) && $locked->paid_at === null) {
                $this->coupons->release($locked->id);
            }

            $this->record($locked, 'status_changed', $from, $to, $actor, $message, $metadata, $unitId);
            $this->outbox->recordOrderStatus($locked, $from, $to);

            OrderStatusChanged::dispatch($locked->id, $from, $to);

            return $locked->refresh();
        }, 3);
    }

    public function canTransition(Order $order, OrderStatus $to, OrderActor $actor): bool
    {
        try {
            $this->check($order, $order->status, $to, $actor);
        } catch (IllegalOrderTransition) {
            return false;
        }

        return true;
    }

    private function assertAllowed(Order $order, OrderStatus $from, OrderStatus $to, OrderActor $actor): void
    {
        try {
            $this->check($order, $from, $to, $actor);
        } catch (IllegalOrderTransition $exception) {
            Log::alert('orders.illegal_transition', [
                'order_id' => $order->id,
                'from' => $from->value,
                'to' => $to->value,
                'actor_type' => $actor->type->value,
                'actor_id' => $actor->id(),
            ]);

            throw $exception;
        }
    }

    private function check(Order $order, OrderStatus $from, OrderStatus $to, OrderActor $actor): void
    {
        $rule = $this->table->rule($from, $to);

        if ($rule === null) {
            throw IllegalOrderTransition::between($from, $to);
        }

        if (! in_array($actor->type, $rule['actors'], true)) {
            throw IllegalOrderTransition::actorNotAllowed($from, $to, $actor->type->value);
        }

        if ($actor->type === OrderEventActorType::Staff) {
            $user = $actor->user;

            if ($user === null || ! $user->isStaff() || ($rule['permission'] !== null && ! $user->can($rule['permission']->value))) {
                throw IllegalOrderTransition::actorNotAllowed($from, $to, 'staff');
            }
        }

        if ($to->requiresVerifiedPayment() && $order->primary_payment_id === null) {
            throw IllegalOrderTransition::withoutVerifiedPayment($to);
        }
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    private function record(Order $order, string $type, ?OrderStatus $from, OrderStatus $to, OrderActor $actor, ?string $message, array $metadata, ?int $unitId = null): void
    {
        (new OrderEvent)->forceFill([
            'order_id' => $order->id,
            'order_item_unit_id' => $unitId,
            'type' => $type,
            'from_status' => $from,
            'to_status' => $to,
            'actor_type' => $actor->type,
            'actor_id' => $actor->id(),
            'message' => $message,
            'metadata' => $metadata === [] ? null : $metadata,
        ])->save();
    }
}
