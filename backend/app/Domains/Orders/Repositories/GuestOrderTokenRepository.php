<?php

namespace App\Domains\Orders\Repositories;

use App\Domains\Orders\Models\GuestOrderToken;

/**
 * Queries over guest order tokens (ADR-036).
 */
class GuestOrderTokenRepository
{
    /**
     * The unexpired token with this hash, scoped to this order.
     */
    public function findLive(string $tokenHash, int $orderId): ?GuestOrderToken
    {
        return GuestOrderToken::query()
            ->where('token_hash', $tokenHash)
            ->where('order_id', $orderId)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function pruneExpired(): int
    {
        return GuestOrderToken::query()->where('expires_at', '<', now())->delete();
    }
}
