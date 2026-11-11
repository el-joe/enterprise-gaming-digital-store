<?php

namespace App\Domains\Notifications\DTOs;

use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Notifications\Enums\InboxNotificationKind;

/**
 * One push notification (M-A18, ADR-042): the inbox kind, the short title and body of the inbox
 * entry rendered in the customer's locale, and string data the app routes on. Identifiers and the
 * order or ticket number only: never a code, PIN, secret, message body or amount.
 *
 * Data keys: `type` (the inbox kind), `category`, `link_type` (`order` or `ticket`), `link_value`
 * (the public id), and `order_id` or `ticket_id` (the same public id).
 */
final readonly class PushMessage
{
    /**
     * @param  array<string, string>  $data
     */
    public function __construct(
        public string $type,
        public string $title,
        public string $body,
        public array $data,
    ) {}

    /**
     * The push twin of an inbox entry: the same text as `GET /account/notifications` shows.
     *
     * @param  array<string, string>  $params
     */
    public static function forInbox(InboxNotificationKind $kind, array $params, LinkTargetType $linkType, string $publicId, ?string $locale): self
    {
        return new self(
            type: $kind->value,
            title: __($kind->title(), $params, $locale),
            body: __($kind->body(), $params, $locale),
            data: [
                'type' => $kind->value,
                'category' => $kind->category()->value,
                'link_type' => $linkType->value,
                'link_value' => $publicId,
                ($linkType === LinkTargetType::Ticket ? 'ticket_id' : 'order_id') => $publicId,
            ],
        );
    }

    /**
     * @return array{type: string, title: string, body: string, data: array<string, string>}
     */
    public function toArray(): array
    {
        return ['type' => $this->type, 'title' => $this->title, 'body' => $this->body, 'data' => $this->data];
    }

    /**
     * @param  array{type: string, title: string, body: string, data: array<string, string>}  $message
     */
    public static function fromArray(array $message): self
    {
        return new self($message['type'], $message['title'], $message['body'], array_map(strval(...), $message['data']));
    }
}
