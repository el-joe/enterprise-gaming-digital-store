<?php

namespace App\Domains\Notifications\Repositories;

use App\Domains\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Notifications\DatabaseNotification;

/**
 * The customer's notification inbox (M-A5, gap G6): Laravel `database` channel rows of the
 * customer, newest first. Another customer's notification is never found.
 */
class InboxRepository
{
    /**
     * @return LengthAwarePaginator<int, DatabaseNotification>
     */
    public function paginate(User $user, bool $unreadOnly, int $page, int $perPage): LengthAwarePaginator
    {
        return $user->notifications()
            ->when($unreadOnly, static fn ($notifications) => $notifications->whereNull('read_at'))
            ->reorder()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function unreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Mark one of the customer's notifications read (idempotent); null when it is not theirs.
     */
    public function markRead(User $user, string $id): ?DatabaseNotification
    {
        /** @var DatabaseNotification|null $notification */
        $notification = $user->notifications()->whereKey($id)->first();
        $notification?->markAsRead();

        return $notification;
    }

    /**
     * Mark every unread notification of the customer read; returns how many.
     */
    public function markAllRead(User $user): int
    {
        return $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
