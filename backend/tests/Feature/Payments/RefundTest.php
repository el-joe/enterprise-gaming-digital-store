<?php

/**
 * Refunds (BD4), refund_pending handling and order_item_financials, against the Stripe adapter
 * on a faked transport. Mocked; awaiting sandbox verification.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemFinancial;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\PaymentTransactionType;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Exceptions\RefundRefused;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->gateway = gatewayFor('stripe');
    [$order, $this->payment] = orderAwaitingPayment($this->gateway, '20.00', units: 2);
    app(PaymentStateApplier::class)->apply($this->payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
    $this->machine = app(OrderStateMachine::class);
    $this->order = $this->machine->transition($order, OrderStatus::Processing, OrderActor::system());
    $this->units = OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->get();
});

function refundRequests(): array
{
    return Http::recorded(fn (Request $request): bool => str_ends_with($request->url(), '/v1/refunds') && $request->method() === 'POST')->map(fn (array $pair): Request => $pair[0])->values()->all();
}

function stripeRefundResponse(string $status = 'succeeded', int $amount = 1000, string $id = 're_fixture'): array
{
    return ['id' => $id, 'object' => 'refund', 'amount' => $amount, 'currency' => 'usd', 'status' => $status, 'metadata' => []];
}

describe('automatic refunds of undelivered units (BD4 default)', function (): void {
    it('refunds only the failed unit of a partially fulfilled order and ends partially_refunded', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse())]);
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Failed]);

        $this->machine->transition($this->order, OrderStatus::PartiallyFulfilled, OrderActor::system());

        $refund = Refund::query()->sole();
        expect($refund->reason)->toBe(RefundReason::Undelivered)
            ->and($refund->amount)->toBe('10.000000')
            ->and($refund->status)->toBe(RefundStatus::Succeeded)
            ->and($refund->units()->pluck('order_item_units.id')->all())->toBe([$this->units[1]->id])
            ->and($this->units[0]->fresh()->status)->toBe(Unit::Delivered)
            ->and($this->units[1]->fresh()->status)->toBe(Unit::Refunded)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::PartiallyRefunded)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded)
            ->and(PaymentTransaction::query()->where('type', PaymentTransactionType::Refund)->count())->toBe(1);

        expect(refundRequests())->toHaveCount(1)
            ->and(refundRequests()[0]->body())->toContain('amount=1000')
            ->and(refundRequests()[0]->hasHeader('Idempotency-Key', 'refund:'.$refund->public_id))->toBeTrue();
    });

    it('refunds the whole payment when every unit failed and ends refunded', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse(amount: 2000))]);
        setUnitStatuses($this->order, [Unit::Failed, Unit::Failed]);

        $this->machine->transition($this->order, OrderStatus::RefundPending, OrderActor::system());

        expect(Refund::query()->sole()->amount)->toBe('20.000000')
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Refunded)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Refunded);
    });

    it('sends a refund the provider refuses to manual_review and releases the units for a new refund', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(['error' => ['type' => 'invalid_request_error', 'code' => 'charge_already_refunded', 'message' => 'Already refunded']], 400)]);
        setUnitStatuses($this->order, [Unit::Failed, Unit::Failed]);

        $this->machine->transition($this->order, OrderStatus::RefundPending, OrderActor::system());

        $refund = Refund::query()->sole();
        expect($refund->status)->toBe(RefundStatus::Failed)
            ->and($refund->failure_reason)->toBe('charge_already_refunded')
            ->and($refund->units()->count())->toBe(0)
            ->and($this->units[0]->fresh()->status)->toBe(Unit::Failed)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::ManualReview);
    });

    it('leaves a timed-out refund unknown, then resolves it by re-reading the provider, never by a second create', function (): void {
        $refundsPosted = 0;
        Http::fake([
            'api.stripe.com/v1/refunds?*' => fn () => Http::response(['object' => 'list', 'data' => [array_merge(stripeRefundResponse(amount: 2000), ['metadata' => ['refund_public_id' => Refund::query()->value('public_id')]])], 'has_more' => false]),
            'api.stripe.com/v1/refunds' => function () use (&$refundsPosted) {
                $refundsPosted++;

                return Http::failedConnection('cURL error 28: Operation timed out after 20001 milliseconds with 0 bytes received');
            },
        ]);
        setUnitStatuses($this->order, [Unit::Failed, Unit::Failed]);

        $this->machine->transition($this->order, OrderStatus::RefundPending, OrderActor::system());
        expect(Refund::query()->sole()->status)->toBe(RefundStatus::Unknown);

        $this->travel(10)->minutes();
        $this->artisan('payments:reconcile-open')->assertSuccessful();

        expect(Refund::query()->sole()->status)->toBe(RefundStatus::Succeeded)
            ->and($refundsPosted)->toBe(1)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::Refunded);
    });
});

describe('staff refunds (BD4 admin discretion)', function (): void {
    beforeEach(function (): void {
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Delivered]);
        $this->order = $this->machine->transition($this->order, OrderStatus::Completed, OrderActor::system());
    });

    it('refunds delivered units in two partial refunds that add up to the payment', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::sequence()->push(stripeRefundResponse(id: 're_first'))->push(stripeRefundResponse(id: 're_second'))]);
        actingAsStaffWith(['refunds.create']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [$this->units[0]->public_id], 'reason' => 'customer_request', 'note' => 'Code did not work for the customer.'])
            ->assertCreated()->assertJsonPath('data.amount.amount', '10.000000')->assertJsonPath('data.status', 'succeeded');

        expect($this->order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::PartiallyRefunded);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [$this->units[1]->public_id], 'reason' => 'customer_request', 'note' => 'Second unit as well.'])
            ->assertCreated()->assertJsonPath('data.amount.amount', '10.000000');

        expect($this->order->fresh()->status)->toBe(OrderStatus::Refunded)
            ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Refunded)
            ->and(Refund::query()->sum('amount'))->toEqual('20.000000');
    });

    it('refuses to refund the same unit twice', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse())]);
        actingAsStaffWith(['refunds.create']);
        $body = ['unit_ids' => [$this->units[0]->public_id], 'reason' => 'customer_request', 'note' => 'Customer asked for it.'];

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", $body)->assertCreated();
        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", $body)->assertStatus(409)->assertJsonPath('code', 'refund_duplicate');

        expect(Refund::query()->count())->toBe(1)->and(refundRequests())->toHaveCount(1);
    });

    it('refuses a refund once nothing remains on the payment', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse(amount: 2000))]);
        $staff = actingAsStaffWith(['refunds.create']);
        app(RefundService::class)->refundUnits($this->order, $this->units->pluck('public_id')->all(), RefundReason::Admin, OrderActor::staff($staff), 'All of it.');

        expect(fn () => app(RefundService::class)->refundUnits($this->order->fresh(), [$this->units[0]->public_id], RefundReason::Admin, OrderActor::staff($staff), 'Again.'))
            ->toThrow(RefundRefused::class);
    });

    it('denies staff without refunds.create and changes nothing', function (): void {
        actingAsStaffWith(['orders.view']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [$this->units[0]->public_id], 'reason' => 'admin', 'note' => 'Not allowed.'])->assertForbidden();

        expect(Refund::query()->exists())->toBeFalse()->and($this->order->fresh()->status)->toBe(OrderStatus::Completed);
    });

    it('validates the request and refuses a unit of another order', function (): void {
        actingAsStaffWith(['refunds.create']);
        $other = OrderItemUnit::factory()->create();

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [], 'reason' => 'duplicate', 'note' => ''])
            ->assertUnprocessable()->assertJsonValidationErrors(['unit_ids', 'reason', 'note']);
        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [$other->public_id], 'reason' => 'admin', 'note' => 'Wrong order.'])
            ->assertUnprocessable()->assertJsonPath('code', 'refund_units_invalid');
    });
});

describe('order_item_financials', function (): void {
    it('is written on completion with revenue, supplier cost, estimated fee, FX cost and net', function (): void {
        app(SettingsService::class)->set('pricing', 'default_gateway_fee_pct', '3', SettingType::Decimal);
        $delivered = SupplierTransaction::factory()->create(['order_item_unit_id' => $this->units[0]->id, 'status' => SupplierTransactionStatus::Succeeded, 'actual_cost' => '8.000000', 'actual_cost_currency' => 'USD']);
        SupplierTransaction::factory()->create(['order_item_unit_id' => $this->units[1]->id, 'status' => SupplierTransactionStatus::Succeeded, 'actual_cost' => '7.500000', 'actual_cost_currency' => 'USD']);
        Payment::query()->whereKey($this->payment->id)->update(['fee_amount' => '0.600000', 'fee_currency' => 'USD', 'fee_is_estimate' => true]);
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Delivered]);

        $this->machine->transition($this->order, OrderStatus::Completed, OrderActor::system());

        $row = OrderItemFinancial::query()->where('order_id', $this->order->id)->sole();
        expect($row->revenue_base)->toBe('20.000000')
            ->and($row->supplier_cost_base)->toBe('15.500000')
            ->and($row->supplier_id)->toBe($delivered->supplier_id)
            ->and($row->gateway_fee_base)->toBe('0.600000')
            ->and($row->gateway_fee_is_estimate)->toBeTrue()
            ->and($row->fx_cost_base)->toBe('0.000000')
            ->and($row->gross_profit_base)->toBe('4.500000')
            ->and($row->net_profit_base)->toBe('3.900000')
            ->and($row->currency)->toBe('USD');
    });

    it('books no supplier cost for a unit whose supplier attempt FAILED (cost reversal pending) and records the refund', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse())]);
        SupplierTransaction::factory()->create(['order_item_unit_id' => $this->units[0]->id, 'status' => SupplierTransactionStatus::Succeeded, 'actual_cost' => '8.000000', 'actual_cost_currency' => 'USD']);
        SupplierTransaction::factory()->create(['order_item_unit_id' => $this->units[1]->id, 'status' => SupplierTransactionStatus::Rejected, 'cost_reversal_pending' => true, 'actual_cost' => '8.000000', 'actual_cost_currency' => 'USD']);
        Payment::query()->whereKey($this->payment->id)->update(['fee_amount' => '0', 'fee_currency' => 'USD']);
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Failed]);

        $this->machine->transition($this->order, OrderStatus::PartiallyFulfilled, OrderActor::system());

        $row = OrderItemFinancial::query()->where('order_id', $this->order->id)->sole();
        expect($row->supplier_cost_base)->toBe('8.000000')
            ->and($row->refunded_base)->toBe('10.000000')
            ->and($row->net_profit_base)->toBe('2.000000')
            ->and(SupplierTransaction::query()->where('cost_reversal_pending', true)->count())->toBe(1)
            ->and($this->order->fresh()->status)->toBe(OrderStatus::PartiallyRefunded);
    });

    it('is written once per item however many settling transitions happen', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(stripeRefundResponse())]);
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Delivered]);
        $order = $this->machine->transition($this->order, OrderStatus::Completed, OrderActor::system());
        $staff = actingAsStaffWith(['refunds.create']);
        app(RefundService::class)->refundUnits($order, [$this->units[0]->public_id], RefundReason::Admin, OrderActor::staff($staff), 'Goodwill.');

        expect(OrderItemFinancial::query()->where('order_id', $this->order->id)->count())->toBe(1)
            ->and(OrderItemFinancial::query()->where('order_id', $this->order->id)->sole()->refunded_base)->toBe('10.000000')
            ->and(Order::query()->find($order->id)->status)->toBe(OrderStatus::PartiallyRefunded);
    });
});
