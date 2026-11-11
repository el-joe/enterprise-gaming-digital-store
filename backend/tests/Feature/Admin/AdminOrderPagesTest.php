<?php

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Payments\DTOs\VerifiedPaymentState;
use App\Domains\Payments\Enums\ProviderPaymentStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\PaymentStateApplier;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

/*
| M14: the order detail page (timeline, units, attempts, payments, risk, snapshot) and its
| actions: reconcile, approve, reject, refund, each behind its permission and audited.
*/

beforeEach(function (): void {
    Queue::fake([FulfilOrder::class]);
    $this->gateway = gatewayFor('stripe');
    [$order, $payment] = orderAwaitingPayment($this->gateway, '20.00', units: 2, orderAttributes: ['contact_email' => 'sara.ali@example.com', 'customer_name' => 'Sara Ali']);
    app(PaymentStateApplier::class)->apply($payment->id, new VerifiedPaymentState(ProviderPaymentStatus::Succeeded, Money::of('20', 'USD')), OrderActor::webhook());
    $this->machine = app(OrderStateMachine::class);
    $this->order = $this->machine->transition($order, OrderStatus::Processing, OrderActor::system());
    $this->units = OrderItemUnit::query()->where('order_id', $order->id)->orderBy('id')->get();
});

/**
 * Hold the fixture order for manual review (the risk gate's path).
 */
function holdForReview(Order $order): Order
{
    return app(OrderStateMachine::class)->transition($order, OrderStatus::ManualReview, OrderActor::system(), 'Risk review');
}

describe('order detail page', function (): void {
    it('shows units, payments, snapshot and the append-only timeline, with the email masked', function (): void {
        adminWith(['orders.view']);

        $page = $this->get("/admin/orders/{$this->order->public_id}");

        $page->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee($this->order->order_number)
            ->assertSee('Purchased units')->assertSee('U-2')
            ->assertSee('Payment snapshot')->assertSee('20.00')
            ->assertSee('Event timeline')->assertSee('Status: Paid')->assertSee('Status: Processing')
            ->assertSee('Sara Ali')
            ->assertDontSee('sara.ali@example.com');
        assertCspSafeHtml($page);
    });

    it('shows the full email to holders of customers.view_pii and renders in Arabic', function (): void {
        adminWith(['orders.view', 'customers.view_pii']);

        $this->get("/admin/orders/{$this->order->public_id}")->assertSee('sara.ali@example.com');
        $this->withSession(['admin.locale' => 'ar'])->get("/admin/orders/{$this->order->public_id}")
            ->assertOk()->assertSee('dir="rtl"', false)->assertSee('الوحدات المشتراة')->assertSee('سجل الأحداث');
    });

    it('offers each action only to holders of its permission', function (): void {
        holdForReview($this->order);
        $attempt = SupplierTransaction::factory()->create(['order_item_unit_id' => $this->units[0]->id, 'status' => SupplierTransactionStatus::PendingVerification]);
        $this->units[0]->forceFill(['status' => Unit::PendingVerification])->save();
        $url = "/admin/orders/{$this->order->public_id}";

        adminWith(['orders.view']);
        $this->get($url)->assertOk()
            ->assertSee('Reconciliation needed')->assertSee('Retry (locked)')
            ->assertDontSee('/reconcile"', false)->assertDontSee('/review"', false)->assertDontSee('/reject"', false)
            ->assertDontSee('Refund selected units')->assertDontSee('Mark as verified not accepted');

        adminWith(['orders.view', 'orders.review', 'orders.reconcile', 'refunds.create']);
        $this->get($url)->assertOk()
            ->assertSee('/reconcile"', false)->assertSee('/review"', false)->assertSee('/reject"', false)
            ->assertSee('Mark as verified not accepted')->assertSee('/transactions/'.$attempt->public_id.'/mark-not-accepted', false)
            ->assertSee('Refund selected units');
    });

    it('never shows a code, a provider secret or a delivery value', function (): void {
        $this->gateway->paymentGatewayCredentials()->update(['credentials' => ['secret_key' => ADMIN_CREDENTIAL_CANARY, 'publishable_key' => 'pk_test_x']]);
        adminWith(['orders.view', 'customers.view_pii']);

        expect($this->get("/admin/orders/{$this->order->public_id}")->getContent())->not->toContain(ADMIN_CREDENTIAL_CANARY);
    });
});

describe('order actions', function (): void {
    it('approves a held order with a reason, audited', function (): void {
        holdForReview($this->order);
        $staff = adminWith(['orders.view', 'orders.review']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/review", ['reason' => 'Customer verified by phone'])
            ->assertOk()->assertJsonPath('data.status', 'processing');

        expect(AuditLog::query()->where('action', 'orders.review_approved')->where('actor_id', $staff->id)->sole()->reason)->toBe('Customer verified by phone');
    });

    it('needs a reason to approve or reject, shown on the reason field', function (): void {
        holdForReview($this->order);
        adminWith(['orders.view', 'orders.review']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/review", ['reason' => 'no'])
            ->assertUnprocessable()->assertJsonPath('code', 'validation_failed')->assertJsonValidationErrors('reason');
        $this->postJson("/admin/api/orders/{$this->order->public_id}/reject", ['reason' => '', 'refund_reason' => 'duplicate'])
            ->assertUnprocessable()->assertJsonValidationErrors(['reason', 'refund_reason']);
    });

    it('rejects a paid held order by refunding its undelivered units, audited', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(['id' => 're_m14', 'object' => 'refund', 'amount' => 2000, 'currency' => 'usd', 'status' => 'succeeded', 'metadata' => []])]);
        holdForReview($this->order);
        $staff = adminWith(['orders.view', 'orders.review', 'refunds.create']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/reject", ['reason' => 'Stolen card reported', 'refund_reason' => 'fraud'])->assertOk();

        $refund = Refund::query()->sole();
        expect($refund->reason->value)->toBe('fraud')
            ->and($refund->units()->count())->toBe(2)
            ->and(AuditLog::query()->where('action', 'orders.review_rejected')->where('actor_id', $staff->id)->sole()->after['outcome'])->toBe('refund_pending')
            ->and(AuditLog::query()->where('action', 'refunds.created')->where('actor_id', $staff->id)->count())->toBe(1)
            ->and(OrderEvent::query()->where('order_id', $this->order->id)->where('type', 'unit.review_rejected')->count())->toBe(2);
    });

    it('refuses to reject a paid order without refunds.create and changes nothing', function (): void {
        holdForReview($this->order);
        adminWith(['orders.view', 'orders.review']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/reject", ['reason' => 'Stolen card reported'])->assertForbidden();

        expect($this->order->fresh()->status)->toBe(OrderStatus::ManualReview)
            ->and($this->units->map(fn (OrderItemUnit $unit) => $unit->fresh()->status)->unique()->all())->toBe([Unit::Pending])
            ->and(Refund::query()->count())->toBe(0);
    });

    it('cancels an unpaid held order on reject', function (): void {
        $order = Order::factory()->create(['status' => OrderStatus::ManualReview]);
        $staff = adminWith(['orders.view', 'orders.review']);

        $this->postJson("/admin/api/orders/{$order->public_id}/reject", ['reason' => 'Late payment never arrived'])->assertOk()->assertJsonPath('data.status', 'cancelled');

        expect(AuditLog::query()->where('action', 'orders.review_rejected')->where('actor_id', $staff->id)->sole()->after['outcome'])->toBe('cancelled');
    });

    it('refuses to reject while a unit still has an open supplier attempt', function (): void {
        holdForReview($this->order);
        $this->units[0]->forceFill(['status' => Unit::PendingVerification])->save();
        adminWith(['orders.view', 'orders.review', 'refunds.create']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/reject", ['reason' => 'Stolen card reported'])
            ->assertStatus(409)->assertJsonPath('code', 'supplier_attempt_open');
    });

    it('refuses to reject an order that is not in review', function (): void {
        adminWith(['orders.view', 'orders.review', 'refunds.create']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/reject", ['reason' => 'Stolen card reported'])->assertStatus(409);
    });

    it('runs the supplier check for one order and for selected orders, audited per order', function (): void {
        $other = Order::factory()->create();
        $staff = adminWith(['orders.view', 'orders.reconcile']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/reconcile")->assertOk()->assertJsonPath('data.attempts_checked', 0);
        $this->postJson('/admin/api/tables/orders/bulk/reconcile', ['ids' => [$this->order->public_id, $other->public_id, '01JUNKNOWNUNKNOWNUNKNOWN00']])
            ->assertOk()->assertJsonPath('data.orders', 2)->assertJsonPath('data.skipped', 1);
        $this->postJson('/admin/api/tables/orders/bulk/reconcile', ['ids' => []])->assertUnprocessable()->assertJsonValidationErrors('ids');

        expect(AuditLog::query()->where('action', 'orders.reconciled')->where('actor_id', $staff->id)->count())->toBe(3);
    });

    it('refunds chosen units from the order page endpoint, audited with the note', function (): void {
        fakeStripe(overrides: ['api.stripe.com/v1/refunds*' => Http::response(['id' => 're_m14', 'object' => 'refund', 'amount' => 1000, 'currency' => 'usd', 'status' => 'succeeded', 'metadata' => []])]);
        setUnitStatuses($this->order, [Unit::Delivered, Unit::Delivered]);
        $this->machine->transition($this->order, OrderStatus::Completed, OrderActor::system());
        $staff = adminWith(['orders.view', 'refunds.create']);

        $this->postJson("/admin/api/orders/{$this->order->public_id}/refunds", ['unit_ids' => [$this->units[0]->public_id], 'reason' => 'customer_request', 'note' => 'Code did not redeem in region'])
            ->assertCreated();

        expect(AuditLog::query()->where('action', 'refunds.created')->where('actor_id', $staff->id)->sole()->reason)->toBe('Code did not redeem in region');
    });
});
