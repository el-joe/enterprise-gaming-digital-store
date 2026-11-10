<?php

namespace App\Domains\Support\Repositories;

use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Support\Enums\RefundRequestStatus;
use App\Domains\Support\Models\RefundRequest;
use App\Domains\Support\Models\SupportTicket;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupportTicketRepository
{
    public function __construct(private ListQueryApplier $lists) {}

    /**
     * The customer's own tickets (idx_st_user).
     */
    public function forCustomer(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return SupportTicket::query()->with('order:id,public_id,order_number')->where('user_id', $userId)
            ->orderByDesc('created_at')->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * One of the customer's tickets with its public conversation only. Internal notes are not
     * even loaded for a customer.
     */
    public function findForCustomer(int $userId, string $publicId): ?SupportTicket
    {
        return SupportTicket::query()
            ->with(['order:id,public_id,order_number', 'supportTicketMessages' => static fn ($query) => $query->where('is_internal', false)->orderBy('id')])
            ->where('user_id', $userId)
            ->where('public_id', $publicId)
            ->first();
    }

    public function lock(int $ticketId): SupportTicket
    {
        return SupportTicket::query()->lockForUpdate()->findOrFail($ticketId);
    }

    /**
     * The agent queue (idx_st_queue).
     */
    public function staffPage(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            SupportTicket::query()->with(['order:id,public_id,order_number', 'user:id,name,email', 'assignedTo:id,name']),
            $list,
            ['status' => 'exact', 'priority' => 'exact', 'category' => 'exact', 'assigned_to' => 'exact'],
            ['id', 'created_at', 'last_activity_at', 'priority'],
            ['subject', 'ticket_number'],
            '-last_activity_at',
        );
    }

    /**
     * The whole conversation, internal notes included, for staff.
     */
    public function forStaff(SupportTicket $ticket): SupportTicket
    {
        return $ticket->load([
            'order:id,public_id,order_number,status',
            'user:id,name,email',
            'assignedTo:id,name',
            'supportTicketMessages' => static fn ($query) => $query->orderBy('id'),
        ]);
    }

    public function refundRequestsForCustomer(int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        return RefundRequest::query()->with(['order:id,public_id,order_number', 'refund:id,public_id,status'])
            ->where('user_id', $userId)->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function refundRequestsPage(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            RefundRequest::query()->with(['order:id,public_id,order_number,status', 'user:id,name,email', 'refund:id,public_id,status', 'supportTicket:id,public_id,ticket_number']),
            $list,
            ['status' => 'exact'],
            ['id', 'created_at'],
            [],
            '-id',
        );
    }

    public function hasOpenRefundRequest(int $orderId): bool
    {
        return RefundRequest::query()->where('order_id', $orderId)->where('status', RefundRequestStatus::Requested->value)->exists();
    }
}
