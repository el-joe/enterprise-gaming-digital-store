<?php

namespace App\Domains\Orders\Notifications;

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
        return ['mail'];
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
