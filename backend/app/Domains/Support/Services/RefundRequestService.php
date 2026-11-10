<?php

namespace App\Domains\Support\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Enums\RefundDestination;
use App\Domains\Payments\Enums\RefundReason;
use App\Domains\Payments\Enums\RefundStatus;
use App\Domains\Payments\Models\Refund;
use App\Domains\Payments\Services\RefundService;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Support\Enums\RefundRequestStatus;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use App\Domains\Support\Repositories\SupportTicketRepository;
use Illuminate\Support\Facades\DB;

/**
 * Customer refund requests (BD4: anything beyond undelivered units is at staff discretion). A
 * customer may ask once at a time per paid order of their own. Staff approve by choosing the
 * units, which creates the refund through the M10 RefundService (permission `refunds.create`,
 * all its limits apply), or reject with a reason. An approved request becomes `completed` once
 * its refund has succeeded.
 */
class RefundRequestService
{
    public function __construct(
        private SupportTicketRepository $tickets,
        private RefundService $refunds,
        private AuditLogger $audit,
    ) {}

    public function request(User $customer, string $orderPublicId, string $reason, ?string $ticketPublicId): RefundRequest
    {
        $order = Order::query()->where('public_id', $orderPublicId)->where('user_id', $customer->id)->first()
            ?? throw new ApplicationException('This order was not found.', 'validation_failed', 422, ['order_public_id' => ['This order was not found.']]);

        if ($order->primary_payment_id === null) {
            throw new ApplicationException('Only a paid order can be refunded.', 'refund_not_allowed', 409);
        }

        $ticketId = null;

        if ($ticketPublicId !== null) {
            $ticketId = SupportTicket::query()->where('public_id', $ticketPublicId)->where('user_id', $customer->id)->value('id')
                ?? throw new ApplicationException('This ticket was not found.', 'validation_failed', 422, ['support_ticket_public_id' => ['This ticket was not found.']]);
        }

        return DB::transaction(function () use ($order, $customer, $reason, $ticketId): RefundRequest {
            Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($this->tickets->hasOpenRefundRequest($order->id)) {
                throw new ApplicationException('A refund request for this order is already open.', 'refund_request_open', 409);
            }

            $request = (new RefundRequest)->forceFill([
                'order_id' => $order->id,
                'support_ticket_id' => $ticketId,
                'user_id' => $customer->id,
                'reason' => trim(strip_tags($reason)),
                'status' => RefundRequestStatus::Requested,
            ]);
            $request->save();

            return $request->load(['order:id,public_id,order_number', 'refund:id,public_id,status']);
        });
    }

    /**
     * @param  list<string>  $unitPublicIds
     */
    public function approve(RefundRequest $request, User $staff, array $unitPublicIds, RefundReason $reason, string $note, RefundDestination $destination): RefundRequest
    {
        DB::transaction(function () use ($request, $staff, $unitPublicIds, $reason, $note, $destination): void {
            $order = Order::query()->lockForUpdate()->findOrFail($request->order_id);
            $locked = RefundRequest::query()->lockForUpdate()->findOrFail($request->id);

            if ($locked->status !== RefundRequestStatus::Requested) {
                throw new ApplicationException('This refund request was already decided.', 'conflict', 409);
            }

            $refund = $this->refunds->refundUnits($order, $unitPublicIds, $reason, OrderActor::staff($staff), $note, $destination);

            $locked->forceFill(['status' => RefundRequestStatus::Approved, 'reviewed_by' => $staff->id, 'refund_id' => $refund->id])->save();
            $this->audit->record(new AuditEntry(action: 'support.refund_request.approved', subject: $locked, after: ['refund_id' => $refund->id, 'units' => count($unitPublicIds)], reason: $note));
        });

        return $this->detailed($request);
    }

    public function reject(RefundRequest $request, User $staff, string $note): RefundRequest
    {
        DB::transaction(function () use ($request, $staff, $note): void {
            $locked = RefundRequest::query()->lockForUpdate()->findOrFail($request->id);

            if ($locked->status !== RefundRequestStatus::Requested) {
                throw new ApplicationException('This refund request was already decided.', 'conflict', 409);
            }

            $locked->forceFill(['status' => RefundRequestStatus::Rejected, 'reviewed_by' => $staff->id])->save();
            $this->audit->record(new AuditEntry(action: 'support.refund_request.rejected', subject: $locked, reason: $note));
        });

        return $this->detailed($request);
    }

    /**
     * Approved requests whose refund has succeeded are complete.
     */
    public function completeSettled(int $orderId): void
    {
        RefundRequest::query()
            ->where('order_id', $orderId)
            ->where('status', RefundRequestStatus::Approved->value)
            ->whereIn('refund_id', Refund::query()->select('id')->where('order_id', $orderId)->where('status', RefundStatus::Succeeded->value))
            ->update(['status' => RefundRequestStatus::Completed->value, 'updated_at' => now()]);
    }

    public function detailed(RefundRequest $request): RefundRequest
    {
        return $request->refresh()->load(['order:id,public_id,order_number,status', 'user:id,name,email', 'refund:id,public_id,status', 'supportTicket:id,public_id,ticket_number']);
    }
}
