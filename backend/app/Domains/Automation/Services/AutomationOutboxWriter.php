<?php

namespace App\Domains\Automation\Services;

use App\Domains\Automation\Enums\AutomationOutboxStatus;
use App\Domains\Automation\Models\AutomationOutbox;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Support\Models\SupportTicket;

/**
 * Writes domain events to automation_outbox (architecture section 16). Callers run it inside the
 * transaction that changes the state, so an event exists exactly when the change committed.
 * Payloads hold identifiers and non-sensitive facts only: no codes, contact details, amounts
 * paid by card or provider data. Disabled unless `automation.outbox.enabled`.
 */
class AutomationOutboxWriter
{
    public const string ORDER_CREATED = 'order.created';

    public const string PAYMENT_SUCCEEDED = 'payment.succeeded';

    public const string ORDER_DELIVERED = 'order.delivered';

    public const string ORDER_FAILED = 'order.failed';

    public const string TICKET_UPDATED = 'ticket.updated';

    /**
     * The order event for a status change, if the change is one n8n subscribes to.
     */
    public function recordOrderStatus(Order $order, ?OrderStatus $from, OrderStatus $to): ?AutomationOutbox
    {
        $type = match (true) {
            $from === null => self::ORDER_CREATED,
            $to === OrderStatus::Paid => self::PAYMENT_SUCCEEDED,
            $to === OrderStatus::Completed, $to === OrderStatus::PartiallyFulfilled => self::ORDER_DELIVERED,
            $to === OrderStatus::Failed => self::ORDER_FAILED,
            default => null,
        };

        if ($type === null) {
            return null;
        }

        return $this->record($type, [
            'order_public_id' => (string) $order->public_id,
            'order_number' => (string) $order->order_number,
            'status' => $to->value,
            'previous_status' => $from?->value,
            'is_guest' => $order->user_id === null,
            'locale' => (string) $order->locale,
        ]);
    }

    public function recordTicketUpdate(SupportTicket $ticket, string $change): ?AutomationOutbox
    {
        return $this->record(self::TICKET_UPDATED, [
            'ticket_public_id' => (string) $ticket->public_id,
            'status' => $ticket->status->value,
            'change' => $change,
            'is_guest' => $ticket->user_id === null,
        ]);
    }

    /**
     * @param  array<string, scalar|null>  $payload
     */
    public function record(string $eventType, array $payload): ?AutomationOutbox
    {
        if (! config('automation.outbox.enabled')) {
            return null;
        }

        $event = new AutomationOutbox;
        $event->forceFill([
            'event_type' => $eventType,
            'payload' => $payload,
            'status' => AutomationOutboxStatus::Pending,
            'attempts' => 0,
            'next_attempt_at' => now(),
        ])->save();

        return $event;
    }
}
