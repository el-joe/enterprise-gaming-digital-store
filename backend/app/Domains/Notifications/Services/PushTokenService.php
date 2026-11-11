<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\MobileTokenService;
use App\Domains\Notifications\Enums\PushPlatform;
use App\Domains\Notifications\Models\MobilePushToken;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Registration of the app's FCM token (M-A18, owner decision 9, ADR-042).
 *
 * - A token belongs to the calling sign-in (refresh-token family, ADR-035): one token per device.
 *   Registering again (a refreshed FCM token) replaces it.
 * - The same FCM token is never stored twice: when a device signs in to another account, its
 *   token leaves the previous sign-in, so notifications of the old account stop.
 * - Unregistering (the app's sign-out, or notifications turned off) deletes the device's token.
 *   Logout, sign-out everywhere, device revocation, refresh-token reuse and a password change delete
 *   it as well (Identity, through the `DevicePushTokens` contract).
 * - Both are audited without the token value.
 */
class PushTokenService
{
    public function __construct(
        private MobileTokenService $tokens,
        private AuditLogger $audit,
    ) {}

    /**
     * @throws ApplicationException
     */
    public function register(User $user, PersonalAccessToken $accessToken, PushPlatform $platform, string $token): MobilePushToken
    {
        $familyId = $this->familyOf($accessToken);
        $hash = MobilePushToken::hashToken($token);

        return DB::transaction(function () use ($user, $familyId, $platform, $token, $hash): MobilePushToken {
            $previous = MobilePushToken::query()->where('family_id', $familyId)->lockForUpdate()->first();
            $unchanged = $previous !== null && hash_equals($previous->token_hash, $hash) && $previous->platform === $platform;

            if ($unchanged) {
                $previous->touch();

                return $previous;
            }

            $moved = MobilePushToken::query()->where('token_hash', $hash)->where('family_id', '!=', $familyId)->delete();
            $previous?->delete();

            $device = MobilePushToken::query()->create([
                'user_id' => $user->id,
                'family_id' => $familyId,
                'platform' => $platform,
                'token' => $token,
                'token_hash' => $hash,
            ]);

            $this->audited('account.push_token_registered', $user, ['family_id' => $familyId, 'platform' => $platform->value, 'replaced' => $previous !== null, 'moved_from_other_sign_in' => $moved > 0]);

            return $device;
        });
    }

    /**
     * Delete the calling device's token. Idempotent.
     *
     * @throws ApplicationException
     */
    public function unregister(User $user, PersonalAccessToken $accessToken): void
    {
        $familyId = $this->familyOf($accessToken);
        $deleted = MobilePushToken::query()->where('user_id', $user->id)->where('family_id', $familyId)->delete();

        if ($deleted > 0) {
            $this->audited('account.push_token_removed', $user, ['family_id' => $familyId]);
        }
    }

    /**
     * @throws ApplicationException
     */
    private function familyOf(PersonalAccessToken $accessToken): string
    {
        $familyId = $this->tokens->familyIdForAccessToken($accessToken);

        if ($familyId === null) {
            throw new ApplicationException('Push notifications need a signed-in mobile device.', 'push_not_available', 403);
        }

        return $familyId;
    }

    /**
     * @param  array<string, mixed>  $after
     */
    private function audited(string $action, User $user, array $after): void
    {
        $this->audit->record(new AuditEntry(
            action: $action,
            subject: $user,
            after: $after,
            actorType: AuditLogActorType::Customer,
            actorId: $user->id,
        ));
    }
}
