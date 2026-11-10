<?php

namespace App\Domains\Orders\Notifications;

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
 * and must also verify their email by one-time code before any reveal.
 */
class OrderDelivered extends Notification implements ShouldQueue
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = Order::query()->findOrFail($this->orderId);
        $partial = $order->status === OrderStatus::PartiallyFulfilled;

        $mail = (new MailMessage)
            ->subject($partial ? "Part of order {$order->order_number} is ready" : "Order {$order->order_number} is ready")
            ->line($partial
                ? 'Some items in your order are ready. Items we could not deliver will be refunded.'
                : 'Your order has been delivered.')
            ->line('For your security, codes are never sent by email. Open your order to view them.');

        return $mail->action('View your order', self::link($order));
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
