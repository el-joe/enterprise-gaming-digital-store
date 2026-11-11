<?php

namespace App\Domains\Support\Notifications;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Contracts\PushNotification;
use App\Domains\Notifications\DTOs\PushMessage;
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
 * so no message body, and never an internal note, travels by email. Queued with the id only. The
 * push (M-A18) carries the same: the ticket number and its public id.
 */
class SupportTicketReplied extends Notification implements PushNotification, ShouldQueue
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
        return app(NotificationPreferenceService::class)->channelsFor($notifiable, NotificationPreferenceCategory::Support, [NotificationPreferenceChannel::Mail, NotificationPreferenceChannel::Database, NotificationPreferenceChannel::Push]);
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

    /**
     * The push twin of the inbox entry (M-A18): no message body.
     */
    public function toPush(User $notifiable): ?PushMessage
    {
        $ticket = SupportTicket::query()->find($this->ticketId);

        return $ticket === null ? null : PushMessage::forInbox(InboxNotificationKind::SupportTicketReplied, ['number' => $ticket->ticket_number], LinkTargetType::Ticket, $ticket->public_id, $notifiable->locale);
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
