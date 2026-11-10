<?php

namespace App\Domains\Orders\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Sent synchronously so the one-time code never sits in a queue payload. It carries the
 * verification code only, never a digital code.
 */
class GuestOrderAccessCode extends Notification
{
    public function __construct(
        public readonly string $code,
        public readonly string $orderNumber,
        public readonly int $minutes,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your order verification code')
            ->line("Use this code to verify your email for order {$this->orderNumber}: {$this->code}")
            ->line("The code expires in {$this->minutes} minutes. If you did not place this order, ignore this email.");
    }
}
