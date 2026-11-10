<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Identity\DTOs\IssuedTokens;
use App\Domains\Identity\Enums\TokenRevocationReason;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\MobileRefreshToken;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\MobileTokenRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Mobile token pairs (ADR-035): a short-lived Sanctum access token with the exact ability
 * `customer`, and a single-use refresh token stored as a SHA-256 hash. Every sign-in starts a
 * family whose absolute expiry rotation never extends. Presenting an already used refresh token
 * revokes the whole family, deletes its access tokens and is audited. Token values are never
 * logged or audited.
 */
class MobileTokenService
{
    public const string ABILITY = 'customer';

    public function __construct(
        private MobileTokenRepository $tokens,
        private AuditLogger $audit,
        private SecurityEventRecorder $securityEvents,
    ) {}

    /**
     * Start a new family for a fresh sign-in. Audits the first sign-in from a device name.
     */
    public function issueForNewSignIn(User $user, string $deviceName, ?string $ip): IssuedTokens
    {
        return DB::transaction(function () use ($user, $deviceName, $ip): IssuedTokens {
            $familyId = (string) Str::ulid();
            $isNewDevice = ! $this->tokens->hasSignedInFromDevice($user->id, $deviceName);

            $issued = $this->issue($user, $familyId, $deviceName, $ip, now()->addDays((int) config('mobile.auth.refresh_token_ttl_days'))->toImmutable());

            if ($isNewDevice) {
                $this->audit->record(new AuditEntry(
                    action: 'auth.mobile_new_device',
                    subject: $user,
                    after: ['device_name' => $deviceName, 'family_id' => $familyId],
                    actorType: AuditLogActorType::Customer,
                    actorId: $user->id,
                ));
            }

            return $issued;
        });
    }

    /**
     * Exchange a refresh token for a new pair. Unknown, expired, revoked and reused tokens all
     * get the same 401 `refresh_token_invalid`.
     */
    public function refresh(string $plainRefreshToken, ?string $ip): IssuedTokens
    {
        /** @var IssuedTokens|TokenRevocationReason|null $outcome */
        $outcome = DB::transaction(function () use ($plainRefreshToken, $ip): IssuedTokens|TokenRevocationReason|null {
            $current = $this->tokens->lockRefreshTokenByHash(self::hash($plainRefreshToken));

            if ($current === null || $current->revoked_at !== null || $current->expires_at->isPast()) {
                return null;
            }

            if ($current->used_at !== null) {
                $this->revokeReusedFamily($current);

                return TokenRevocationReason::Reuse;
            }

            $user = $current->user;

            if ($user === null || $user->status !== UserStatus::Active || $user->isStaff()) {
                $this->tokens->revokeFamily($current->family_id, TokenRevocationReason::AccountInactive);

                return TokenRevocationReason::AccountInactive;
            }

            $current->forceFill(['used_at' => now()])->save();

            if ($current->access_token_id !== null) {
                $this->tokens->deleteAccessTokens([$current->access_token_id]);
            }

            return $this->issue($user, $current->family_id, $current->device_name, $ip, $current->expires_at->toImmutable());
        });

        if (! $outcome instanceof IssuedTokens) {
            throw new ApplicationException('The refresh token is invalid or has expired. Please sign in again.', 'refresh_token_invalid', 401);
        }

        return $outcome;
    }

    /**
     * Sign out the device holding this access token: revoke its family (refresh tokens and
     * access tokens).
     */
    public function revokeCurrent(PersonalAccessToken $accessToken): void
    {
        DB::transaction(function () use ($accessToken): void {
            $refreshToken = $this->tokens->refreshTokenForAccessToken((int) $accessToken->getKey());

            if ($refreshToken !== null) {
                $this->tokens->revokeFamily($refreshToken->family_id, TokenRevocationReason::Logout);
            }

            $accessToken->delete();
        });
    }

    /**
     * Sign the customer out of every device.
     */
    public function revokeAll(User $user): int
    {
        return DB::transaction(fn (): int => $this->tokens->revokeAllForUser($user, TokenRevocationReason::LogoutAll));
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    private function issue(User $user, string $familyId, string $deviceName, ?string $ip, CarbonImmutable $familyExpiresAt): IssuedTokens
    {
        $accessExpiresAt = now()->addMinutes((int) config('mobile.auth.access_token_ttl_minutes'))->toImmutable();
        $access = $user->createToken($deviceName, [self::ABILITY], $accessExpiresAt);

        $plainRefreshToken = Str::random(64);

        MobileRefreshToken::query()->create([
            'user_id' => $user->id,
            'family_id' => $familyId,
            'token_hash' => self::hash($plainRefreshToken),
            'device_name' => $deviceName,
            'access_token_id' => $access->accessToken->getKey(),
            'ip' => $ip,
            'expires_at' => $familyExpiresAt,
        ]);

        return new IssuedTokens($access->plainTextToken, $accessExpiresAt, $plainRefreshToken, $familyExpiresAt);
    }

    private function revokeReusedFamily(MobileRefreshToken $reused): void
    {
        $revoked = $this->tokens->revokeFamily($reused->family_id, TokenRevocationReason::Reuse);

        $this->audit->record(new AuditEntry(
            action: 'auth.mobile_refresh_token_reused',
            subject: $reused->user,
            after: ['family_id' => $reused->family_id, 'device_name' => $reused->device_name, 'revoked_tokens' => $revoked],
            reason: 'A used refresh token was presented again; the token family was revoked.',
            actorType: AuditLogActorType::System,
        ));

        $this->securityEvents->record(
            'auth.refresh_token_reused',
            SecurityEventSeverity::Warning,
            request()->ip(),
            $reused->user_id,
            ['family_id' => $reused->family_id],
        );
    }
}
