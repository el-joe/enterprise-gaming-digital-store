<?php

/**
 * Paymob adapter (Intention API, processed callbacks with HMAC, refunds), with Http::fake.
 * Mocked; awaiting sandbox verification.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\Adapters\Paymob\PaymobHmac;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Exceptions\RefundRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentWebhook;
use App\Domains\Payments\Services\PaymentAttemptService;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Shared\Models\Country;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->gateway = gatewayFor('paymob', ['EGP']);
    givenRate('EGP', '50');
    [$this->order, $this->payment] = orderAwaitingPayment($this->gateway, '1000.00', 'EGP', 'pi_paymob_1', units: 2, orderAttributes: ['country_code' => Country::query()->firstOrCreate(['code' => 'EG'], ['name' => 'Egypt', 'is_active' => true])->code], rate: '50');
    Payment::query()->whereKey($this->payment->id)->update(['provider_order_id' => '217503754']);
});

describe('HMAC', function (): void {
    it('concatenates the 20 official fields in order, with booleans as true and false', function (): void {
        expect(PaymobHmac::concatenate(paymobTransaction()))
            ->toBe('1000002026-11-10T12:00:00.123456EGPfalsefalse1920364654500001truefalsefalsefalsetruefalse217503754302852false2346MasterCardcardtrue')
            ->and(PaymobHmac::sign(paymobTransaction(), PAYMOB_HMAC_SECRET))
            ->toBe(hash_hmac('sha512', '1000002026-11-10T12:00:00.123456EGPfalsefalse1920364654500001truefalsefalsefalsetruefalse217503754302852false2346MasterCardcardtrue', PAYMOB_HMAC_SECRET));
    });

    it('accepts a valid callback, re-reads the transaction and marks the order paid', function (): void {
        fakePaymob();

        postPaymobWebhook(paymobTransaction())->assertOk();

        $payment = $this->payment->fresh();
        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
            ->and($payment->provider_capture_id)->toBe('192036465')
            ->and($payment->status)->toBe(PaymentStatus::Succeeded);
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/api/ecommerce/orders/transaction_inquiry')
            && $request['order_id'] === '217503754' && $request->hasHeader('Authorization', 'Bearer paymob-auth-token'));
    });

    it('accepts the hmac from the body when it is not in the query string', function (): void {
        fakePaymob();
        $transaction = paymobTransaction();
        $body = json_encode(['type' => 'TRANSACTION', 'obj' => $transaction, 'hmac' => PaymobHmac::sign($transaction, PAYMOB_HMAC_SECRET)]);

        $this->call('POST', '/webhooks/paymob', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body)->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('rejects a wrong hmac, a tampered field and a missing hmac', function (Closure $send): void {
        fakePaymob();

        $send()->assertStatus(400);

        expect(PaymentWebhook::query()->exists())->toBeFalse()->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
        Http::assertNothingSent();
    })->with([
        'wrong hmac' => fn () => postPaymobWebhook(paymobTransaction(), str_repeat('a', 128)),
        'tampered amount' => fn () => postPaymobWebhook(paymobTransaction(['amount_cents' => 1]), PaymobHmac::sign(paymobTransaction(), PAYMOB_HMAC_SECRET)),
        'boolean sent as 1' => fn () => postPaymobWebhook(paymobTransaction(), hash_hmac('sha512', str_replace(['true', 'false'], ['1', ''], PaymobHmac::concatenate(paymobTransaction())), PAYMOB_HMAC_SECRET)),
        'missing hmac' => fn () => postPaymobWebhook(paymobTransaction(), signed: false),
    ]);

    it('stores a repeated callback once, using the state hash as event id', function (): void {
        fakePaymob();

        postPaymobWebhook(paymobTransaction())->assertOk()->assertJsonPath('duplicate', false);
        postPaymobWebhook(paymobTransaction())->assertOk()->assertJsonPath('duplicate', true);

        expect(PaymentWebhook::query()->count())->toBe(1)->and(strlen(PaymentWebhook::query()->sole()->provider_event_id))->toBe(64);
    });

    it('sends a callback whose re-read amount differs to manual_review', function (): void {
        fakePaymob(Http::response(paymobTransaction(['amount_cents' => 50000])));

        postPaymobWebhook(paymobTransaction(['amount_cents' => 50000]))->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::ManualReview);
    });

    it('treats a declined transaction as a failed attempt', function (): void {
        fakePaymob(Http::response(paymobTransaction(['success' => false])));

        postPaymobWebhook(paymobTransaction(['success' => false]))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Failed)->and($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment);
    });
});

describe('ordering and timeouts', function (): void {
    it('keeps the order paid when a declined callback for the same order arrives late and the re-read still says success', function (): void {
        fakePaymob();

        postPaymobWebhook(paymobTransaction())->assertOk();
        postPaymobWebhook(paymobTransaction(['success' => false, 'updated_at' => '2026-11-10T11:59:00.000000']))->assertOk();

        expect($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)->and($this->order->fresh()->status)->toBe(OrderStatus::Paid);
    });

    it('changes nothing when the transaction read times out', function (): void {
        fakePaymob(Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received'));

        postPaymobWebhook(paymobTransaction())->assertOk();

        expect($this->order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and(PaymentWebhook::query()->sole()->status)->toBe(WebhookStatus::Failed);
    });
});

describe('intention', function (): void {
    it('creates an intention in EGP piastres with the integration id and returns the Unified Checkout URL', function (): void {
        $this->payment->delete();
        fakePaymob();

        $session = app(PaymentAttemptService::class)->start($this->order->public_id);

        $payment = Payment::query()->sole();
        expect($session->next['redirect_url'])->toBe(PAYMOB_API.'/unifiedcheckout/?publicKey=egy_pk_test_fixture&clientSecret=egy_csk_test_fixture')
            ->and($session->next['redirect_url'])->not->toContain('egy_sk_test')
            ->and($payment->provider_order_id)->toBe('217503754')
            ->and($payment->provider_reference)->toBe($this->order->order_number.'-1');
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/v1/intention/')
            && $request->hasHeader('Authorization', 'Token egy_sk_test_fixture')
            && $request['amount'] === 100000 && $request['currency'] === 'EGP'
            && $request['payment_methods'] === [4500001]
            && $request['special_reference'] === $this->order->order_number.'-1');
    });
});

describe('refunds', function (): void {
    beforeEach(function (): void {
        fakePaymob(overrides: [PAYMOB_API.'/api/acceptance/void_refund/refund' => Http::response(['id' => 900, 'success' => false, 'pending' => true])]);
        postPaymobWebhook(paymobTransaction())->assertOk();
        $order = app(OrderStateMachine::class)->transition($this->order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Delivered, Unit::Delivered]);
        $this->order = app(OrderStateMachine::class)->transition($order, OrderStatus::Completed, OrderActor::system());
        $this->staff = actingAsStaffWith(['refunds.create']);
        $this->units = $this->order->orderItemUnits()->orderBy('id')->pluck('public_id')->all();
    });

    it('sends a partial refund in piastres against the transaction and allows only one in flight', function (): void {
        $refunds = app(RefundService::class);
        $refund = $refunds->refundUnits($this->order, [$this->units[0]], RefundReason::CustomerRequest, OrderActor::staff($this->staff), 'Partial.');

        expect($refund->fresh()->status)->toBe(RefundStatus::Pending)->and($refund->fresh()->amount)->toBe('500.000000');
        Http::assertSent(fn (Request $request): bool => str_ends_with($request->url(), '/void_refund/refund') && $request['transaction_id'] === '192036465' && $request['amount_cents'] === 50000);

        expect(fn () => $refunds->refundUnits($this->order->fresh(), [$this->units[1]], RefundReason::CustomerRequest, OrderActor::staff($this->staff), 'Second.'))
            ->toThrow(RefundRefused::class, 'Another refund on this payment is still in progress.');
    });

    it('resolves a timed-out refund by re-reading refunded_amount_cents, never by sending it again', function (): void {
        $posts = 0;
        resetHttpFakes();
        Http::fake([
            PAYMOB_API.'/api/acceptance/void_refund/refund' => function () use (&$posts) {
                $posts++;

                return Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received');
            },
            PAYMOB_API.'/api/auth/tokens' => Http::response(['token' => 'paymob-auth-token']),
            PAYMOB_API.'/api/acceptance/transactions/*' => Http::response(paymobTransaction(['refunded_amount_cents' => 50000])),
        ]);

        $refund = app(RefundService::class)->refundUnits($this->order, [$this->units[0]], RefundReason::CustomerRequest, OrderActor::staff($this->staff), 'Partial.');
        expect($refund->fresh()->status)->toBe(RefundStatus::Unknown);

        $this->travel(10)->minutes();
        $this->artisan('payments:reconcile-open')->assertSuccessful();

        expect($refund->fresh()->status)->toBe(RefundStatus::Succeeded)
            ->and($posts)->toBe(1)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded);
    });
});
