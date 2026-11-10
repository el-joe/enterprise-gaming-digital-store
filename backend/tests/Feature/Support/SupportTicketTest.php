<?php

/**
 * Support tickets for customers and staff, internal notes, and refund requests.
 */

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Support\Enums\RefundRequestStatus;
use App\Domains\Support\Enums\SupportTicketMessageAuthorType;
use App\Domains\Support\Enums\SupportTicketStatus;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Models\SupportTicketMessage;
use App\Domains\Support\Notifications\SupportTicketReplied;
use App\Http\Resources\Account\SupportTicketMessageResource;
use App\Http\Resources\Account\SupportTicketResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;

const INTERNAL_NOTE = 'INTERNAL-CANARY: customer looks like a chargeback risk';

beforeEach(function (): void {
    Notification::fake();
    $this->customer = User::factory()->create();
});

function openTicket(User $customer, array $extra = []): string
{
    return test()->actingAs($customer)->postJson('/api/v1/account/tickets', ['category' => 'order', 'subject' => 'Code not received', 'body' => 'My order shows delivered but I see nothing.', ...$extra])
        ->assertCreated()->json('data.ticket.public_id');
}

describe('customer flow', function (): void {
    it('opens a ticket, lists and shows it, and replies', function (): void {
        $order = Order::factory()->create(['user_id' => $this->customer->id]);
        $id = openTicket($this->customer, ['order_public_id' => $order->public_id]);

        $ticket = SupportTicket::query()->sole();
        expect($ticket->status)->toBe(SupportTicketStatus::Open)->and($ticket->order_id)->toBe($order->id)->and($ticket->ticket_number)->toStartWith('T-');

        $this->actingAs($this->customer)->getJson('/api/v1/account/tickets')->assertOk()->assertJsonCount(1, 'data')->assertJsonMissingPath('data.0.messages');
        $this->actingAs($this->customer)->postJson("/api/v1/account/tickets/{$id}/messages", ['body' => 'Any news?'])->assertCreated()
            ->assertJsonCount(2, 'data.ticket.messages')
            ->assertJsonPath('data.ticket.status', 'pending_staff')
            ->assertJsonPath('data.ticket.messages.1.from', 'customer');
    });

    it('hides other customers\' tickets and refuses someone else\'s order', function (): void {
        $id = openTicket(User::factory()->create());
        $foreignOrder = Order::factory()->create();

        $this->actingAs($this->customer)->getJson("/api/v1/account/tickets/{$id}")->assertNotFound();
        $this->actingAs($this->customer)->postJson("/api/v1/account/tickets/{$id}/messages", ['body' => 'hi'])->assertNotFound();
        $this->actingAs($this->customer)->getJson('/api/v1/account/tickets')->assertJsonCount(0, 'data');
        $this->actingAs($this->customer)->postJson('/api/v1/account/tickets', ['category' => 'order', 'subject' => 'Help', 'body' => 'x', 'order_public_id' => $foreignOrder->public_id])
            ->assertUnprocessable()->assertJsonValidationErrors('order_public_id');
    });

    it('refuses a reply on a closed ticket', function (): void {
        $id = openTicket($this->customer);
        SupportTicket::query()->update(['status' => SupportTicketStatus::Closed->value]);

        $this->actingAs($this->customer)->postJson("/api/v1/account/tickets/{$id}/messages", ['body' => 'Hello?'])->assertStatus(409)->assertJsonPath('code', 'ticket_closed');
    });
});

describe('internal notes are never visible to customers', function (): void {
    beforeEach(function (): void {
        $this->publicId = openTicket($this->customer);
        $this->ticket = SupportTicket::query()->sole();
        actingAsStaffWith(['support.view', 'support.reply']);
        $this->postJson("/admin/api/tickets/{$this->publicId}/messages", ['body' => INTERNAL_NOTE, 'is_internal' => true])->assertCreated();
        $this->postJson("/admin/api/tickets/{$this->publicId}/messages", ['body' => 'We are checking with the supplier.'])->assertCreated();
    });

    it('shows staff the note, marked internal', function (): void {
        $this->getJson("/admin/api/tickets/{$this->publicId}")->assertOk()
            ->assertJsonCount(3, 'data.messages')
            ->assertJsonPath('data.messages.1.is_internal', true)
            ->assertJsonPath('data.messages.1.body', INTERNAL_NOTE);
    });

    it('never returns the note to the customer, in the ticket or the list', function (): void {
        $show = $this->actingAs($this->customer)->getJson("/api/v1/account/tickets/{$this->publicId}")->assertOk()->assertJsonCount(2, 'data.ticket.messages');
        $list = $this->actingAs($this->customer)->getJson('/api/v1/account/tickets')->assertOk();
        $reply = $this->actingAs($this->customer)->postJson("/api/v1/account/tickets/{$this->publicId}/messages", ['body' => 'Thanks'])->assertCreated();

        foreach ([$show, $list, $reply] as $response) {
            expect($response->getContent())->not->toContain('INTERNAL-CANARY')->not->toContain('is_internal');
        }
    });

    it('filters notes in the customer resource itself, even when the relation holds them', function (): void {
        $ticket = $this->ticket->fresh()->load(['order', 'supportTicketMessages']);
        expect($ticket->supportTicketMessages->where('is_internal', true))->toHaveCount(1);

        $array = SupportTicketResource::make($ticket)->resolve(Request::create('/'));

        expect($array['messages'])->toHaveCount(2)
            ->and(json_encode($array))->not->toContain('INTERNAL-CANARY');

        $note = SupportTicketMessage::query()->where('is_internal', true)->sole();
        expect(SupportTicketMessageResource::make($note)->resolve(Request::create('/')))->toBe([]);
    });

    it('emails the customer for a public reply only, with a link and no message text', function (): void {
        Notification::assertSentTo($this->customer, SupportTicketReplied::class, function (SupportTicketReplied $notification): bool {
            $mail = $notification->toMail($this->customer);
            $text = json_encode($mail->toArray());

            return ! str_contains($text, 'INTERNAL-CANARY') && ! str_contains($text, 'checking with the supplier') && str_contains($text, $this->publicId);
        });
        Notification::assertSentToTimes($this->customer, SupportTicketReplied::class, 1);
        expect($this->ticket->fresh()->status)->toBe(SupportTicketStatus::PendingCustomer);
    });
});

describe('staff flow', function (): void {
    it('lists the queue, assigns to an agent who can reply and changes status and priority, audited', function (): void {
        $id = openTicket($this->customer);
        $agent = User::factory()->staff()->withTwoFactor()->create();
        $agent->givePermissionTo(Permission::findOrCreate('support.reply', 'web'));
        $lurker = User::factory()->staff()->withTwoFactor()->create();
        actingAsStaffWith(['support.view', 'support.manage']);

        $this->getJson('/admin/api/tickets?filter[status]=open')->assertOk()->assertJsonCount(1, 'data');
        $this->postJson("/admin/api/tickets/{$id}/assign", ['assigned_to' => $lurker->id])->assertUnprocessable()->assertJsonValidationErrors('assigned_to');
        $this->postJson("/admin/api/tickets/{$id}/assign", ['assigned_to' => $this->customer->id])->assertUnprocessable();
        $this->postJson("/admin/api/tickets/{$id}/assign", ['assigned_to' => $agent->id])->assertOk()->assertJsonPath('data.assigned_to.id', $agent->id);
        $this->patchJson("/admin/api/tickets/{$id}", ['status' => 'resolved', 'priority' => 'high'])->assertOk()
            ->assertJsonPath('data.status', 'resolved')->assertJsonPath('data.priority', 'high');

        expect(AuditLog::query()->whereIn('action', ['support.ticket.assigned', 'support.ticket.updated'])->count())->toBe(2);
    });

    it('runs the same number of queries for the agent queue whatever its length', function (): void {
        actingAsStaffWith(['support.view']);
        $count = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->getJson('/admin/api/tickets')->assertOk();

            return count(DB::getQueryLog());
        };

        SupportTicket::factory()->count(2)->create(['user_id' => User::factory()->create()->id, 'order_id' => Order::factory()->create()->id]);
        $few = $count();
        SupportTicket::factory()->count(5)->create(['user_id' => User::factory()->create()->id, 'assigned_to' => User::factory()->staff()->create()->id]);

        expect($count())->toBe($few);
    });

    it('enforces support permissions per action', function (): void {
        $id = openTicket($this->customer);

        actingAsStaffWith(['support.view']);
        $this->getJson("/admin/api/tickets/{$id}")->assertOk();
        $this->postJson("/admin/api/tickets/{$id}/messages", ['body' => 'x'])->assertForbidden();
        $this->patchJson("/admin/api/tickets/{$id}", ['status' => 'closed'])->assertForbidden();

        actingAsStaffWith([]);
        $this->getJson('/admin/api/tickets')->assertForbidden();
    });

    it('records staff replies as staff authored', function (): void {
        $id = openTicket($this->customer);
        $staff = actingAsStaffWith(['support.reply']);

        $this->postJson("/admin/api/tickets/{$id}/messages", ['body' => 'Hello'])->assertCreated();

        $message = SupportTicketMessage::query()->latest('id')->first();
        expect($message->author_type)->toBe(SupportTicketMessageAuthorType::Staff)->and($message->author_id)->toBe($staff->id);
    });
});

describe('refund requests', function (): void {
    beforeEach(function (): void {
        Queue::fake([FulfilOrder::class]);
        $gateway = gatewayFor('stripe');
        [$order, $payment] = orderAwaitingPayment($gateway, '20.00', units: 2, orderAttributes: ['user_id' => $this->customer->id]);
        app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        $machine = app(OrderStateMachine::class);
        $machine->transition($order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Delivered, Unit::Delivered]);
        $this->order = $machine->transition($order, OrderStatus::Completed, OrderActor::system());
    });

    it('lets a customer ask once per paid order of their own', function (): void {
        $this->actingAs($this->customer)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'The code was already redeemed.'])
            ->assertCreated()->assertJsonPath('data.refund_request.status', 'requested');
        $this->actingAs($this->customer)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'Asking again, please.'])
            ->assertStatus(409)->assertJsonPath('code', 'refund_request_open');
        $this->actingAs(User::factory()->create())->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'Not my order at all.'])
            ->assertUnprocessable();

        $this->actingAs($this->customer)->getJson('/api/v1/account/refund-requests')->assertOk()->assertJsonCount(1, 'data');
    });

    it('approves by refunding the chosen units through the refund flow, then completes once the refund succeeds', function (): void {
        Http::preventStrayRequests();
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(['id' => 're_rr', 'object' => 'refund', 'amount' => 1000, 'currency' => 'usd', 'status' => 'succeeded', 'metadata' => []])]);
        $this->actingAs($this->customer)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'The code was already redeemed.'])->assertCreated();
        $request = RefundRequest::query()->sole();
        $unit = OrderItemUnit::query()->where('order_id', $this->order->id)->orderBy('id')->first();

        actingAsStaffWith(['support.refund_requests', 'refunds.create']);
        $this->postJson("/admin/api/refund-requests/{$request->id}/approve", ['unit_ids' => [$unit->public_id], 'reason' => 'customer_request', 'note' => 'Verified with supplier'])
            ->assertOk()->assertJsonPath('data.refund.status', 'succeeded');

        $refund = Refund::query()->sole();
        expect($request->fresh()->refund_id)->toBe($refund->id)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded)
            ->and($request->fresh()->status)->toBe(RefundRequestStatus::Completed);

        $this->postJson("/admin/api/refund-requests/{$request->id}/reject", ['note' => 'Too late now'])->assertStatus(409);
    });

    it('rejects with a note, and approval needs refunds.create too', function (): void {
        $this->actingAs($this->customer)->postJson('/api/v1/account/refund-requests', ['order_public_id' => $this->order->public_id, 'reason' => 'Changed my mind about it.'])->assertCreated();
        $request = RefundRequest::query()->sole();
        $unit = OrderItemUnit::query()->where('order_id', $this->order->id)->first();

        actingAsStaffWith(['support.refund_requests']);
        $this->postJson("/admin/api/refund-requests/{$request->id}/approve", ['unit_ids' => [$unit->public_id], 'reason' => 'customer_request', 'note' => 'Approving it'])->assertForbidden();
        $this->postJson("/admin/api/refund-requests/{$request->id}/reject", ['note' => 'Delivered and revealed'])->assertOk()->assertJsonPath('data.status', 'rejected');

        expect(Refund::query()->count())->toBe(0)
            ->and(AuditLog::query()->where('action', 'support.refund_request.rejected')->sole()->reason)->toBe('Delivered and revealed');
    });
});
