<?php

namespace App\Domains\Notifications\Repositories;

use App\Domains\Identity\Contracts\DevicePushTokens;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Notifications\Models\MobilePushToken;

/**
 * Push tokens of signed-in app devices (M-A18, ADR-042). A token is usable only while its sign-in
 * (refresh-token family) is live; the revocation paths delete it, and the prune removes what an
 * expired sign-in left behind.
 */
class PushTokenRepository implements DevicePushTokens
{
    /**
     * Ids of the customer's tokens whose sign-in is live (a refresh token not used, not revoked,
     * not expired), oldest first.
     *
     * @return list<int>
     */
    public function liveIdsForUser(int $userId): array
    {
        return MobilePushToken::query()
            ->where('user_id', $userId)
            ->whereExists(static fn ($live) => $live->selectRaw('1')
                ->from('mobile_refresh_tokens as mrt')
                ->whereColumn('mrt.family_id', 'mobile_push_tokens.family_id')
                ->whereColumn('mrt.user_id', 'mobile_push_tokens.user_id')
                ->whereNull('mrt.revoked_at')
                ->whereNull('mrt.used_at')
                ->where('mrt.expires_at', '>', now()))
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    /**
     * @param  iterable<string>  $familyIds
     */
    public function deleteForFamilies(iterable $familyIds): int
    {
        $ids = collect($familyIds)->filter()->unique()->values()->all();

        return $ids === [] ? 0 : MobilePushToken::query()->whereIn('family_id', $ids)->delete();
    }

    /**
     * Every token of the customer except the one of the kept sign-in (when given).
     */
    public function deleteForUserExcept(int $userId, ?string $keptFamilyId): int
    {
        return MobilePushToken::query()
            ->where('user_id', $userId)
            ->when($keptFamilyId !== null, static fn ($tokens) => $tokens->where('family_id', '!=', $keptFamilyId))
            ->delete();
    }

    /**
     * Tokens whose sign-in has no live refresh token any more (expired or revoked elsewhere).
     */
    public function pruneOrphaned(): int
    {
        return MobilePushToken::query()
            ->whereNotExists(static fn ($live) => $live->selectRaw('1')
                ->from((new MobileRefreshToken)->getTable().' as mrt')
                ->whereColumn('mrt.family_id', 'mobile_push_tokens.family_id')
                ->whereNull('mrt.revoked_at')
                ->whereNull('mrt.used_at')
                ->where('mrt.expires_at', '>', now()))
            ->delete();
    }
}
