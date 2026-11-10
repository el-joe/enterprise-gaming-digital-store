<?php

namespace App\Domains\Support\Notifications;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Notifications\Enums\InboxNotificationKind;
use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;
use App\Domains\Notifications\Services\NotificationPreferenceService;
use App\Domains\Support\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * "Support replied": the ticket number and a link only. The reply itself is read in the account,
 * so no message body, and never an internal note, travels by email. Queued with the id only.
 */
class SupportTicketReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $ticketId)
    {
        $this->onQueue('notifications');
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return app(NotificationPreferenceService::class)->channelsFor($notifiable, NotificationPreferenceCategory::Support, [NotificationPreferenceChannel::Mail, NotificationPreferenceChannel::Database]);
    }

    /**
     * The inbox kind, stored as the notification type (M-A5).
     */
    public function databaseType(object $notifiable): string
    {
        return InboxNotificationKind::SupportTicketReplied->value;
    }

    /**
     * The inbox entry (M-A5): identifiers and the number only, never a code (ADR-009).
     *
     * @return array{kind: string, params: array<string, string>, link: array{type: string, value: string}}
     */
    public function toDatabase(object $notifiable): array
    {
        $ticket = SupportTicket::query()->findOrFail($this->ticketId);

        return ['kind' => InboxNotificationKind::SupportTicketReplied->value, 'params' => ['number' => $ticket->ticket_number], 'link' => ['type' => LinkTargetType::Ticket->value, 'value' => $ticket->public_id]];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $ticket = SupportTicket::query()->findOrFail($this->ticketId);

        return (new MailMessage)
            ->subject("Update on your support request {$ticket->ticket_number}")
            ->line('Our support team has replied to your request.')
            ->action('View the conversation', url('/account/tickets/'.$ticket->public_id));
    }
}
