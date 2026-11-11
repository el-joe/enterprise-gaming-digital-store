<?php

namespace App\Domains\Customers\Repositories;

use App\Domains\Audit\Models\AuditLog;
use App\Domains\Customers\Models\CustomerNote;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Reviews\Models\Review;
use App\Domains\Support\Models\SupportTicket;
use Illuminate\Support\Collection;

/**
 * Reads of the admin customer detail page (M15, spec Phase 9.5): fixed queries, each bounded,
 * so the page cost does not grow with the customer's history.
 */
class CustomerAdminRepository
{
    public const int RECENT = 10;

    /**
     * @return Collection<int, Order>
     */
    public function recentOrders(User $customer): Collection
    {
        return Order::query()->where('user_id', $customer->id)
            ->latest('id')->limit(self::RECENT)
            ->get(['id', 'public_id', 'order_number', 'status', 'display_currency', 'display_total', 'created_at']);
    }

    public function ordersCount(User $customer): int
    {
        return Order::query()->where('user_id', $customer->id)->count();
    }

    /**
     * @return Collection<int, SupportTicket>
     */
    public function recentTickets(User $customer): Collection
    {
        return SupportTicket::query()->where('user_id', $customer->id)
            ->latest('id')->limit(self::RECENT)
            ->get(['id', 'public_id', 'ticket_number', 'subject', 'status', 'priority', 'created_at']);
    }

    public function reviewsCount(User $customer): int
    {
        return Review::query()->where('user_id', $customer->id)->count();
    }

    /**
     * @return Collection<int, CustomerNote>
     */
    public function notes(User $customer): Collection
    {
        return CustomerNote::query()->where('user_id', $customer->id)->with('author:id,name')->latest('id')->limit(50)->get();
    }

    /**
     * Biometric keys still able to unlock (OD-1, ADR-040).
     *
     * @return Collection<int, MobileBiometricKey>
     */
    public function biometricKeys(User $customer): Collection
    {
        return MobileBiometricKey::query()->where('user_id', $customer->id)->whereNull('revoked_at')
            ->latest('id')->get(['id', 'public_id', 'family_id', 'algorithm', 'key_fingerprint', 'created_at', 'last_used_at']);
    }

    /**
     * Audit entries about the customer (staff with audit.view).
     *
     * @return Collection<int, AuditLog>
     */
    public function auditTrail(User $customer): Collection
    {
        return AuditLog::query()->where('subject_type', $customer->getMorphClass())->where('subject_id', $customer->id)
            ->latest('id')->limit(20)->get(['id', 'actor_type', 'actor_id', 'action', 'reason', 'created_at']);
    }
}
