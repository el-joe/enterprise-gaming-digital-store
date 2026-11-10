<?php

namespace App\Domains\Orders\Notifications;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Domains\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Fulfilment failed for every unit; the order awaits refund. Carries the order id only.
 */
class OrderFulfilmentFailed extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $orderId)
    {
        $this->onQueue('notifications');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)->channelsFor($notifiable, NotificationPreferenceCategory::Orders, [NotificationPreferenceChannel::Mail, NotificationPreferenceChannel::Database]);
    }

    /**
     * The inbox kind, stored as the notification type (M-A5).
     */
    public function databaseType(object $notifiable): string
    {
        return InboxNotificationKind::OrderFulfilmentFailed->value;
    }

    /**
     * The inbox entry (M-A5): identifiers and the number only, never a code (ADR-009).
     *
     * @return array{kind: string, params: array<string, string>, link: array{type: string, value: string}}
     */
    public function toDatabase(object $notifiable): array
    {
        $order = Order::query()->findOrFail($this->orderId);

        return ['kind' => InboxNotificationKind::OrderFulfilmentFailed->value, 'params' => ['number' => $order->order_number], 'link' => ['type' => LinkTargetType::Order->value, 'value' => $order->public_id]];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = Order::query()->findOrFail($this->orderId);

        return (new MailMessage)
            ->subject("We could not complete order {$order->order_number}")
            ->line('We were unable to deliver your order. You will not be charged for undelivered items: a refund is being prepared.')
            ->line('If you have questions, reply to this email or contact support with your order number.');
    }
}
