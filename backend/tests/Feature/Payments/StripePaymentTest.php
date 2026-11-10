<?php

/**
 * Stripe adapter and the shared payment-state rules, through the real webhook endpoint and the
 * official SDK on a faked HTTP transport. Mocked; awaiting sandbox verification.
 */

use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentDisputeStatus;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\PaymentTransactionType;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Exceptions\GatewayNotConfigured;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentDispute;
use App\Domains\Payments\Models\PaymentGatewayCredential;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\ReconciliationItem;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentAttemptService;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Shared\ValueObjects\Money;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\Request;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->gateway = gatewayFor('stripe');
    [$this->order, $this->payment] = orderAwaitingPayment($this->gateway);
});

function intentPaid(string $id = 'pi_fixture', int $amount = 2000, string $currency = 'usd'): string
{
    return stripeEvent('payment_intent.succeeded', stripeIntent($id, 'succeeded', $amount, $currency));
}

describe('webhook signatures', function (): void {
    it('accepts a valid signature, re-reads the intent and marks the order paid, which starts fulfilment', function (): void {
        fakeStripe();
        $payload = intentPaid();

        postStripeWebhook($payload, stripeSignature($payload))->assertOk()->assertJsonPath('duplicate', false);

        $order = $this->order->fresh();
        expect($order->status)->toBe(OrderStatus::Paid)
            ->and($order->primary_payment_id)->toBe($this->payment->id)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
            ->and(PaymentWebhook::query()->sole()->status)->toBe(WebhookStatus::Processed)
            ->and(PaymentTransaction::query()->where('type', PaymentTransactionType::Capture)->count())->toBe(1);

        Queue::assertPushed(FulfilOrder::class, fn (FulfilOrder $job): bool => $job->orderId === $order->id);
        Http::assertSent(fn (Request $request): bool => $request->method() === 'GET' && str_contains($request->url(), '/v1/payment_intents/pi_fixture'));
    });

    it('rejects a wrong, a stale and a missing signature without storing or applying anything', function (?string $signature): void {
        fakeStripe();
        $payload = intentPaid();

        postStripeWebhook($payload, $signature === null ? null : str_replace('{payload}', $payload, $signature))
            ->assertStatus(400)->assertJsonPath('code', 'invalid_signature');

        expect(PaymentWebhook::query()->exists())->toBeFalse()
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and(SecurityEvent::query()->where('type', 'payments.webhook_signature_invalid')->count())->toBe(1);
        Http::assertNothingSent();
    })->with([
        'wrong secret' => fn (): string => stripeSignature(intentPaid(), secret: 'whsec_other'),
        'stale timestamp' => fn (): string => stripeSignature(intentPaid(), time() - 600),
        'missing header' => null,
    ]);

    it('rejects a valid signature over a different body (raw body is what is signed)', function (): void {
        fakeStripe();
        $payload = intentPaid();
        $tampered = str_replace('"amount":2000', '"amount":1', $payload);

        postStripeWebhook($tampered, stripeSignature($payload))->assertStatus(400);

        expect(PaymentWebhook::query()->exists())->toBeFalse();
    });
});

describe('idempotency and ordering', function (): void {
    it('stores a duplicate delivery once and processes it once', function (): void {
        fakeStripe();
        $payload = intentPaid(id: 'pi_fixture');
        $payload = str_replace('"id":"evt_', '"id":"evt_dup', $payload);

        postStripeWebhook($payload, stripeSignature($payload))->assertOk()->assertJsonPath('duplicate', false);
        postStripeWebhook($payload, stripeSignature($payload))->assertOk()->assertJsonPath('duplicate', true);

        expect(PaymentWebhook::query()->count())->toBe(1)
            ->and(OrderEvent::query()->where('order_id', $this->order->id)->where('to_status', OrderStatus::Paid)->count())->toBe(1);
        Http::assertSentCount(1);
    });

    it('marks paid once when two distinct events describe the same success', function (): void {
        fakeStripe();
        $first = intentPaid();
        $second = stripeEvent('payment_intent.succeeded', stripeIntent());

        postStripeWebhook($first, stripeSignature($first))->assertOk();
        postStripeWebhook($second, stripeSignature($second))->assertOk();

        expect(PaymentWebhook::query()->count())->toBe(2)
            ->and(OrderEvent::query()->where('order_id', $this->order->id)->where('to_status', OrderStatus::Paid)->count())->toBe(1)
            ->and(PaymentTransaction::query()->count())->toBe(1);
    });

    it('ignores a failure that arrives after success, because the re-read still says succeeded', function (): void {
        fakeStripe();
        $paid = intentPaid();
        $failed = stripeEvent('payment_intent.payment_failed', stripeIntent(status: 'requires_payment_method', extra: ['last_payment_error' => ['code' => 'card_declined']]));

        postStripeWebhook($paid, stripeSignature($paid))->assertOk();
        postStripeWebhook($failed, stripeSignature($failed))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('never lets a failed state overwrite a settled payment, even when applied directly', function (): void {
        $applier = app(PaymentStateApplier::class);
        $applier->apply($this->payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        $applier->apply($this->payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Failed, failureCode: 'card_declined'), OrderActor::webhook());

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('handles a refund event that arrives before the success event: the payment is re-verified first', function (): void {
        fakeStripe();
        $refundFirst = stripeEvent('charge.refunded', ['id' => 'ch_pi_fixture', 'object' => 'charge', 'payment_intent' => 'pi_fixture', 'amount_refunded' => 0]);
        postStripeWebhook($refundFirst, stripeSignature($refundFirst))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);

        $paidLater = intentPaid();
        postStripeWebhook($paidLater, stripeSignature($paidLater))->assertOk();

        expect(OrderEvent::query()->where('order_id', $this->order->id)->where('to_status', OrderStatus::Paid)->count())->toBe(1);
    });
});

describe('verification rules', function (): void {
    it('sends an amount mismatch to manual_review and never marks it paid', function (): void {
        fakeStripe(Http::response(stripeIntent(amountReceived: 1999)));
        $payload = intentPaid(amount: 1999);

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        $order = $this->order->fresh();
        expect($order->status)->toBe(OrderStatus::ManualReview)
            ->and($order->paid_at)->toBeNull()
            ->and($this->payment->fresh()->failure_code)->toBe('amount_mismatch');
        Queue::assertNotPushed(FulfilOrder::class);
    });

    it('sends a currency mismatch to manual_review', function (): void {
        fakeStripe(Http::response(stripeIntent(currency: 'eur')));
        $payload = intentPaid(currency: 'eur');

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::ManualReview);
        Queue::assertNotPushed(FulfilOrder::class);
    });

    it('trusts the re-read, not the webhook body: a body claiming success while the provider says processing', function (): void {
        fakeStripe(Http::response(stripeIntent(status: 'processing')));
        $payload = intentPaid();

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::PaymentProcessing)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Processing);
    });

    it('sends a late payment on a cancelled order to manual_review, never to fulfilment', function (): void {
        app(OrderStateMachine::class)->transition($this->order, OrderStatus::Cancelled, OrderActor::system(), 'Expired.');
        fakeStripe();
        $payload = intentPaid();

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::ManualReview);
        Queue::assertNotPushed(FulfilOrder::class);
    });

    it('records a second successful payment as duplicate_capture and refunds it in full', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents/pi_second*' => Http::response(stripeIntent('pi_second'))]);
        $second = Payment::factory()->create(['order_id' => $this->order->id, 'payment_gateway_id' => $this->gateway->id, 'attempt_no' => 2, 'status' => PaymentStatus::RequiresAction, 'provider_payment_id' => 'pi_second', 'amount' => '20.00', 'base_amount' => '20.00']);

        foreach ([intentPaid(), intentPaid('pi_second')] as $payload) {
            postStripeWebhook($payload, stripeSignature($payload))->assertOk();
        }

        $refund = Refund::query()->sole();
        expect($second->fresh()->status)->toBe(PaymentStatus::DuplicateCapture)
            ->and($this->order->fresh()->primary_payment_id)->toBe($this->payment->id)
            ->and($refund->payment_id)->toBe($second->id)
            ->and($refund->reason)->toBe(RefundReason::Duplicate)
            ->and($refund->amount)->toBe('20.000000')
            ->and($refund->status)->toBe(RefundStatus::Succeeded);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/refunds') && $request->hasHeader('Idempotency-Key', 'refund:'.$refund->public_id));
    });

    it('marks a declined payment failed and keeps the order open for another attempt', function (): void {
        fakeStripe(Http::response(stripeIntent(status: 'requires_payment_method', extra: ['last_payment_error' => ['code' => 'card_declined', 'message' => 'Declined']])));
        $payload = stripeEvent('payment_intent.payment_failed', stripeIntent(status: 'requires_payment_method'));

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Failed)
            ->and($this->payment->fresh()->failure_code)->toBe('card_declined')
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });

    it('fails the order when the last permitted attempt fails', function (): void {
        Payment::factory()->count(4)->sequence(fn ($sequence): array => ['attempt_no' => $sequence->index + 2])->create(['order_id' => $this->order->id, 'payment_gateway_id' => $this->gateway->id, 'status' => PaymentStatus::Failed]);
        fakeStripe(Http::response(stripeIntent(status: 'canceled')));
        $payload = stripeEvent('payment_intent.canceled', stripeIntent(status: 'canceled'));

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Failed);
    });

    it('changes nothing when the provider times out during verification, and recovers on reconciliation', function (): void {
        $reads = 0;
        fakeStripe(function () use (&$reads) {
            return $reads++ === 0
                ? Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received')
                : Http::response(stripeIntent());
        });
        $payload = intentPaid();

        postStripeWebhook($payload, stripeSignature($payload))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and(PaymentWebhook::query()->sole()->status)->toBe(WebhookStatus::Failed)
            ->and($this->payment->fresh()->next_check_at)->not->toBeNull();

        $this->travel(2)->hours();
        $this->artisan('payments:reconcile-open')->assertSuccessful();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
            ->and(PaymentWebhook::query()->sole()->status)->toBe(WebhookStatus::Processed);
    });

    it('queues the GA4 purchase event once, however many times success is applied', function (): void {
        $applier = app(PaymentStateApplier::class);

        foreach (range(1, 3) as $repeat) {
            $applier->apply($this->payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        }

        expect(AnalyticsEventDispatch::query()->where('order_id', $this->order->id)->count())->toBe(1)
            ->and(AnalyticsEventDispatch::query()->sole()->dedupe_key)->toBe('purchase:'.$this->order->order_number)
            ->and(json_encode(AnalyticsEventDispatch::query()->sole()->payload))->not->toContain($this->order->contact_email);
    });
});

describe('browser return', function (): void {
    beforeEach(function (): void {
        $this->withSession([OrderStatusReader::SESSION_KEY => [$this->order->public_id]]);
    });

    it('does not mark paid on return alone: without a provider success the order stays unpaid', function (): void {
        fakeStripe(Http::response(stripeIntent(status: 'requires_action')));

        $this->postJson("/api/v1/checkout/orders/{$this->order->public_id}/verify-payment")
            ->assertOk()->assertJsonPath('data.status', 'pending_payment')->assertJsonPath('data.payment_status', 'unpaid');

        expect($this->order->fresh()->paid_at)->toBeNull();
    });

    it('marks paid on return only because the server-side check confirmed it, with no webhook at all', function (): void {
        fakeStripe();

        $this->postJson("/api/v1/checkout/orders/{$this->order->public_id}/verify-payment")
            ->assertOk()->assertJsonPath('data.status', 'paid');

        expect(PaymentWebhook::query()->exists())->toBeFalse();
    });

    it('hides another session\'s guest order', function (): void {
        $this->withSession([OrderStatusReader::SESSION_KEY => []]);

        $this->postJson("/api/v1/checkout/orders/{$this->order->public_id}/verify-payment")->assertNotFound();
    });
});

describe('payment attempts', function (): void {
    it('creates the intent with the snapshot amount in minor units and the pay_create idempotency key', function (): void {
        $this->payment->delete();
        fakeStripe();

        $session = app(PaymentAttemptService::class)->start($this->order->public_id);

        $payment = Payment::query()->sole();
        expect($session->next['client_secret'])->toBe('pi_fixture_secret_fixture')
            ->and($payment->provider_payment_id)->toBe('pi_fixture')
            ->and($payment->amount)->toBe($this->order->payment_total)
            ->and($payment->rate)->toBe($this->order->payment_rate);

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/payment_intents')
            && $request->hasHeader('Idempotency-Key', 'pay_create:'.$payment->public_id)
            && str_contains($request->body(), 'amount=2000')
            && str_contains($request->body(), 'currency=usd')
            && str_contains($request->body(), 'automatic_payment_methods[enabled]=true')
            && ! str_contains($request->body(), 'sk_test'));
    });

    it('expires an attempt whose create timed out, without retrying blindly', function (): void {
        $this->payment->delete();
        fakeStripe(overrides: ['api.stripe.com/v1/payment_intents' => Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received')]);

        expect(fn () => app(PaymentAttemptService::class)->start($this->order->public_id))->toThrow(GatewayNotConfigured::class);

        expect(Payment::query()->sole()->status)->toBe(PaymentStatus::Expired)
            ->and(Payment::query()->sole()->failure_code)->toBe('create_unknown');
        Http::assertSentCount(1);
    });

    it('refuses a key that does not match the gateway mode', function (): void {
        PaymentGatewayCredential::query()->sole()->forceFill(['credentials' => ['secret_key' => 'sk_live_wrong']])->save();
        $this->payment->delete();

        expect(fn () => app(PaymentAttemptService::class)->start($this->order->public_id))->toThrow(GatewayNotConfigured::class);
        Http::assertNothingSent();
    });
});

describe('secrets and limits', function (): void {
    it('keeps the secret key, the signing secret and the signature out of logs and stored webhook headers', function (): void {
        $messages = [];
        Event::listen(MessageLogged::class, function ($event) use (&$messages): void {
            $messages[] = $event->message.json_encode($event->context);
        });
        $this->payment->delete();
        fakeStripe();
        app(PaymentAttemptService::class)->start($this->order->public_id);
        $payload = intentPaid();
        $signature = stripeSignature($payload);

        postStripeWebhook($payload, $signature)->assertOk();

        $logged = implode("\n", $messages);
        expect($messages)->not->toBeEmpty()
            ->and($logged)->not->toContain('sk_test_fixture')
            ->and($logged)->not->toContain(STRIPE_WEBHOOK_SECRET)
            ->and($logged)->not->toContain('_secret_fixture')
            ->and(json_encode(PaymentWebhook::query()->sole()->headers))->not->toContain($signature)
            ->and(json_encode(PaymentGatewayCredential::query()->sole()->toArray()))->not->toContain('sk_test_fixture');
    });

    it('rate limits the webhook endpoint', function (): void {
        RateLimiter::for('webhooks', fn () => Limit::perMinute(2)->by('test'));

        postStripeWebhook('{}', 'bad')->assertStatus(400);
        postStripeWebhook('{}', 'bad')->assertStatus(400);
        postStripeWebhook('{}', 'bad')->assertStatus(429);
    });
});

describe('disputes and reconciliation', function (): void {
    it('records a dispute and flags the order without changing its status', function (): void {
        fakeStripe();
        $paid = intentPaid();
        postStripeWebhook($paid, stripeSignature($paid))->assertOk();
        $dispute = stripeEvent('charge.dispute.created', ['id' => 'dp_1', 'object' => 'dispute', 'payment_intent' => 'pi_fixture', 'amount' => 2000, 'currency' => 'usd', 'status' => 'needs_response', 'reason' => 'fraudulent', 'evidence_details' => ['due_by' => time() + 86400]]);

        postStripeWebhook($dispute, stripeSignature($dispute))->assertOk();

        $row = PaymentDispute::query()->sole();
        expect($row->status)->toBe(PaymentDisputeStatus::Open)
            ->and($row->amount)->toBe('20.000000')
            ->and($this->order->fresh()->has_open_dispute)->toBeTrue()
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);

        $won = stripeEvent('charge.dispute.closed', ['id' => 'dp_1', 'object' => 'dispute', 'payment_intent' => 'pi_fixture', 'amount' => 2000, 'currency' => 'usd', 'status' => 'won']);
        postStripeWebhook($won, stripeSignature($won))->assertOk();

        expect($row->fresh()->status)->toBe(PaymentDisputeStatus::Won)->and($this->order->fresh()->has_open_dispute)->toBeFalse();
    });

    it('records a daily mismatch when the provider no longer agrees with a settled payment, and changes nothing', function (): void {
        app(PaymentStateApplier::class)->apply($this->payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        fakeStripe(Http::response(stripeIntent(status: 'canceled')));
        $this->travel(1)->hours();

        $this->artisan('payments:reconcile-daily')->assertSuccessful();

        $item = ReconciliationItem::query()->sole();
        expect($item->type)->toBe(ReconciliationItemType::StatusMismatch)
            ->and($item->reference_id)->toBe($this->payment->id)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded);
    });
});
