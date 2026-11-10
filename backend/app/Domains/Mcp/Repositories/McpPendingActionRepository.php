<?php

namespace App\Domains\Mcp\Repositories;

use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Models\McpPendingAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class McpPendingActionRepository
{
    /**
     * A live pending action of the account with the same arguments hash (idx_mpa_dedupe).
     */
    public function livePendingDuplicate(int $serviceAccountId, string $argumentsHash): ?McpPendingAction
    {
        return McpPendingAction::query()
            ->where('service_account_id', $serviceAccountId)
            ->where('arguments_hash', $argumentsHash)
            ->where('status', McpPendingActionStatus::Pending)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function findForAccount(string $publicId, int $serviceAccountId): ?McpPendingAction
    {
        return McpPendingAction::query()->where('public_id', $publicId)->where('service_account_id', $serviceAccountId)->first();
    }

    public function lock(McpPendingAction $action): McpPendingAction
    {
        return McpPendingAction::query()->whereKey($action->id)->lockForUpdate()->firstOrFail();
    }

    /**
     * Admin queue: newest first, optionally one status.
     *
     * @return LengthAwarePaginator<int, McpPendingAction>
     */
    public function adminPage(?McpPendingActionStatus $status, int $page, int $perPage): LengthAwarePaginator
    {
        return McpPendingAction::query()
            ->with(['serviceAccount:id,name', 'approvedBy:id,name'])
            ->when($status !== null, static fn ($query) => $query->where('status', $status))
            ->orderByDesc('id')
            ->paginate($perPage, page: $page);
    }

    /**
     * @return Collection<int, int>
     */
    public function dueForExpiry(int $limit): Collection
    {
        return McpPendingAction::query()
            ->where('status', McpPendingActionStatus::Pending)
            ->where('expires_at', '<=', now())
            ->orderBy('expires_at')
            ->limit($limit)
            ->pluck('id');
    }

    public function expire(int $id): int
    {
        return McpPendingAction::query()
            ->whereKey($id)
            ->where('status', McpPendingActionStatus::Pending)
            ->update(['status' => McpPendingActionStatus::Expired, 'confirmation_hash' => null, 'updated_at' => now()]);
    }
}
