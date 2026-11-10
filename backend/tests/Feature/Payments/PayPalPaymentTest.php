<?php

/**
 * PayPal Orders v2 adapter through the webhook, capture and attempt paths, with Http::fake.
 * Mocked; awaiting sandbox verification.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\OrderStatusReader;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Exceptions\PaymentAttemptRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentAttemptService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->gateway = gatewayFor('paypal');
    [$this->order, $this->payment] = orderAwaitingPayment($this->gateway, providerPaymentId: 'PAYPAL-ORDER-1');
});

function captureEvent(string $type = 'PAYMENT.CAPTURE.COMPLETED'): string
{
    return paypalEvent($type, ['id' => 'CAPTURE-1', 'status' => 'COMPLETED', 'supplementary_data' => ['related_ids' => ['order_id' => 'PAYPAL-ORDER-1']]]);
}

describe('webhook verification', function (): void {
    it('verifies through verify-webhook-signature with the raw event, then marks paid on capture COMPLETED', function (): void {
        fakePayPal();
        $payload = captureEvent();

        postPayPalWebhook($payload)->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
            ->and($this->payment->fresh()->provider_capture_id)->toBe('CAPTURE-1')
            ->and($this->payment->fresh()->fee_amount)->toBe('0.880000')
            ->and($this->payment->fresh()->fee_is_estimate)->toBeFalse();

        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/notifications/verify-webhook-signature')
            && str_contains($request->body(), '"webhook_event":'.$payload)
            && str_contains($request->body(), '"webhook_id":"WH-FIXTURE"')
            && str_contains($request->body(), '"transmission_sig":"c2lnbmF0dXJl"'));
    });

    it('rejects an event PayPal does not verify', function (): void {
        fakePayPal(verify: Http::response(['verification_status' => 'FAILURE']));

        postPayPalWebhook(captureEvent())->assertStatus(400);

        expect(PaymentWebhook::query()->exists())->toBeFalse()->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });

    it('rejects an event with missing transmission headers without calling PayPal', function (): void {
        fakePayPal();

        postPayPalWebhook(captureEvent(), ['PAYPAL-TRANSMISSION-ID' => 'tx-1'])->assertStatus(400);

        Http::assertNothingSent();
    });

    it('answers 503 when the verification API cannot be reached, so PayPal retries', function (): void {
        fakePayPal(verify: Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received'));

        postPayPalWebhook(captureEvent())->assertStatus(503);

        expect(PaymentWebhook::query()->exists())->toBeFalse();
    });

    it('stores a duplicate delivery once', function (): void {
        fakePayPal();
        $payload = captureEvent();

        postPayPalWebhook($payload)->assertOk()->assertJsonPath('duplicate', false);
        postPayPalWebhook($payload)->assertOk()->assertJsonPath('duplicate', true);

        expect(PaymentWebhook::query()->count())->toBe(1);
    });
});

describe('capture', function (): void {
    it('does not mark paid when the order is COMPLETED but its capture is PENDING', function (): void {
        fakePayPal(Http::response(paypalOrder(captureStatus: 'PENDING')));

        postPayPalWebhook(captureEvent('PAYMENT.CAPTURE.PENDING'))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::PaymentProcessing)
            ->and($this->order->fresh()->paid_at)->toBeNull()
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Processing);
    });

    it('captures an approved order server side when CHECKOUT.ORDER.APPROVED arrives (recovery capture)', function (): void {
        fakePayPal(Http::response(paypalOrder(status: 'APPROVED', captureStatus: null)));

        postPayPalWebhook(paypalEvent('CHECKOUT.ORDER.APPROVED', ['id' => 'PAYPAL-ORDER-1', 'status' => 'APPROVED']))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders/PAYPAL-ORDER-1/capture')
            && $request->hasHeader('PayPal-Request-Id', 'pay_capture:'.$this->payment->public_id));
    });

    it('captures from the capture endpoint for the order\'s own session only', function (): void {
        fakePayPal(Http::response(paypalOrder(status: 'APPROVED', captureStatus: null)));

        $this->postJson("/api/v1/checkout/orders/{$this->order->public_id}/paypal/capture")->assertNotFound();

        $this->withSession([OrderStatusReader::SESSION_KEY => [$this->order->public_id]])
            ->postJson("/api/v1/checkout/orders/{$this->order->public_id}/paypal/capture")
            ->assertOk()->assertJsonPath('data.status', 'paid');
    });

    it('marks a DECLINED capture failed', function (): void {
        fakePayPal(Http::response(paypalOrder(captureStatus: 'DECLINED')));

        postPayPalWebhook(captureEvent('PAYMENT.CAPTURE.DECLINED'))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Failed)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });
});

describe('ordering and timeouts', function (): void {
    it('ignores a DECLINED event that arrives after the capture completed, because the re-read says COMPLETED', function (): void {
        fakePayPal();

        postPayPalWebhook(captureEvent())->assertOk();
        postPayPalWebhook(captureEvent('PAYMENT.CAPTURE.DECLINED'))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('changes nothing when the order read times out during webhook processing', function (): void {
        fakePayPal(Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received'));

        postPayPalWebhook(captureEvent())->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and(PaymentWebhook::query()->sole()->status)->toBe(WebhookStatus::Failed);
    });
});

describe('attempts and currencies', function (): void {
    it('creates the order with PayPal-Request-Id, the invoice id and a decimal amount', function (): void {
        $this->payment->delete();
        fakePayPal();

        $session = app(PaymentAttemptService::class)->start($this->order->public_id);

        $payment = Payment::query()->sole();
        expect($session->next['approve_url'])->toContain('PAYPAL-ORDER-1')
            ->and($payment->provider_payment_id)->toBe('PAYPAL-ORDER-1');
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/checkout/orders')
            && $request->hasHeader('PayPal-Request-Id', 'pay_create:'.$payment->public_id)
            && $request['purchase_units'][0]['amount'] === ['currency_code' => 'USD', 'value' => '20.00']
            && $request['purchase_units'][0]['invoice_id'] === $this->order->order_number.'-1'
            && $request['purchase_units'][0]['custom_id'] === $payment->public_id);
    });

    it('never sends EGP, SAR or AED to PayPal, even if the merchant list wrongly enables them', function (string $currency): void {
        gatewayFor('paypal', ['USD', $currency], defaultCurrency: 'USD');
        [$order] = orderAwaitingPayment($this->gateway, '100.00', $currency, null, orderAttributes: ['order_number' => 'GS-'.$currency]);
        fakePayPal();

        expect(fn () => app(PaymentAttemptService::class)->start($order->public_id))->toThrow(PaymentAttemptRefused::class);

        Http::assertNothingSent();
    })->with(['EGP', 'SAR', 'AED']);

    it('refunds against the capture with PayPal-Request-Id', function (): void {
        fakePayPal();
        postPayPalWebhook(captureEvent())->assertOk();
        $order = app(OrderStateMachine::class)->transition($this->order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Failed]);

        app(OrderStateMachine::class)->transition($order, OrderStatus::RefundPending, OrderActor::system());

        $refund = Refund::query()->sole();
        expect($refund->status)->toBe(RefundStatus::Succeeded)->and($refund->provider_refund_id)->toBe('REFUND-1')
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Refunded);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v2/payments/captures/CAPTURE-1/refund')
            && $request->hasHeader('PayPal-Request-Id', 'refund:'.$refund->public_id)
            && $request['amount'] === ['currency_code' => 'USD', 'value' => '20.00']);
    });
});
