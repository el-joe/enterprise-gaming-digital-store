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
