<?php

namespace App\Domains\Identity\Repositories;

use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use Illuminate\Support\Collection;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Queries over mobile refresh tokens, login challenges and the customers' Sanctum tokens.
 */
class MobileTokenRepository
{
    public function lockRefreshTokenByHash(string $hash): ?MobileRefreshToken
    {
        return MobileRefreshToken::query()->where('token_hash', $hash)->lockForUpdate()->first();
    }

    public function refreshTokenForAccessToken(int $accessTokenId): ?MobileRefreshToken
    {
        return MobileRefreshToken::query()->where('access_token_id', $accessTokenId)->first();
    }

    /**
     * Whether this customer has signed in from a device of this name before (within the
     * refresh-token retention, index idx_mrt_user_device).
     */
    public function hasSignedInFromDevice(int $userId, string $deviceName): bool
    {
        return MobileRefreshToken::query()->where('user_id', $userId)->where('device_name', $deviceName)->exists();
    }

    /**
     * Revoke every live token of a family and delete the access tokens issued with it.
     */
    public function revokeFamily(string $familyId, TokenRevocationReason $reason): int
    {
        $this->deleteAccessTokens(MobileRefreshToken::query()->where('family_id', $familyId)->whereNotNull('access_token_id')->pluck('access_token_id')->all());

        return MobileRefreshToken::query()
            ->where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'revocation_reason' => $reason->value, 'updated_at' => now()]);
    }

    /**
     * Revoke every family of a customer and delete all of the customer's access tokens.
     */
    public function revokeAllForUser(User $user, TokenRevocationReason $reason): int
    {
        $user->tokens()->delete();

        return MobileRefreshToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'revocation_reason' => $reason->value, 'updated_at' => now()]);
    }

    /**
     * One row per signed-in device of the customer (M-A5): the live refresh token of each family
     * (not used, not revoked, not expired), with the sign-in time of the family and the last use
     * of its current access token.
     *
     * @return Collection<int, MobileRefreshToken>
     */
    public function liveFamiliesForUser(int $userId): Collection
    {
        return MobileRefreshToken::query()
            ->from('mobile_refresh_tokens as live')
            ->leftJoin('personal_access_tokens as pat', 'pat.id', '=', 'live.access_token_id')
            ->where('live.user_id', $userId)
            ->whereNull('live.revoked_at')
            ->whereNull('live.used_at')
            ->where('live.expires_at', '>', now())
            ->select(['live.id', 'live.family_id', 'live.device_name', 'live.ip', 'live.access_token_id', 'live.created_at', 'live.expires_at', 'pat.last_used_at as access_token_last_used_at'])
            ->selectSub(static fn ($family) => $family->from('mobile_refresh_tokens as f')->whereColumn('f.family_id', 'live.family_id')->selectRaw('MIN(f.created_at)'), 'signed_in_at')
            ->orderByDesc('live.created_at')
            ->get();
    }

    /**
     * Whether the customer has a live (signed-in) family with this id.
     */
    public function hasLiveFamily(int $userId, string $familyId): bool
    {
        return MobileRefreshToken::query()
            ->where('user_id', $userId)
            ->where('family_id', $familyId)
            ->whereNull('revoked_at')
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Revoke every family of the customer except one (the caller's), and delete every access
     * token of the customer except one (the caller's). Returns the number of families revoked.
     */
    public function revokeAllForUserExcept(User $user, ?string $keptFamilyId, ?int $keptAccessTokenId, TokenRevocationReason $reason): int
    {
        $user->tokens()->when($keptAccessTokenId !== null, static fn ($tokens) => $tokens->whereKeyNot($keptAccessTokenId))->delete();

        $families = MobileRefreshToken::query()
            ->where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->when($keptFamilyId !== null, static fn ($tokens) => $tokens->where('family_id', '!=', $keptFamilyId))
            ->distinct()
            ->pluck('family_id');

        MobileRefreshToken::query()
            ->where('user_id', $user->id)
            ->whereIn('family_id', $families)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'revocation_reason' => $reason->value, 'updated_at' => now()]);

        return $families->count();
    }

    public function findAccessToken(int $id): ?PersonalAccessToken
    {
        return PersonalAccessToken::query()->find($id);
    }

    /**
     * @param  list<int>  $ids
     */
    public function deleteAccessTokens(array $ids): void
    {
        if ($ids !== []) {
            PersonalAccessToken::query()->whereKey($ids)->delete();
        }
    }

    public function lockChallengeByHash(string $hash): ?MobileLoginChallenge
    {
        return MobileLoginChallenge::query()->where('token_hash', $hash)->lockForUpdate()->first();
    }

    /**
     * Delete refresh tokens, login challenges and customer access tokens past their expiry.
     *
     * @return array{refresh_tokens: int, challenges: int, access_tokens: int}
     */
    public function pruneExpired(): array
    {
        $now = now();

        return [
            'refresh_tokens' => MobileRefreshToken::query()->where('expires_at', '<', $now)->delete(),
            'challenges' => MobileLoginChallenge::query()->where('expires_at', '<', $now)->delete(),
            'access_tokens' => PersonalAccessToken::query()
                ->where('tokenable_type', (new User)->getMorphClass())
                ->where('expires_at', '<', $now)
                ->delete(),
        ];
    }
}
