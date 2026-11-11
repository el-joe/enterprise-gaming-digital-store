<?php

namespace App\Domains\Orders\Notifications;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Contracts\PushNotification;
use App\Domains\Notifications\DTOs\PushMessage;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * "Your order is ready": a secure link to reveal codes, never the code itself (ADR-009). Queued
 * with the order id only. Guests receive a signed link valid for `fulfillment.guest_link_hours`
 * and must also verify their email by one-time code before any reveal. Accounts also get a push
 * (M-A18) with the inbox text and the order's public id when their orders push preference is on.
 */
class OrderDelivered extends Notification implements PushNotification, ShouldQueue
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
        return app(NotificationPreferenceService::class)->channelsFor($notifiable, NotificationPreferenceCategory::Orders, [NotificationPreferenceChannel::Mail, NotificationPreferenceChannel::Database, NotificationPreferenceChannel::Push]);
    }

    /**
     * The inbox kind, stored as the notification type (M-A5).
     */
    public function databaseType(object $notifiable): string
    {
        return self::kind($this->order())->value;
    }

    /**
     * The inbox entry (M-A5): identifiers and the number only, never a code (ADR-009).
     *
     * @return array{kind: string, params: array<string, string>, link: array{type: string, value: string}}
     */
    public function toDatabase(object $notifiable): array
    {
        $order = $this->order();

        return ['kind' => self::kind($order)->value, 'params' => ['number' => $order->order_number], 'link' => ['type' => LinkTargetType::Order->value, 'value' => $order->public_id]];
    }

    /**
     * The push twin of the inbox entry (M-A18): number and public id only, never a code.
     */
    public function toPush(User $notifiable): ?PushMessage
    {
        $order = Order::query()->find($this->orderId);

        return $order === null ? null : PushMessage::forInbox(self::kind($order), ['number' => $order->order_number], LinkTargetType::Order, $order->public_id, $notifiable->locale);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order();
        $partial = $order->status === OrderStatus::PartiallyFulfilled;

        $mail = (new MailMessage)
            ->subject($partial ? "Part of order {$order->order_number} is ready" : "Order {$order->order_number} is ready")
            ->line($partial
                ? 'Some items in your order are ready. Items we could not deliver will be refunded.'
                : 'Your order has been delivered.')
            ->line('For your security, codes are never sent by email. Open your order to view them.');

        return $mail->action('View your order', self::link($order));
    }

    private static function kind(Order $order): InboxNotificationKind
    {
        return $order->status === OrderStatus::PartiallyFulfilled ? InboxNotificationKind::OrderPartiallyDelivered : InboxNotificationKind::OrderDelivered;
    }

    private function order(): Order
    {
        return Order::query()->findOrFail($this->orderId);
    }

    public static function link(Order $order): string
    {
        if ($order->user_id !== null) {
            return url('/account/orders/'.$order->public_id);
        }

        return URL::temporarySignedRoute(
            'api.v1.checkout.guest.orders.access',
            now()->addHours((int) config('fulfillment.guest_link_hours')),
            ['orderPublicId' => $order->public_id],
        );
    }
}
