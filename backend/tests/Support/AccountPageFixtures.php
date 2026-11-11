<?php

/*
 * Fixtures for the M13 account page tests.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Support\Enums\SupportTicketMessageAuthorType;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;

const ACCOUNT_PAGE_CANARY_CODE = 'CANARY-ACCT-PAGE-4242';

/**
 * A paid order of `$customer` for a listed storefront product, with one delivered unit whose code
 * (the canary) is in the vault.
 *
 * @return array{0: Order, 1: OrderItemUnit}
 */
function accountPageDeliveredOrder(User $customer, string $code = ACCOUNT_PAGE_CANARY_CODE, string $product = 'Steam Wallet $20'): array
{
    $order = paidOrderFor(storefrontVariant(storefrontProduct($product)), orderAttributes: ['user_id' => $customer->id]);
    OrderItem::query()->where('order_id', $order->id)->update(['name_snapshot' => $product]);
    $unit = deliveredUnitOf($order, $code);

    return [$order->fresh(), $unit->fresh()];
}

/**
 * A ticket of `$customer` with a customer message, a staff reply and an internal staff note.
 */
function accountPageTicket(User $customer, string $subject = 'Code not working on my region'): SupportTicket
{
    $ticket = SupportTicket::factory()->create(['user_id' => $customer->id, 'subject' => $subject]);
    SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'author_type' => SupportTicketMessageAuthorType::Customer, 'body' => 'My code says invalid.']);
    SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'author_type' => SupportTicketMessageAuthorType::Staff, 'body' => 'We are checking with the supplier.']);
    SupportTicketMessage::factory()->create(['support_ticket_id' => $ticket->id, 'author_type' => SupportTicketMessageAuthorType::Staff, 'body' => 'INTERNAL-NOTE-SECRET', 'is_internal' => true]);

    return $ticket->fresh();
}
