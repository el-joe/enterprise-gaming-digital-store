<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Orders\Services\OrderStateMachine;
use App\Domains\Orders\Services\UnitStatusRecorder;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Staff actions on fulfilment (API_DOCUMENTATION.md section 5): approve a held order, confirm
 * "verified not accepted" with evidence, retry a verified-failed unit, and force a verification
 * lookup. None of them can submit a unit whose previous attempt is still active: the gate and
 * uq_st_active_unit refuse it.
 */
class FulfilmentAdminService
{
    public function __construct(
        private OrderStateMachine $machine,
        private UnitStatusRecorder $units,
        private SupplierVerificationService $verifier,
        private AuditLogger $audit,
        private RefundService $refunds,
    ) {}

    /**
     * @return Collection<int, OrderEvent>
     */
    public function timeline(Order $order): Collection
    {
        return OrderEvent::query()->where('order_id', $order->id)->orderBy('id')->get();
    }

    /**
     * Release an order held in review (risk hold or no supplier) to processing; pending units are
     * then planned by the fulfilment listener.
     */
    public function approve(Order $order, User $staff, string $note): Order
    {
        $order = $this->machine->transition($order, OrderStatus::Processing, OrderActor::staff($staff), $note, ['action' => 'approve']);
        $this->audit->record(new AuditEntry('orders.review_approved', $order, reason: $note));

        return $order;
    }

    /**
     * Reject an order held in review (M14, spec "Fraud review"). Units without an open supplier
     * attempt (pending, queued, in review or failed) become failed. An unpaid order is
     * cancelled (`orders.review`); a paid one has those units refunded to the original method
     * and enters `refund_pending`, which the state machine allows only with `refunds.create`.
     * Delivered units are left as they are (refund them separately). Refused while any unit
     * still has an attempt with the supplier: a second order could double-charge us.
     */
    public function reject(Order $order, User $staff, string $reason, RefundReason $refundReason): Order
    {
        return DB::transaction(function () use ($order, $staff, $reason, $refundReason): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($locked->status !== OrderStatus::ManualReview) {
                throw new ApplicationException('Only an order in manual review can be rejected.', 'conflict', 409);
            }

            $units = OrderItemUnit::query()->where('order_id', $locked->id)->lockForUpdate()->get();
            $active = [Unit::Submitting, Unit::SupplierPending, Unit::PendingVerification];

            if ($units->contains(static fn (OrderItemUnit $unit): bool => in_array($unit->status, $active, true))) {
                throw new ApplicationException('A unit still has an open supplier attempt. Reconcile it first.', 'supplier_attempt_open', 409);
            }

            $actor = OrderActor::staff($staff);
            $rejectable = $units->filter(static fn (OrderItemUnit $unit): bool => in_array($unit->status, [Unit::Pending, Unit::Queued, Unit::ManualReview, Unit::ManualTask, Unit::Failed], true));

            if ($locked->primary_payment_id === null) {
                foreach ($rejectable as $unit) {
                    $this->units->move($unit, Unit::Cancelled, 'review_rejected', $reason, [], $actor);
                }

                $locked = $this->machine->transition($locked, OrderStatus::Cancelled, $actor, $reason, ['action' => 'reject']);
                $this->audit->record(new AuditEntry('orders.review_rejected', $locked, after: ['outcome' => 'cancelled'], reason: $reason));

                return $locked;
            }

            if ($rejectable->isEmpty()) {
                throw new ApplicationException('No undelivered unit is left to refund.', 'conflict', 409);
            }

            if (! $this->machine->canTransition($locked, OrderStatus::RefundPending, $actor)) {
                throw new ApplicationException('Rejecting a paid order refunds it, which needs the refunds.create permission.', 'forbidden', 403);
            }

            foreach ($rejectable as $unit) {
                if ($unit->status !== Unit::Failed) {
                    $this->units->move($unit, Unit::Failed, 'review_rejected', $reason, [], $actor);
                }
            }

            $refund = $this->refunds->refundUnits($locked, $rejectable->pluck('public_id')->all(), $refundReason, $actor, $reason);
            $this->audit->record(new AuditEntry('orders.review_rejected', $locked, after: ['outcome' => 'refund_pending', 'refund_id' => $refund->id], reason: $reason));

            return $locked->refresh();
        }, 3);
    }

    /**
     * Architecture 8.3: a human confirms, with a written reason and evidence, that the supplier did
     * not accept the attempt. Releases the active lock; the unit stays in review until retried.
     */
    public function markNotAccepted(SupplierTransaction $transaction, User $staff, string $reason, string $evidence): SupplierTransaction
    {
        DB::transaction(function () use ($transaction, $staff, $reason, $evidence): void {
            $order = Order::query()->lockForUpdate()->findOrFail(OrderItemUnit::query()->whereKey($transaction->order_item_unit_id)->value('order_id'));
            $unit = OrderItemUnit::query()->lockForUpdate()->findOrFail($transaction->order_item_unit_id);
            $locked = SupplierTransaction::query()->lockForUpdate()->findOrFail($transaction->id);

            if (! in_array($locked->status, [Status::PendingVerification, Status::Accepted, Status::Submitting], true)) {
                throw new ApplicationException('Only an unresolved attempt can be marked not accepted.', 'conflict', 409);
            }

            $locked->forceFill([
                'status' => Status::NotAcceptedVerified,
                'resolved_by' => $staff->id,
                'resolution_note' => "{$reason}\n\nEvidence: {$evidence}",
                'next_verification_at' => null,
                'completed_at' => now(),
            ])->save();

            $actor = OrderActor::staff($staff);

            if ($unit->status !== Unit::ManualReview) {
                $this->units->move($unit, Unit::ManualReview, 'verified_not_accepted', 'Staff verified the supplier did not accept this attempt.', ['supplier_transaction_id' => $locked->id], $actor);
            } else {
                $this->units->note($unit, 'verified_not_accepted', 'Staff verified the supplier did not accept this attempt.', ['supplier_transaction_id' => $locked->id], $actor);
            }

            $this->audit->record(new AuditEntry('suppliers.transaction_not_accepted_verified', $locked, after: ['order_id' => $order->id], reason: $reason));
        }, 3);

        return $transaction->refresh();
    }

    /**
     * Retry a unit whose last attempt is verified failed (rejected or not_accepted_verified). The
     * order goes to processing (staff, `orders.review`) and the unit back to pending, so it is
     * ranked and submitted through the normal gate, reusing its client reference on the same
     * supplier.
     */
    public function retryVerifiedUnit(Order $order, string $unitPublicId, User $staff, string $reason): Order
    {
        $unit = OrderItemUnit::query()->where('public_id', $unitPublicId)->first();

        if ($unit === null || $unit->order_id !== $order->id) {
            throw new ApplicationException('This unit is not part of the order.', 'not_found', 404);
        }

        return DB::transaction(function () use ($order, $unit, $staff, $reason): Order {
            $locked = Order::query()->lockForUpdate()->findOrFail($order->id);
            $lockedUnit = OrderItemUnit::query()->lockForUpdate()->findOrFail($unit->id);
            $last = SupplierTransaction::query()->where('order_item_unit_id', $lockedUnit->id)->orderByDesc('attempt_no')->first();

            $verifiedFailed = in_array($lockedUnit->status, [Unit::Failed, Unit::ManualReview], true)
                && ($last === null || in_array($last->status, [Status::Rejected, Status::NotAcceptedVerified], true));

            if (! $verifiedFailed || ! in_array($locked->status, [OrderStatus::ManualReview, OrderStatus::PartiallyFulfilled], true)) {
                throw new ApplicationException('Only a verified-failed unit on an order in review or partially fulfilled can be retried.', 'conflict', 409);
            }

            $actor = OrderActor::staff($staff);
            $this->units->move($lockedUnit, Unit::Pending, 'retry_requested', $reason, [], $actor);
            $this->audit->record(new AuditEntry('suppliers.unit_retry', $lockedUnit, after: ['order_id' => $locked->id], reason: $reason));

            return $this->machine->transition($locked, OrderStatus::Processing, $actor, 'Staff retried a verified-failed unit.', ['unit_id' => $lockedUnit->id]);
        }, 3);
    }

    /**
     * Bulk "check status now" on selected orders (M14), each run and audited as the single
     * action. Unknown ids are counted as skipped.
     *
     * @param  list<string>  $publicIds
     * @return array{orders: int, attempts_checked: int, skipped: int}
     */
    public function reconcileMany(array $publicIds, User $staff): array
    {
        $orders = Order::query()->whereIn('public_id', $publicIds)->get();
        $checked = 0;

        foreach ($orders as $order) {
            $checked += $this->reconcile($order, $staff);
        }

        return ['orders' => $orders->count(), 'attempts_checked' => $checked, 'skipped' => count($publicIds) - $orders->count()];
    }

    /**
     * Ask the supplier now about every unresolved attempt of the order, past the deadline too.
     *
     * @return int attempts checked
     */
    public function reconcile(Order $order, User $staff): int
    {
        $ids = SupplierTransaction::query()
            ->whereIn('order_item_unit_id', OrderItemUnit::query()->select('id')->where('order_id', $order->id))
            ->whereIn('status', [Status::PendingVerification, Status::Accepted])
            ->pluck('id');

        foreach ($ids as $id) {
            $this->verifier->verify($id, force: true, actor: OrderActor::staff($staff));
        }

        $this->audit->record(new AuditEntry('orders.reconciled', $order, after: ['attempts_checked' => $ids->count()]));

        return $ids->count();
    }
}
