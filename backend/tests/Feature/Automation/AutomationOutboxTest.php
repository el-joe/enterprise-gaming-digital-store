<?php

/**
 * Architecture section 16: events written in the state change's transaction, delivered to n8n by
 * automation:flush-outbox with HMAC signatures, retry with backoff, and recorded failures.
 */

use App\Domains\Automation\Enums\AutomationOutboxStatus;
use App\Domains\Automation\Models\AutomationOutbox;
use App\Domains\Automation\Services\AutomationOutboxWriter;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Events\OrderStatusChanged;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Services\SupportTicketService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

const OUTBOUND_TEST_SECRET = 'outbound-test-secret-abcdef0123456789';

beforeEach(function (): void {
    Event::fake([OrderStatusChanged::class]);
    config([
        'automation.outbox.enabled' => true,
        'automation.outbox.webhook_url' => 'https://n8n.example.test/webhook/egs-events',
        'automation.outbox.secret' => OUTBOUND_TEST_SECRET,
        'automation.outbox.auth_token' => 'header-auth-token',
    ]);
});

function processingOrder(array $attributes = []): Order
{
    return paidOrderFor(sellableVariant('5'), orderAttributes: ['contact_email' => 'buyer@example.com', 'contact_phone' => '+15550001111', ...$attributes], status: OrderStatus::Processing);
}

it('writes nothing when the outbox is disabled', function (): void {
    config(['automation.outbox.enabled' => false]);
    $order = processingOrder();

    app(OrderStateMachine::class)->transition($order, OrderStatus::Completed, OrderActor::system());

    expect(AutomationOutbox::query()->count())->toBe(0);
});

it('writes subscribed order events with identifiers only', function (): void {
    $order = processingOrder();

    app(OrderStateMachine::class)->transition($order, OrderStatus::Completed, OrderActor::system());
    $failing = Order::factory()->create();
    app(OrderStateMachine::class)->transition($failing, OrderStatus::Failed, OrderActor::system());

    $events = AutomationOutbox::query()->orderBy('id')->get();
    expect($events->pluck('event_type')->all())->toBe([AutomationOutboxWriter::ORDER_DELIVERED, AutomationOutboxWriter::ORDER_FAILED])
        ->and($events->first()->status)->toBe(AutomationOutboxStatus::Pending)
        ->and($events->first()->payload)->toEqual([
            'order_public_id' => $order->public_id,
            'order_number' => $order->order_number,
            'status' => 'completed',
            'previous_status' => 'processing',
            'is_guest' => true,
            'locale' => 'en',
        ])
        ->and(json_encode($events->pluck('payload')))->not->toContain('buyer@example.com')->not->toContain('+15550001111');
});

it('writes no event for transitions n8n does not subscribe to', function (): void {
    $order = processingOrder();

    app(OrderStateMachine::class)->transition($order, OrderStatus::ManualReview, OrderActor::system());

    expect(AutomationOutbox::query()->count())->toBe(0);
});

it('writes the event in the transaction of the state change', function (): void {
    $order = processingOrder();

    try {
        DB::transaction(function () use ($order): void {
            app(OrderStateMachine::class)->transition($order, OrderStatus::Completed, OrderActor::system());

            throw new RuntimeException('rolled back after the transition');
        });
    } catch (RuntimeException) {
    }

    expect($order->refresh()->status)->toBe(OrderStatus::Processing)
        ->and(AutomationOutbox::query()->count())->toBe(0);
});

it('writes a ticket event for a public staff reply but not an internal note', function (): void {
    $staff = User::factory()->staff()->create();
    $ticket = SupportTicket::factory()->create(['user_id' => User::factory()->create()->id]);
    $service = app(SupportTicketService::class);

    $service->staffReply($staff, $ticket, 'Internal only', true);
    expect(AutomationOutbox::query()->count())->toBe(0);

    $service->staffReply($staff, $ticket, 'We have refunded you', false);
    $event = AutomationOutbox::query()->sole();

    expect($event->event_type)->toBe(AutomationOutboxWriter::TICKET_UPDATED)
        ->and($event->payload['ticket_public_id'])->toBe($ticket->public_id)
        ->and(json_encode($event->payload))->not->toContain('refunded you');
});

it('keeps events pending when delivery is not configured', function (): void {
    Http::fake();
    config(['automation.outbox.webhook_url' => '']);
    app(AutomationOutboxWriter::class)->record('order.created', ['order_public_id' => 'x']);

    $this->artisan('automation:flush-outbox')->expectsOutputToContain('not configured')->assertSuccessful();

    Http::assertNothingSent();
    expect(AutomationOutbox::query()->sole()->status)->toBe(AutomationOutboxStatus::Pending);
});

it('delivers a signed event that n8n can verify', function (): void {
    Http::fake(['n8n.example.test/*' => Http::response(['ok' => true], 202)]);
    $event = app(AutomationOutboxWriter::class)->record('order.created', ['order_public_id' => '01JAAAAAAAAAAAAAAAAAAAAAAA', 'order_number' => 'EGS-1']);

    $this->artisan('automation:flush-outbox')->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request) use ($event): bool {
        $body = $request->body();
        $timestamp = $request->header('X-Signature-Timestamp')[0];
        $decoded = json_decode($body, true);

        return $request->url() === 'https://n8n.example.test/webhook/egs-events'
            && hash_equals(hash_hmac('sha256', $timestamp.'.'.$body, OUTBOUND_TEST_SECRET), $request->header('X-Signature')[0])
            && abs((int) $timestamp - now()->getTimestamp()) <= 5
            && $request->header('X-EGS-Webhook-Token')[0] === 'header-auth-token'
            && $request->header('X-Event-Id')[0] === $event->event_id
            && $decoded['event_id'] === $event->event_id
            && $decoded['event_type'] === 'order.created'
            && $decoded['payload']['order_number'] === 'EGS-1';
    });

    $event->refresh();
    expect($event->status)->toBe(AutomationOutboxStatus::Delivered)
        ->and($event->attempts)->toBe(1)
        ->and($event->delivered_at)->not->toBeNull()
        ->and($event->last_http_status)->toBe(202);
});

it('retries a failed delivery with backoff and records why', function (): void {
    Http::fake(['n8n.example.test/*' => Http::response('upstream error with details', 500)]);
    $event = app(AutomationOutboxWriter::class)->record('order.failed', ['order_public_id' => 'x']);

    $this->artisan('automation:flush-outbox')->assertSuccessful();

    $event->refresh();
    expect($event->status)->toBe(AutomationOutboxStatus::Pending)
        ->and($event->attempts)->toBe(1)
        ->and($event->last_http_status)->toBe(500)
        ->and($event->last_error)->toBe('HTTP 500')
        ->and($event->next_attempt_at->getTimestamp())->toBe(now()->addSeconds(60)->getTimestamp());

    $this->artisan('automation:flush-outbox')->assertSuccessful();
    Http::assertSentCount(1);

    $this->travel(61)->seconds();
    $this->artisan('automation:flush-outbox')->assertSuccessful();
    Http::assertSentCount(2);
    expect($event->refresh()->attempts)->toBe(2)
        ->and($event->next_attempt_at->getTimestamp())->toBe(now()->addSeconds(300)->getTimestamp());
});

it('marks an event failed after the last retry and reports it to F08', function (): void {
    Http::fake(['n8n.example.test/*' => Http::response('', 503)]);
    $event = app(AutomationOutboxWriter::class)->record('payment.succeeded', ['order_public_id' => 'x']);
    $delays = config('automation.outbox.retry_delays_seconds');

    foreach ($delays as $delay) {
        $this->artisan('automation:flush-outbox')->assertSuccessful();
        $this->travel($delay + 1)->seconds();
    }

    $this->artisan('automation:flush-outbox')->assertSuccessful();

    $event->refresh();
    expect($event->status)->toBe(AutomationOutboxStatus::Failed)
        ->and($event->attempts)->toBe(count($delays) + 1)
        ->and($event->failed_at)->not->toBeNull()
        ->and($event->last_error)->toBe('HTTP 503');
    Http::assertSentCount(count($delays) + 1);

    enableAutomationWorkflows(['F08']);
    ['token' => $token] = automationCredential();
    automationGet($token, 'monitor/webhooks?window=15', ['workflow' => 'F08'])
        ->assertOk()
        ->assertJsonPath('data.alert', true)
        ->assertJsonPath('data.outbox_failed', 1);
});

it('treats a connection failure as retryable', function (): void {
    Http::fake(['n8n.example.test/*' => Http::failedConnection()]);
    $event = app(AutomationOutboxWriter::class)->record('order.created', ['order_public_id' => 'x']);

    $this->artisan('automation:flush-outbox')->assertSuccessful();

    expect($event->refresh()->status)->toBe(AutomationOutboxStatus::Pending)
        ->and($event->attempts)->toBe(1)
        ->and($event->last_http_status)->toBeNull()
        ->and($event->last_error)->toBe('connection failed or timed out');
});

it('skips events another runner has leased', function (): void {
    Http::fake(['n8n.example.test/*' => Http::response('', 200)]);
    $leased = app(AutomationOutboxWriter::class)->record('order.created', ['order_public_id' => 'a']);
    $leased->forceFill(['next_attempt_at' => now()->addMinutes(2)])->save();
    app(AutomationOutboxWriter::class)->record('order.created', ['order_public_id' => 'b']);

    $this->artisan('automation:flush-outbox')->assertSuccessful();

    Http::assertSentCount(1);
    expect($leased->refresh()->status)->toBe(AutomationOutboxStatus::Pending);
});
