<?php

namespace App\Domains\Notifications\Enums;

/**
 * What a customer inbox notification is about (M-A5, gap G6). The kind is stored as the
 * notification `type`; title and body are English source strings with `:placeholders`, rendered
 * in the reader's locale when the inbox is listed (lang/ar.json). Payloads hold identifiers and
 * an order or ticket number only, never a code, PIN or message body.
 */
enum InboxNotificationKind: string
{
    case OrderDelivered = 'order_delivered';
    case OrderPartiallyDelivered = 'order_partially_delivered';
    case OrderFulfilmentFailed = 'order_fulfilment_failed';
    case SupportTicketReplied = 'support_ticket_replied';

    public function category(): NotificationPreferenceCategory
    {
        return $this === self::SupportTicketReplied ? NotificationPreferenceCategory::Support : NotificationPreferenceCategory::Orders;
    }

    public function title(): string
    {
        return match ($this) {
            self::OrderDelivered => 'Order :number is ready',
            self::OrderPartiallyDelivered => 'Part of order :number is ready',
            self::OrderFulfilmentFailed => 'We could not complete order :number',
            self::SupportTicketReplied => 'Update on your support request :number',
        };
    }

    public function body(): string
    {
        return match ($this) {
            self::OrderDelivered => 'Your order has been delivered. Open it to view your codes.',
            self::OrderPartiallyDelivered => 'Some items in your order are ready. Items we could not deliver will be refunded.',
            self::OrderFulfilmentFailed => 'We were unable to deliver your order. A refund is being prepared.',
            self::SupportTicketReplied => 'Our support team has replied to your request.',
        };
    }
}
