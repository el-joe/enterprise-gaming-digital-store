<?php

/**
 * Contract fixtures (M-A7, G11): the customer account under /api/v1/mobile/account (sections 4,
 * 4a, 4b, 4c) for a bearer token. Fixtures land in docs/api-fixtures/{orders,code_reveal,wallet,
 * reviews,support,notifications,account,security}.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItem;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Notifications\SupportTicketReplied;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    contractSetUp();
    $this->customer = User::factory()->create(['name' => 'Sara Customer', 'email' => 'sara@example.com', 'email_verified_at' => now()]);
    $this->access = mobileSignIn($this->customer)['access_token'];
});

/**
 * A call to /api/v1/mobile/account/{path} with the customer's bearer token.
 *
 * @param  array<string, mixed>  $body
 */
function contractAccount(string $method, string $path, array $body = []): TestResponse
{
    return mobileCall($method, 'account/'.ltrim($path, '/'), $body, test()->access);
}

/**
 * A paid order of the customer with one delivered unit holding the canary code.
 *
 * @return array{0: Order, 1: OrderItemUnit}
 */
function contractPaidOrder(User $customer, string $number = 'BX-1001'): array
{
    $order = contractDecorateOrder(paidOrderFor(contractVariant(), unitPrice: '10', orderAttributes: ['user_id' => $customer->id, 'completed_at' => now()], status: OrderStatus::Completed), $number);

    return [$order, contractCanaryUnit($order)];
}

describe('orders and reveal', function (): void {
    beforeEach(function (): void {
        [$this->order, $this->unit] = contractPaidOrder($this->customer);
        $this->travel(1)->minutes();
        orderAwaitingPayment(gatewayFor('stripe'), '15.00', orderAttributes: ['user_id' => $this->customer->id, 'order_number' => 'BX-1002']);
        $this->travelTo(CONTRACT_NOW);
    });

    it('lists and shows orders', function (): void {
        contractFixture('orders/list_ok', contractAccount('GET', 'orders?per_page=20'));
        contractFixture('orders/list_filtered', contractAccount('GET', 'orders?status=completed&from=2026-11-01&to=2026-11-10'))->assertOk();
        contractFixture('orders/list_validation_failed', contractAccount('GET', 'orders?status=lost')->assertUnprocessable());
        contractFixture('orders/detail_ok', contractAccount('GET', "orders/{$this->order->public_id}")->assertOk());
        contractFixture('orders/detail_not_found', contractAccount('GET', 'orders/01JC000000000000000000000Z')->assertNotFound());
    });

    it('asks for recent authentication, confirms the password and reveals the canary', function (): void {
        $path = "orders/{$this->order->public_id}/units/{$this->unit->public_id}/reveal";

        contractFixture('code_reveal/reveal_recent_auth_required', contractAccount('POST', $path)->assertStatus(423)->assertJsonPath('code', 'recent_auth_required'));
        contractFixture('code_reveal/confirm_password_invalid', contractAccount('POST', 'confirm-password', ['password' => 'wrong-password'])->assertUnprocessable());
        contractFixture('code_reveal/confirm_password_ok', contractAccount('POST', 'confirm-password', ['password' => 'password'])->assertOk());
        contractFixture('code_reveal/reveal_ok', contractAccount('POST', $path)->assertOk()->assertJsonPath('data.code', CONTRACT_CANARY_CODE));
        contractFixture('code_reveal/reveal_not_found', contractAccount('POST', "orders/{$this->order->public_id}/units/01JC000000000000000000000Z/reveal")->assertNotFound());

        $response = contractAccount('POST', $path);
        while ($response->status() === 200) {
            $response = contractAccount('POST', $path);
        }
        contractFixture('code_reveal/reveal_rate_limited', $response->assertTooManyRequests());
    });
});

describe('wallet, reviews, tickets and refund requests', function (): void {
    it('reads the wallet and its statement', function (): void {
        fundWallet($this->customer, '25', 'contract:refund-1');

        contractFixture('wallet/wallet_ok', contractAccount('GET', 'wallet')->assertOk());
        contractFixture('wallet/transactions_ok', contractAccount('GET', 'wallet/transactions')->assertOk());
    });

    it('reads an empty wallet', function (): void {
        contractFixture('wallet/wallet_empty', contractAccount('GET', 'wallet')->assertOk());
    });

    it('lists eligible items, submits a review and refuses a second one', function (): void {
        [$order] = contractPaidOrder($this->customer);
        $itemId = OrderItem::query()->where('order_id', $order->id)->sole()->id;

        contractFixture('reviews/eligible_ok', contractAccount('GET', 'reviews/eligible')->assertOk());
        contractFixture('reviews/create_created', contractAccount('POST', 'reviews', ['order_item_id' => $itemId, 'rating' => 5, 'title' => 'Instant', 'body' => 'Code arrived in a minute.'])->assertCreated());
        contractFixture('reviews/create_exists', contractAccount('POST', 'reviews', ['order_item_id' => $itemId, 'rating' => 4])->assertConflict()->assertJsonPath('code', 'review_exists'));
        contractFixture('reviews/create_not_eligible', contractAccount('POST', 'reviews', ['order_item_id' => 999999, 'rating' => 4])->assertUnprocessable()->assertJsonPath('code', 'review_not_eligible'));
        contractFixture('reviews/list_ok', contractAccount('GET', 'reviews')->assertOk());
    });

    it('refuses a review from an unverified email', function (): void {
        $this->customer->forceFill(['email_verified_at' => null])->save();

        contractFixture('reviews/create_email_unverified', contractAccount('POST', 'reviews', ['order_item_id' => 1, 'rating' => 4])->assertForbidden()->assertJsonPath('code', 'email_unverified'));
    });

    it('opens, reads and answers a ticket, and refuses a closed one', function (): void {
        [$order] = contractPaidOrder($this->customer);

        $created = contractAccount('POST', 'tickets', ['category' => 'order', 'subject' => 'Code missing', 'body' => 'Nothing arrived.', 'order_public_id' => $order->public_id])->assertCreated();
        contractFixture('support/ticket_created', $created);
        $ticket = $created->json('data.ticket.public_id');

        contractFixture('support/ticket_validation_failed', contractAccount('POST', 'tickets', ['category' => 'billing', 'subject' => '', 'body' => ''])->assertUnprocessable());
        contractFixture('support/message_created', contractAccount('POST', "tickets/{$ticket}/messages", ['body' => 'Any news?'])->assertCreated());
        contractFixture('support/tickets_list', contractAccount('GET', 'tickets')->assertOk());
        contractFixture('support/ticket_detail', contractAccount('GET', "tickets/{$ticket}")->assertOk());
        contractFixture('support/ticket_not_found', contractAccount('GET', 'tickets/01JC000000000000000000000Z')->assertNotFound());

        SupportTicket::query()->update(['status' => SupportTicketStatus::Closed->value]);
        contractFixture('support/message_ticket_closed', contractAccount('POST', "tickets/{$ticket}/messages", ['body' => 'Hello?'])->assertConflict()->assertJsonPath('code', 'ticket_closed'));
    });

    it('requests a refund and refuses a second open request', function (): void {
        [$order] = contractPaidOrder($this->customer);

        contractFixture('support/refund_request_created', contractAccount('POST', 'refund-requests', ['order_public_id' => $order->public_id, 'reason' => 'The code was already redeemed.'])->assertCreated());
        contractFixture('support/refund_request_open', contractAccount('POST', 'refund-requests', ['order_public_id' => $order->public_id, 'reason' => 'Asking again, please.'])->assertConflict()->assertJsonPath('code', 'refund_request_open'));
        contractFixture('support/refund_requests_list', contractAccount('GET', 'refund-requests')->assertOk());
    });
});

describe('notifications', function (): void {
    it('lists, counts and marks the inbox, and reads and updates preferences', function (): void {
        [$order] = contractPaidOrder($this->customer);
        $this->customer->notify(new OrderDelivered($order->id));
        $this->travel(1)->minutes();
        $this->customer->notify(new SupportTicketReplied(SupportTicket::factory()->create(['user_id' => $this->customer->id, 'subject' => 'Code missing', 'ticket_number' => 'T-261110-FIXTURE1'])->id));
        $this->travelTo(CONTRACT_NOW);

        $list = contractAccount('GET', 'notifications')->assertOk();
        contractFixture('notifications/list_ok', $list);
        contractFixture('notifications/unread_count_ok', contractAccount('GET', 'notifications/unread-count')->assertOk());
        contractFixture('notifications/mark_read_ok', contractAccount('POST', 'notifications/'.$list->json('data.0.id').'/read')->assertOk());
        contractFixture('notifications/mark_read_not_found', contractAccount('POST', 'notifications/00000000-0000-4000-8000-000000000999/read')->assertNotFound());
        contractFixture('notifications/read_all_ok', contractAccount('POST', 'notifications/read-all')->assertOk());

        contractFixture('notifications/preferences_ok', contractAccount('GET', 'notification-preferences')->assertOk());
        contractFixture('notifications/preferences_updated', contractAccount('PATCH', 'notification-preferences', ['preferences' => [['category' => 'marketing', 'channel' => 'mail', 'enabled' => true], ['category' => 'orders', 'channel' => 'sms', 'enabled' => true]]])->assertOk());
        contractFixture('notifications/preferences_mandatory', contractAccount('PATCH', 'notification-preferences', ['preferences' => [['category' => 'orders', 'channel' => 'mail', 'enabled' => false]]])
            ->assertUnprocessable()->assertJsonPath('code', 'notification_preference_mandatory'));
    });
});

describe('profile, password, devices and verification', function (): void {
    it('reads and updates the profile', function (): void {
        contractFixture('account/profile_ok', contractAccount('GET', 'profile')->assertOk());
        contractFixture('account/profile_updated', contractAccount('PATCH', 'profile', ['name' => 'Sara C.', 'locale' => 'ar', 'phone' => '+20 100 123 4567'])->assertOk());
        contractFixture('account/profile_validation_failed', contractAccount('PATCH', 'profile', ['locale' => 'fr', 'phone' => 'call me'])->assertUnprocessable());
    });

    it('changes the password', function (): void {
        Http::fake(['api.pwnedpasswords.com/*' => Http::response('')]);
        mobileSignIn($this->customer, 'Tablet');

        contractFixture('account/password_validation_failed', contractAccount('PUT', 'password', ['current_password' => 'wrong-password', 'password' => 'short', 'password_confirmation' => 'short'])->assertUnprocessable());
        contractFixture('account/password_updated', contractAccount('PUT', 'password', ['current_password' => 'password', 'password' => 'a-much-longer-passphrase', 'password_confirmation' => 'a-much-longer-passphrase'])->assertOk());
    });

    it('lists devices, revokes another and refuses the current one', function (): void {
        $this->travel(1)->minutes();
        mobileSignIn($this->customer, 'Tablet');
        $this->travelTo(CONTRACT_NOW);

        $devices = contractAccount('GET', 'devices')->assertOk();
        contractFixture('account/devices_ok', $devices);
        $current = collect($devices->json('data.devices'))->firstWhere('current', true)['id'];
        $other = collect($devices->json('data.devices'))->firstWhere('current', false)['id'];

        contractFixture('account/device_current', contractAccount('DELETE', "devices/{$current}")->assertConflict()->assertJsonPath('code', 'current_device'));
        contractFixture('account/device_revoked', contractAccount('DELETE', "devices/{$other}")->assertOk());
        contractFixture('account/device_not_found', contractAccount('DELETE', 'devices/unknown-device')->assertNotFound());
    });

    it('resends the verification email', function (): void {
        $this->customer->forceFill(['email_verified_at' => null])->save();

        contractFixture('account/verification_notification_ok', contractAccount('POST', 'email/verification-notification')->assertOk());
    });
});

describe('two-factor security', function (): void {
    it('enrols, confirms and disables two-factor authentication', function (): void {
        contractFixture('security/two_factor_enable_invalid_password', contractAccount('POST', 'security/two-factor', ['current_password' => 'wrong-password'])->assertUnprocessable());
        $secret = contractAccount('POST', 'security/two-factor', ['current_password' => 'password'])->assertOk();
        contractFixture('security/two_factor_enable_ok', $secret);

        contractFixture('security/two_factor_confirm_invalid', contractAccount('POST', 'security/two-factor/confirm', ['code' => '000000'])->assertUnprocessable());
        contractFixture('security/two_factor_confirm_ok', contractAccount('POST', 'security/two-factor/confirm', ['code' => mobileTotp($secret->json('data.secret'))])->assertOk());
        contractFixture('security/two_factor_already_enabled', contractAccount('POST', 'security/two-factor', ['current_password' => 'password'])->assertConflict());
        contractFixture('security/two_factor_disable_ok', contractAccount('DELETE', 'security/two-factor', ['current_password' => 'password'])->assertOk());
    });
});
