<?php

/**
 * Supplier engine end to end, in one process (M8 acceptance). Reloadly is mocked with Http::fake;
 * awaiting sandbox verification. Orders start from a paid fixture: no code path marks them paid.
 * Real cross-process races are in SupplierConcurrencyTest.
 */

use App\Domains\Catalog\Enums\DeliveryType;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Fulfillment\Jobs\SubmitUnitToSupplier;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Fulfillment\Models\ManualFulfillmentTask;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Fulfillment\Services\StaleSubmissionSweeper;
use App\Domains\Fulfillment\Services\SupplierSubmissionGate;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Notifications\OrderDelivered;
use App\Domains\Orders\Notifications\OrderFulfilmentFailed;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use App\Domains\Suppliers\Enums\SupplierTransactionCodeRetrievalStatus as Retrieval;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierProductMapping;
use App\Domains\Suppliers\Models\SupplierProductPrice;
use App\Domains\Suppliers\Models\SupplierTransaction;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    Notification::fake();
    $this->variant = sellableVariant('9');
    $this->reloadly = reloadlySupplier();
    $this->mapping = mapToSupplier($this->variant, $this->reloadly);
});

function fulfil(Order $order): Order
{
    FulfilOrder::dispatch($order->id);

    return $order->fresh();
}

/**
 * @return list<Unit>
 */
function unitStatuses(Order $order): array
{
    return OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->pluck('status')->all();
}

function stockCode(int $variantId, string $code): DigitalCode
{
    return app(DigitalCodeVault::class)->store(new DeliverableItem($code), DigitalCodeSource::Stock, [
        'product_variant_id' => $variantId,
        'stock_status' => DigitalCodeStockStatus::Available,
    ]);
}

it('fulfils a paid order: risk gate, one purchase, code in the vault, order completed', function (): void {
    fakeReloadly();
    $order = paidOrderFor($this->variant);

    $order = fulfil($order);

    $transaction = SupplierTransaction::query()->sole();
    $code = DigitalCode::query()->sole();

    expect($order->status)->toBe(OrderStatus::Completed)
        ->and($order->riskAssessment()->exists())->toBeTrue()
        ->and(unitStatuses($order))->toBe([Unit::Delivered])
        ->and($transaction->status)->toBe(Status::Succeeded)
        ->and($transaction->code_retrieval_status)->toBe(Retrieval::Retrieved)
        ->and($transaction->actual_cost)->toBe('9.750000')
        ->and($transaction->client_reference)->toBe('u'.OrderItemUnit::query()->value('public_id').'s'.$this->reloadly->id)
        ->and($code->masked_code)->toBe('****7890')
        ->and($code->code_ciphertext)->not->toContain('12345678901234567890')
        ->and(reloadlyOrderPosts())->toHaveCount(1);

    Notification::assertSentOnDemand(OrderDelivered::class);
});

it('holds a risky paid order for review and calls no supplier', function (): void {
    app(SettingsService::class)->set('security', 'risk_review_score', 5, SettingType::Int);
    fakeReloadly();

    $order = fulfil(paidOrderFor($this->variant));

    expect($order->status)->toBe(OrderStatus::ManualReview)
        ->and(unitStatuses($order))->toBe([Unit::Pending]);
    Http::assertNothingSent();
});

it('never fulfils an unpaid order, even with a queued unit', function (): void {
    fakeReloadly();
    $order = paidOrderFor($this->variant, status: OrderStatus::PendingPayment, orderAttributes: ['paid_at' => null]);
    $order->forceFill(['primary_payment_id' => null])->save();
    $unit = OrderItemUnit::query()->sole();
    $unit->forceFill(['status' => Unit::Queued, 'selection_snapshot' => ['candidates' => [['mapping_id' => $this->mapping->id, 'supplier_id' => $this->reloadly->id, 'supplier_product_id' => '1234', 'unit_price' => '10', 'unit_currency' => 'USD', 'base_cost' => '9', 'priority' => 100]]]])->save();

    FulfilOrder::dispatch($order->id);
    SubmitUnitToSupplier::dispatch($unit->id);

    expect(SupplierTransaction::query()->exists())->toBeFalse();
    Http::assertNothingSent();
});

it('sends a read timeout to pending verification with no second POST, then verifies by reference only', function (): void {
    fakeReloadly(Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received'));

    $order = fulfil(paidOrderFor($this->variant));
    $transaction = SupplierTransaction::query()->sole();

    expect($order->status)->toBe(OrderStatus::PendingVerification)
        ->and($transaction->status)->toBe(Status::PendingVerification)
        ->and(unitStatuses($order))->toBe([Unit::PendingVerification]);

    $this->travel(2)->minutes();
    Artisan::call('suppliers:verify-pending');
    Artisan::call('suppliers:sweep-stale-submissions');

    expect(reloadlyOrderPosts())->toHaveCount(1)
        ->and(Http::recorded(static fn (Request $request): bool => str_contains($request->url(), 'customIdentifier='.$transaction->client_reference)))->toHaveCount(1);
});

it('recovers a worker that died between the supplier call and recording the outcome', function (): void {
    $order = paidOrderFor($this->variant);
    $gate = app(SupplierSubmissionGate::class);

    // Plan without submitting, then perform steps 1 and 2 of the gate and "die" before step 3.
    $unit = OrderItemUnit::query()->sole();
    Queue::fake([SubmitUnitToSupplier::class]);
    fulfil($order);
    [$transaction, $request] = $gate->claim($unit->id);

    fakeReloadly(overrides: [RELOADLY_API.'/reports/transactions*' => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '9001', $transaction->client_reference)], 'last' => true])]);
    app(SupplierAdapterRegistry::class)->for($this->reloadly)->createOrder($request);

    expect($transaction->fresh()->status)->toBe(Status::Submitting);

    $this->travel(2)->minutes();
    app(StaleSubmissionSweeper::class)->sweep();

    expect($transaction->fresh()->status)->toBe(Status::PendingVerification)
        ->and($order->fresh()->status)->toBe(OrderStatus::PendingVerification);

    Artisan::call('suppliers:verify-pending');

    expect($transaction->fresh()->status)->toBe(Status::Succeeded)
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and(reloadlyOrderPosts())->toHaveCount(1);
});

it('treats a redelivered submit job as a no-op', function (): void {
    fakeReloadly();
    $order = fulfil(paidOrderFor($this->variant));
    $unitId = OrderItemUnit::query()->value('id');

    SubmitUnitToSupplier::dispatch($unitId);
    (new SubmitUnitToSupplier($unitId))->handle(app(SupplierSubmissionGate::class));

    expect(SupplierTransaction::query()->count())->toBe(1)
        ->and(reloadlyOrderPosts())->toHaveCount(1)
        ->and($order->status)->toBe(OrderStatus::Completed);
});

describe('fallback', function (): void {
    beforeEach(function (): void {
        $this->stock = stockSupplier();
        mapToSupplier($this->variant, $this->stock, '8', ['role' => 'secondary', 'priority' => 200, 'override_cost' => '8', 'override_cost_currency' => 'USD'], 'variant-'.$this->variant->id);
        stockCode($this->variant->id, 'STOCK-CODE-0001');
    });

    it('falls back to the next supplier only after a definitive refusal', function (): void {
        fakeReloadly(Http::response(['errorCode' => 'INSUFFICIENT_BALANCE'], 400));

        $order = fulfil(paidOrderFor($this->variant));

        $attempts = SupplierTransaction::query()->orderBy('attempt_no')->get();
        expect($order->status)->toBe(OrderStatus::Completed)
            ->and($attempts->pluck('status')->all())->toBe([Status::Rejected, Status::Succeeded])
            ->and($attempts[1]->supplier_id)->toBe($this->stock->id)
            ->and($attempts[1]->is_fallback)->toBeTrue()
            ->and(DigitalCode::query()->where('source', DigitalCodeSource::Stock)->sole()->stock_status)->toBe(DigitalCodeStockStatus::Delivered);
    });

    it('never falls back after an ambiguous outcome', function (mixed $response): void {
        fakeReloadly($response);

        $order = fulfil(paidOrderFor($this->variant));

        expect($order->status)->toBe(OrderStatus::PendingVerification)
            ->and(SupplierTransaction::query()->count())->toBe(1)
            ->and(DigitalCode::query()->where('source', DigitalCodeSource::Stock)->sole()->stock_status)->toBe(DigitalCodeStockStatus::Available);
    })->with([
        '500' => [fn () => Http::response([], 500)],
        'read timeout' => [fn () => Http::failedConnection('cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received')],
        'connection reset' => [fn () => Http::failedConnection('cURL error 56: Recv failure: Connection reset by peer')],
        'malformed' => [fn () => Http::response('garbage', 200)],
        'uncertain code' => [fn () => Http::response(['errorCode' => 'DIGITAL_ROLLBACK'], 400)],
    ]);

    it('holds the unit for review when the next supplier would sell below margin', function (): void {
        SupplierProductMapping::query()->where('supplier_id', $this->stock->id)->update(['override_cost' => '25']);
        fakeReloadly(Http::response(['errorCode' => 'TEMPORARY_OUT_OF_STOCK'], 400));

        $order = fulfil(paidOrderFor($this->variant));

        expect(unitStatuses($order))->toBe([Unit::ManualReview])
            ->and($order->status)->toBe(OrderStatus::ManualReview)
            ->and(SupplierTransaction::query()->count())->toBe(1);
    });

    it('fails the unit when fallback is disabled, and tells the customer', function (): void {
        $this->reloadly->forceFill(['fallback_enabled' => false])->save();
        fakeReloadly(Http::response(reloadlyTransaction('REFUNDED')));

        $order = fulfil(paidOrderFor($this->variant));

        expect(unitStatuses($order))->toBe([Unit::Failed])->and($order->status)->toBe(OrderStatus::RefundPending);
        Notification::assertSentOnDemand(OrderFulfilmentFailed::class);
    });
});

it('holds the unit for review at the verification deadline and keeps the active lock', function (): void {
    fakeReloadly(Http::response([], 502));
    $order = fulfil(paidOrderFor($this->variant));
    $transaction = SupplierTransaction::query()->sole();

    foreach ([2, 3, 6, 11, 16, 31, 31, 31, 31] as $minutes) {
        $this->travel($minutes)->minutes();
        Artisan::call('suppliers:verify-pending');
    }

    $transaction->refresh();
    expect($transaction->status)->toBe(Status::PendingVerification)
        ->and($transaction->next_verification_at)->toBeNull()
        ->and($transaction->active_unit_key)->toBe($transaction->order_item_unit_id)
        ->and(unitStatuses($order))->toBe([Unit::ManualReview])
        ->and($order->fresh()->status)->toBe(OrderStatus::ManualReview)
        ->and(reloadlyOrderPosts())->toHaveCount(1);
});

it('retries only the GET when fetching the code fails after a purchase', function (): void {
    fakeReloadly(overrides: [RELOADLY_API.'/orders/transactions/*/cards' => Http::sequence()
        ->push([], 500)
        ->push('{"cardNumber":12345678901234567890}', 200)]);

    $order = fulfil(paidOrderFor($this->variant));
    $transaction = SupplierTransaction::query()->sole();

    expect($transaction->code_retrieval_status)->toBe(Retrieval::Failed)
        ->and($order->status)->toBe(OrderStatus::SupplierPending);

    $this->travel(11)->minutes();
    Artisan::call('suppliers:sweep-stale-submissions');

    expect($transaction->fresh()->code_retrieval_status)->toBe(Retrieval::Retrieved)
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and(reloadlyOrderPosts())->toHaveCount(1);
});

it('never sends an order above the per-request cap', function (): void {
    SupplierProductPrice::query()->update(['cost' => '150']);
    fakeReloadly();

    $order = fulfil(paidOrderFor($this->variant, unitPrice: '200'));

    expect(unitStatuses($order))->toBe([Unit::ManualReview])
        ->and(OrderItemUnit::query()->sole()->selection_snapshot['excluded'][0]['reason'])->toBe('over_request_cap');
    expect(reloadlyOrderPosts())->toBeEmpty();
});

it('skips a supplier whose cached balance does not cover the cost', function (): void {
    $this->reloadly->forceFill(['last_balance' => '5'])->save();
    fakeReloadly();

    $order = fulfil(paidOrderFor($this->variant));

    expect(OrderItemUnit::query()->sole()->selection_snapshot['excluded'][0]['reason'])->toBe('insufficient_balance')
        ->and($order->status)->toBe(OrderStatus::ManualReview);
    expect(reloadlyOrderPosts())->toBeEmpty();
});

it('settles a multi-unit order as partially fulfilled when one unit is refused', function (): void {
    $this->reloadly->forceFill(['fallback_enabled' => false])->save();
    fakeReloadly(Http::sequence()->push(reloadlyTransaction('SUCCESSFUL', '501'))->push(reloadlyTransaction('REFUNDED', '502')));

    $order = fulfil(paidOrderFor($this->variant, 2));

    expect(unitStatuses($order))->toBe([Unit::Delivered, Unit::Failed])
        ->and($order->status)->toBe(OrderStatus::PartiallyFulfilled)
        ->and(reloadlyOrderPosts())->toHaveCount(2);
});

it('treats a duplicate supplier transaction id as ambiguous', function (): void {
    fakeReloadly(Http::response(reloadlyTransaction('SUCCESSFUL', '777')), overrides: [RELOADLY_API.'/orders/transactions/*/cards' => Http::sequence()->push('{"cardNumber":11112222333344445555}')->push('{"cardNumber":99998888777766665555}')]);

    $order = fulfil(paidOrderFor($this->variant, 2));

    expect(SupplierTransaction::query()->orderBy('id')->pluck('status')->all())->toBe([Status::Succeeded, Status::PendingVerification])
        ->and($order->status)->toBe(OrderStatus::PendingVerification)
        ->and(reloadlyOrderPosts())->toHaveCount(2);
});

it('lets staff confirm verified not accepted, then retries with the same client reference', function (): void {
    fakeReloadly(Http::sequence()->push([], 500)->push(reloadlyTransaction('SUCCESSFUL', '808')));
    $order = fulfil(paidOrderFor($this->variant));
    $first = SupplierTransaction::query()->sole();
    $first->forceFill(['verification_deadline_at' => now()->subMinute(), 'next_verification_at' => now()->subMinute()])->save();
    Artisan::call('suppliers:verify-pending');
    expect($order->fresh()->status)->toBe(OrderStatus::ManualReview);

    actingAsStaffWith(['orders.reconcile', 'orders.review']);
    $this->postJson("/admin/api/transactions/{$first->public_id}/mark-not-accepted", ['reason' => 'Supplier support confirmed no order.', 'evidence' => 'Ticket 123 and empty report.'])->assertOk();
    $this->postJson("/admin/api/orders/{$order->public_id}/retry-verified-unit", ['unit_public_id' => OrderItemUnit::query()->value('public_id'), 'reason' => 'Verified not accepted.'])->assertOk();

    $attempts = SupplierTransaction::query()->orderBy('attempt_no')->get();
    expect($attempts->pluck('status')->all())->toBe([Status::NotAcceptedVerified, Status::Succeeded])
        ->and($attempts[1]->client_reference)->toBe($first->client_reference)
        ->and($attempts[0]->resolved_by)->not->toBeNull()
        ->and($order->fresh()->status)->toBe(OrderStatus::Completed);
});

it('refuses to mark a resolved attempt or retry a unit whose attempt is still active', function (): void {
    fakeReloadly(Http::response([], 500));
    $order = fulfil(paidOrderFor($this->variant));
    actingAsStaffWith(['orders.reconcile', 'orders.review']);

    $this->postJson("/admin/api/orders/{$order->public_id}/retry-verified-unit", ['unit_public_id' => OrderItemUnit::query()->value('public_id'), 'reason' => 'Try again please'])->assertConflict();
    $this->postJson('/admin/api/transactions/'.SupplierTransaction::query()->value('public_id').'/mark-not-accepted', ['reason' => 'short'])->assertUnprocessable();

    expect(reloadlyOrderPosts())->toHaveCount(1);
});

it('lets staff force a reconciliation lookup that settles the order', function (): void {
    fakeReloadly(Http::response([], 500), overrides: [RELOADLY_API.'/reports/transactions*' => fn () => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '4242', SupplierTransaction::query()->value('client_reference'))]])]);
    $order = fulfil(paidOrderFor($this->variant));

    actingAsStaffWith(['orders.reconcile']);
    $this->postJson("/admin/api/orders/{$order->public_id}/reconcile")->assertOk()->assertJsonPath('data.attempts_checked', 1);

    expect($order->fresh()->status)->toBe(OrderStatus::Completed)->and(reloadlyOrderPosts())->toHaveCount(1);
});

it('routes direct top-ups to staff tasks (BD2)', function (): void {
    Http::fake();
    $order = fulfil(paidOrderFor($this->variant, type: DeliveryType::DirectTopup));

    expect(unitStatuses($order))->toBe([Unit::ManualTask])
        ->and($order->status)->toBe(OrderStatus::ManualReview)
        ->and(ManualFulfillmentTask::query()->count())->toBe(1);
    Http::assertNothingSent();
});
