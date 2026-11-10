<?php

/**
 * The wallet as a payment method and as a refund destination (BD3), through the M10 checkout,
 * eligibility and refund flows. Internal: no provider is called. In-process; the races are in
 * WalletConcurrencyTest.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\PaymentAttemptRefused;
use App\Domains\Payments\Exceptions\RefundRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentGatewayRegistry;
use App\Domains\Payments\Services\PaymentReconciler;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Payments\Services\RefundExecutor;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\CustomerWalletStatus;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Models\WalletTransaction;
use App\Domains\Wallet\Services\WalletLedger;
use Carbon\CarbonImmutable;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    CountryFactory::ensure('US');
    $this->gateway = walletGateway();
    $this->user = User::factory()->create();
    $this->variant = sellableVariant('10');
});

function walletCheckout(User $user, int $quantity = 1, string $key = 'wallet-order-key-1'): TestResponse
{
    test()->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => test()->variant->public_id, 'quantity' => $quantity])->assertCreated();

    return test()->actingAs($user)->postJson('/api/v1/checkout/place-order', placeOrderBody(bcmul('10', (string) $quantity, 2), ['gateway' => 'wallet']), ['Idempotency-Key' => $key]);
}

function walletOptions(User $user): array
{
    return collect(test()->actingAs($user)->getJson('/api/v1/checkout/payment-options?country=US')->assertOk()->json('data.options'))->pluck('gateway')->all();
}

describe('eligibility', function (): void {
    it('offers the wallet only when the balance covers the order', function (): void {
        test()->actingAs($this->user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 2])->assertCreated();

        fundWallet($this->user, '19.99');
        expect(walletOptions($this->user))->toBe([]);

        fundWallet($this->user, '0.01');
        expect(walletOptions($this->user))->toBe(['wallet']);
    });

    it('never offers the wallet to an unverified customer or from a frozen wallet', function (Closure $break): void {
        fundWallet($this->user, '100');
        $break($this->user);
        test()->actingAs($this->user->fresh())->postJson('/api/v1/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1]);

        expect(walletOptions($this->user->fresh()))->toBe([]);
    })->with([
        'unverified email' => [fn (User $user) => $user->forceFill(['email_verified_at' => null])->save()],
        'frozen wallet' => [fn (User $user) => walletOf($user)->forceFill(['status' => CustomerWalletStatus::Frozen])->save()],
    ]);

    it('is never available to a guest, who has no account to hold credit', function (): void {
        $adapter = app(PaymentGatewayRegistry::class)->for($this->gateway);

        expect($adapter->canCover(null, Money::of('0.01', 'USD')))->toBeFalse()
            ->and($adapter->supportsCurrency('USD'))->toBeTrue()
            ->and($adapter->supportsCurrency('EGP'))->toBeFalse();
    });

    it('does not need gateway credentials, unlike provider gateways', function (): void {
        $this->gateway->paymentGatewayCredentials()->delete();
        fundWallet($this->user, '10');
        test()->actingAs($this->user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1]);

        expect(walletOptions($this->user))->toBe(['wallet']);
    });
});

describe('paying from the wallet', function (): void {
    it('debits the wallet and marks the order paid in the same transaction, with no provider call', function (): void {
        Http::preventStrayRequests();
        fundWallet($this->user, '30');

        $response = walletCheckout($this->user, 2)->assertCreated()
            ->assertJsonPath('data.next.type', 'wallet')
            ->assertJsonPath('data.next.gateway', 'wallet');

        $order = Order::query()->sole();
        $payment = Payment::query()->sole();
        $debit = WalletTransaction::query()->where('type', WalletTransactionType::DebitPurchase)->sole();

        expect($order->status)->toBe(OrderStatus::Paid)
            ->and($order->primary_payment_id)->toBe($payment->id)
            ->and($payment->status)->toBe(PaymentStatus::Succeeded)
            ->and($payment->provider_payment_id)->toBe('wallet_txn_'.$debit->id)
            ->and($payment->fee_amount)->toBe('0.000000')
            ->and($debit->amount)->toBe('-20.000000')
            ->and($debit->reference_type)->toBe('payment')
            ->and($debit->reference_id)->toBe($payment->id)
            ->and($debit->idempotency_key)->toBe('pay:'.$payment->public_id)
            ->and(walletTotals($this->user))->toBe(['balance' => '10.000000', 'sum' => '10.000000'])
            ->and(PaymentTransaction::query()->where('payment_id', $payment->id)->count())->toBe(1)
            ->and($response->json('data.next.payment_public_id'))->toBe($payment->public_id);

        Queue::assertPushed(FulfilOrder::class);
    });

    it('fails the attempt and leaves the order unpaid when the balance was spent after the options were shown', function (): void {
        fundWallet($this->user, '10');
        test()->actingAs($this->user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $this->variant->public_id, 'quantity' => 1]);
        expect(walletOptions($this->user))->toBe(['wallet']);

        $placed = null;
        Order::created(function () use (&$placed): void {
            if ($placed === null) {
                $placed = true;
                app(WalletLedger::class)->post(new WalletEntry(test()->user->id, WalletTransactionType::DebitAdmin, Money::of('5', 'USD'), 'spent-elsewhere'));
            }
        });

        test()->actingAs($this->user)->postJson('/api/v1/checkout/place-order', placeOrderBody('10.00', ['gateway' => 'wallet']), ['Idempotency-Key' => 'wallet-order-key-2'])
            ->assertCreated()->assertJsonPath('data.next', null);

        expect(Order::query()->sole()->status)->toBe(OrderStatus::PendingPayment)
            ->and(Order::query()->sole()->primary_payment_id)->toBeNull()
            ->and(Payment::query()->sole()->status)->toBe(PaymentStatus::Failed)
            ->and(Payment::query()->sole()->failure_code)->toBe('wallet_insufficient_balance')
            ->and(WalletTransaction::query()->where('type', WalletTransactionType::DebitPurchase)->exists())->toBeFalse()
            ->and(walletTotals($this->user))->toBe(['balance' => '5.000000', 'sum' => '5.000000']);
    });

    it('rolls the debit back when applying the payment fails, so there is no debit without paid', function (): void {
        fundWallet($this->user, '20');
        [$order, $payment] = orderAwaitingPayment($this->gateway, '10.00', providerPaymentId: null, orderAttributes: ['user_id' => $this->user->id]);
        $payment->forceFill(['status' => PaymentStatus::Created])->save();

        app()->instance(PaymentStateApplier::class, new class extends PaymentStateApplier
        {
            public function __construct() {}

            public function apply(int $paymentId, VerifiedPaymentState $state, OrderActor $actor): Payment
            {
                throw new RuntimeException('Simulated failure after the debit.');
            }
        });

        $adapter = app(PaymentGatewayRegistry::class)->forPayment($payment);
        expect(fn () => $adapter->settle($payment))->toThrow(RuntimeException::class);

        expect(walletTotals($this->user))->toBe(['balance' => '20.000000', 'sum' => '20.000000'])
            ->and(WalletTransaction::query()->where('type', WalletTransactionType::DebitPurchase)->exists())->toBeFalse()
            ->and($order->fresh()->status)->toBe(OrderStatus::PendingPayment)
            ->and($payment->fresh()->provider_payment_id)->toBeNull();
    });

    it('settles an attempt only once: a second settle is refused and debits nothing', function (): void {
        fundWallet($this->user, '50');
        walletCheckout($this->user)->assertCreated();
        $payment = Payment::query()->sole();

        $adapter = app(PaymentGatewayRegistry::class)->forPayment($payment);

        expect(fn () => $adapter->settle($payment))->toThrow(PaymentAttemptRefused::class)
            ->and(WalletTransaction::query()->where('type', WalletTransactionType::DebitPurchase)->count())->toBe(1)
            ->and($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
            ->and(walletTotals($this->user)['balance'])->toBe('40.000000');
    });

    it('reports the ledger as the provider state, so daily reconciliation matches', function (): void {
        fundWallet($this->user, '50');
        walletCheckout($this->user)->assertCreated();
        $payment = Payment::query()->sole();

        $state = app(PaymentGatewayRegistry::class)->forPayment($payment)->fetchState($payment);
        expect($state->status)->toBe(ProviderPaymentStatus::Succeeded)
            ->and($state->amount->equals(Money::of('10', 'USD')))->toBeTrue();

        $run = app(PaymentReconciler::class)->reconcileDaily($this->gateway, CarbonImmutable::now()->addHour());
        expect($run->checked)->toBe(1)->and($run->mismatches)->toBe(0);
    });

    it('cannot be reached through the webhook route', function (): void {
        $this->postJson('/webhooks/wallet', ['type' => 'payment.succeeded'])->assertStatus(400);

        expect(Order::query()->where('status', OrderStatus::Paid)->exists())->toBeFalse();
    });
});

describe('wallet refunds', function (): void {
    it('refunds a wallet payment back to the wallet through the M10 refund flow, once', function (): void {
        fundWallet($this->user, '20');
        walletCheckout($this->user, 2)->assertCreated();
        $order = Order::query()->sole();
        $machine = app(OrderStateMachine::class);
        $machine->transition($order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Delivered, Unit::Failed]);

        $machine->transition($order, OrderStatus::PartiallyFulfilled, OrderActor::system());

        $refund = Refund::query()->sole();
        expect($refund->status)->toBe(RefundStatus::Succeeded)
            ->and($refund->provider_refund_id)->toStartWith('wallet_txn_')
            ->and($order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded)
            ->and(Payment::query()->sole()->status)->toBe(PaymentStatus::PartiallyRefunded)
            ->and(walletTotals($this->user))->toBe(['balance' => '10.000000', 'sum' => '10.000000']);

        app(RefundExecutor::class)->poll($refund->fresh());
        expect(WalletTransaction::query()->where('type', WalletTransactionType::CreditRefund)->where('idempotency_key', 'refund:'.$refund->public_id)->count())->toBe(1)
            ->and(walletTotals($this->user)['balance'])->toBe('10.000000');
    });

    it('lets staff refund a card payment to the wallet instead, without calling the provider', function (): void {
        Queue::fake([FulfilOrder::class]);
        $stripe = gatewayFor('stripe');
        [$order, $payment] = orderAwaitingPayment($stripe, '20.00', units: 2, orderAttributes: ['user_id' => $this->user->id]);
        app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        $machine = app(OrderStateMachine::class);
        $machine->transition($order, OrderStatus::Processing, OrderActor::system());
        setUnitStatuses($order, [Unit::Delivered, Unit::Delivered]);
        $machine->transition($order, OrderStatus::Completed, OrderActor::system());
        Http::preventStrayRequests();
        Http::fake();
        $unit = OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->first();

        actingAsStaffWith(['refunds.create']);
        $this->postJson("/admin/api/orders/{$order->public_id}/refunds", ['unit_ids' => [$unit->public_id], 'reason' => 'customer_request', 'note' => 'Store credit agreed', 'destination' => 'wallet'])
            ->assertCreated()->assertJsonPath('data.destination', 'wallet');

        $refund = Refund::query()->sole();
        expect($refund->destination)->toBe(RefundDestination::Wallet)
            ->and($refund->status)->toBe(RefundStatus::Succeeded)
            ->and($unit->fresh()->status)->toBe(Unit::Refunded)
            ->and($order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded)
            ->and(walletTotals($this->user))->toBe(['balance' => '10.000000', 'sum' => '10.000000']);

        Http::assertNothingSent();
    });

    it('refuses a wallet refund for a guest order', function (): void {
        $stripe = gatewayFor('stripe');
        [$order, $payment] = orderAwaitingPayment($stripe, '20.00', orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.test']);
        app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
        app(OrderStateMachine::class)->transition($order, OrderStatus::ManualReview, OrderActor::system());
        $unit = OrderItemUnit::query()->where('order_id', $order->id)->first();
        $unit->forceFill(['status' => Unit::Failed])->save();

        $staff = actingAsStaffWith(['refunds.create']);

        expect(fn () => app(RefundService::class)->refundUnits($order->fresh(), [$unit->public_id], RefundReason::Admin, OrderActor::staff($staff), 'note text', RefundDestination::Wallet))
            ->toThrow(RefundRefused::class, 'registered customer');
    });
});
