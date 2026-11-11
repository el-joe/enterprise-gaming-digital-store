<?php

namespace App\Domains\Payments\Notifications;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Contracts\PushNotification;
use App\Domains\Notifications\DTOs\PushMessage;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Domains\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * "Payment did not go through" (M-A18, owner decision 9): a verified provider failure on a
 * customer's order that is still awaiting payment. Inbox and push only (no email), following the
 * customer's orders preferences. Carries the order id only; the entry holds the order number and
 * public id, never an amount, card detail or provider message.
 */
class PaymentFailed extends Notification implements PushNotification, ShouldQueue
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
        return app(NotificationPreferenceService::class)->channelsFor($notifiable, NotificationPreferenceCategory::Orders, [NotificationPreferenceChannel::Database, NotificationPreferenceChannel::Push]);
    }

    /**
     * The inbox kind, stored as the notification type (M-A5).
     */
    public function databaseType(object $notifiable): string
    {
        return InboxNotificationKind::PaymentFailed->value;
    }

    /**
     * @return array{kind: string, params: array<string, string>, link: array{type: string, value: string}}
     */
    public function toDatabase(object $notifiable): array
    {
        $order = Order::query()->findOrFail($this->orderId);

        return ['kind' => InboxNotificationKind::PaymentFailed->value, 'params' => ['number' => $order->order_number], 'link' => ['type' => LinkTargetType::Order->value, 'value' => $order->public_id]];
    }

    public function toPush(User $notifiable): ?PushMessage
    {
        $order = Order::query()->find($this->orderId);

        return $order === null ? null : PushMessage::forInbox(InboxNotificationKind::PaymentFailed, ['number' => $order->order_number], LinkTargetType::Order, $order->public_id, $notifiable->locale);
    }
}
