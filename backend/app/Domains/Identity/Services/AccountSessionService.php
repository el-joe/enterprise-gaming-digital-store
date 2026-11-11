<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\DTOs\AccountDevice;
use App\Domains\Identity\Enums\AccountDeviceType;
use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Exceptions\CurrentDeviceRevocation;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\MobileTokenRepository;
use App\Domains\Identity\Repositories\WebSessionRepository;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * The customer's signed-in devices and what a password change or reset does to them (M-A5,
 * gap G5). A device is a mobile sign-in (a refresh-token family, ADR-035) or, with the `database`
 * session driver, a browser session. The caller is identified by its access token (bearer) or its
 * session id (web) and is never revoked here: it signs out with logout.
 *
 * Password change keeps only the caller signed in: every other mobile family is revoked
 * (`password_changed`) and its access tokens deleted, every other browser session is deleted
 * (database driver), and the remember-me token is rotated so no "remember me" cookie survives.
 * Password reset (by email link, the caller is not signed in) signs out everything.
 */
class AccountSessionService
{
    public function __construct(
        private MobileTokenRepository $tokens,
        private WebSessionRepository $webSessions,
        private AuditLogger $audit,
        private BiometricAuthService $biometrics,
    ) {}

    /**
     * @return list<AccountDevice>
     */
    public function devices(User $user, ?PersonalAccessToken $currentToken, ?string $currentSessionId): array
    {
        $currentFamily = $this->currentFamilyId($currentToken);

        $mobile = $this->tokens->liveFamiliesForUser($user->id)->map(static fn (MobileRefreshToken $live): AccountDevice => new AccountDevice(
            id: $live->family_id,
            type: AccountDeviceType::Mobile,
            deviceName: $live->device_name,
            userAgent: null,
            ip: $live->ip,
            signedInAt: self::time($live->getAttribute('signed_in_at')),
            lastUsedAt: self::latest(self::time($live->getAttribute('access_token_last_used_at')), $live->created_at?->toImmutable()),
            expiresAt: $live->expires_at?->toImmutable(),
            current: $live->family_id === $currentFamily,
        ));

        $web = $this->webSessions->forUser($user->id)->map(static fn (object $session): AccountDevice => new AccountDevice(
            id: WebSessionRepository::publicId($session->id),
            type: AccountDeviceType::Web,
            deviceName: null,
            userAgent: $session->user_agent === null ? null : Str::limit($session->user_agent, 252),
            ip: $session->ip_address,
            signedInAt: null,
            lastUsedAt: CarbonImmutable::createFromTimestamp((int) $session->last_activity),
            expiresAt: null,
            current: $currentSessionId !== null && hash_equals($session->id, $currentSessionId),
        ));

        $devices = [...$mobile->values()->all(), ...$web->values()->all()];

        $this->audited('account.devices_viewed', $user, ['mobile' => $mobile->count(), 'web' => $web->count(), 'web_sessions_listed' => $this->webSessions->available()]);

        return $devices;
    }

    /**
     * Sign one other device out. Unknown ids and other customers' devices are not found.
     *
     * @throws ModelNotFoundException
     * @throws CurrentDeviceRevocation
     */
    public function revoke(User $user, string $deviceId, ?PersonalAccessToken $currentToken, ?string $currentSessionId): void
    {
        if (str_starts_with($deviceId, 'web_')) {
            if ($currentSessionId !== null && hash_equals(WebSessionRepository::publicId($currentSessionId), $deviceId)) {
                throw new CurrentDeviceRevocation;
            }

            if (! $this->webSessions->deleteByPublicId($user->id, $deviceId)) {
                throw (new ModelNotFoundException)->setModel(MobileRefreshToken::class);
            }

            $this->audited('account.device_revoked', $user, ['type' => AccountDeviceType::Web->value]);

            return;
        }

        if (! $this->tokens->hasLiveFamily($user->id, $deviceId)) {
            throw (new ModelNotFoundException)->setModel(MobileRefreshToken::class);
        }

        if ($deviceId === $this->currentFamilyId($currentToken)) {
            throw new CurrentDeviceRevocation;
        }

        DB::transaction(fn (): int => $this->tokens->revokeFamily($deviceId, TokenRevocationReason::DeviceRevoked));

        $this->audited('account.device_revoked', $user, ['type' => AccountDeviceType::Mobile->value, 'family_id' => $deviceId]);
    }

    /**
     * Set a new password and sign out every other device. Returns what was revoked.
     *
     * @return array{mobile_families: int, web_sessions: int}
     */
    public function changePassword(User $user, string $newPassword, ?PersonalAccessToken $currentToken, ?string $currentSessionId): array
    {
        $revoked = DB::transaction(function () use ($user, $newPassword, $currentToken, $currentSessionId): array {
            $user->forceFill(['password' => $newPassword, 'remember_token' => Str::random(60)])->save();
            // OD-1 (ADR-040): biometric keys are re-enrolled after a password change, on every device.
            $this->biometrics->revokeAllForUser($user, BiometricKeyRevocationReason::PasswordChanged);

            return [
                'mobile_families' => $this->tokens->revokeAllForUserExcept($user, $this->currentFamilyId($currentToken), $currentToken?->getKey() === null ? null : (int) $currentToken->getKey(), TokenRevocationReason::PasswordChanged),
                'web_sessions' => $this->webSessions->deleteAllExcept($user->id, $currentSessionId),
            ];
        });

        $this->audited('account.password_changed', $user, [...$revoked, 'via' => $currentToken !== null ? AccountDeviceType::Mobile->value : AccountDeviceType::Web->value]);

        return $revoked;
    }

    /**
     * "Sign out everywhere else" (M13): every mobile sign-in and browser session except the
     * caller's, and the remember-me token is rotated. The password is unchanged.
     *
     * @return array{mobile_families: int, web_sessions: int}
     */
    public function signOutOthers(User $user, ?PersonalAccessToken $currentToken, ?string $currentSessionId): array
    {
        $revoked = DB::transaction(function () use ($user, $currentToken, $currentSessionId): array {
            $user->forceFill(['remember_token' => Str::random(60)])->save();

            return [
                'mobile_families' => $this->tokens->revokeAllForUserExcept($user, $this->currentFamilyId($currentToken), $currentToken?->getKey() === null ? null : (int) $currentToken->getKey(), TokenRevocationReason::LogoutAll),
                'web_sessions' => $this->webSessions->deleteAllExcept($user->id, $currentSessionId),
            ];
        });

        $this->audited('account.signed_out_others', $user, $revoked);

        return $revoked;
    }

    /**
     * After a password reset by email: no device stays signed in.
     *
     * @return array{mobile_families: int, web_sessions: int}
     */
    public function signOutEverywhere(User $user, TokenRevocationReason $reason): array
    {
        $this->biometrics->revokeAllForUser($user, BiometricKeyRevocationReason::PasswordReset);

        return [
            'mobile_families' => $this->tokens->revokeAllForUserExcept($user, null, null, $reason),
            'web_sessions' => $this->webSessions->deleteAllExcept($user->id, null),
        ];
    }

    private function currentFamilyId(?PersonalAccessToken $token): ?string
    {
        return $token === null ? null : $this->tokens->refreshTokenForAccessToken((int) $token->getKey())?->family_id;
    }

    private static function time(mixed $value): ?CarbonImmutable
    {
        return $value === null ? null : CarbonImmutable::parse($value);
    }

    private static function latest(?CarbonImmutable $a, ?CarbonImmutable $b): ?CarbonImmutable
    {
        return match (true) {
            $a === null => $b,
            $b === null => $a,
            default => $a->greaterThan($b) ? $a : $b,
        };
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
