<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Audit\Services\SecurityEventRecorder;
use App\Domains\Identity\DTOs\BiometricChallenge;
use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use App\Domains\Identity\Enums\RecentAuthMethod;
use App\Domains\Identity\Models\MobileBiometricChallenge;
use App\Domains\Identity\Models\MobileBiometricKey;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\MobileTokenRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Biometric re-authentication for the mobile app (owner decisions 5 and 6, OD-1, ADR-040).
 *
 * The app creates an ECDSA P-256 key pair in the device's secure hardware (Android Keystore /
 * iOS Secure Enclave) whose private key is usable only after a biometric prompt, and registers
 * the public key here. To satisfy the reveal re-auth (423) it asks for a challenge, signs it after
 * the prompt, and posts the signature; a valid signature sets `password_confirmed_at` on the
 * current access token, exactly as a password confirmation does, with the same window. The
 * password is never stored on the device.
 *
 * Binding and revocation:
 * - a key belongs to one sign-in (refresh-token family). It works only while that family is
 *   signed in, so logout, device revocation, refresh-token reuse and sign-out everywhere end it;
 * - registering needs a recent *password* confirmation on this token (a biometric confirmation
 *   cannot enrol another key) and replaces the family's previous key;
 * - a password change or reset revokes every key of the customer;
 * - a challenge is single use, lives `mobile.biometric.challenge_ttl_seconds`, and is bound to
 *   the key and to the access token that asked for it; every presentation consumes it;
 * - `mobile.biometric.max_failed_attempts` consecutive bad signatures revoke the key.
 */
class BiometricAuthService
{
    public const string ALGORITHM = 'ES256';

    /**
     * DER prefix of a P-256 SubjectPublicKeyInfo, before the 65-byte uncompressed point.
     */
    private const string P256_SPKI_PREFIX = '3059301306072a8648ce3d020106082a8648ce3d030107034200';

    private const string CHALLENGE_PREFIX = 'boxcards-biometric-v1';

    public function __construct(
        private MobileTokenRepository $tokens,
        private RecentAuthService $recentAuth,
        private AuditLogger $audit,
        private SecurityEventRecorder $securityEvents,
    ) {}

    /**
     * @throws ApplicationException
     */
    public function register(User $user, PersonalAccessToken $token, string $encodedPublicKey, ?string $ip): MobileBiometricKey
    {
        if (! $this->recentAuth->tokenHasRecentPassword($token)) {
            throw new ApplicationException('Confirm your password to turn on biometric unlock.', 'recent_auth_required', 423);
        }

        $familyId = $this->familyOf($token);
        $pem = $this->publicKeyPem($encodedPublicKey);

        $key = DB::transaction(function () use ($user, $familyId, $pem): MobileBiometricKey {
            $replaced = $this->revokeWhere(MobileBiometricKey::query()->where('family_id', $familyId), BiometricKeyRevocationReason::Replaced);

            $key = MobileBiometricKey::query()->create([
                'public_id' => (string) Str::ulid(),
                'user_id' => $user->id,
                'family_id' => $familyId,
                'algorithm' => self::ALGORITHM,
                'public_key' => $pem,
                'key_fingerprint' => hash('sha256', $pem),
            ]);

            $this->audited('auth.biometric_key_registered', $user, ['key' => $key->public_id, 'family_id' => $familyId, 'replaced' => $replaced]);

            return $key;
        });

        $this->securityEvents->record('auth.biometric_key_registered', SecurityEventSeverity::Info, $ip, $user->id, ['key' => $key->public_id]);

        return $key;
    }

    /**
     * Turn biometric unlock off for the calling device. Idempotent.
     *
     * @throws ApplicationException
     */
    public function revokeCurrent(User $user, PersonalAccessToken $token): void
    {
        $familyId = $this->familyOf($token);
        $revoked = $this->revokeWhere(MobileBiometricKey::query()->where('user_id', $user->id)->where('family_id', $familyId), BiometricKeyRevocationReason::RevokedByUser);

        $this->audited('auth.biometric_key_revoked', $user, ['family_id' => $familyId, 'revoked' => $revoked, 'reason' => BiometricKeyRevocationReason::RevokedByUser->value]);
    }

    /**
     * Every key of the customer (password changed or reset). Returns the number revoked.
     */
    public function revokeAllForUser(User $user, BiometricKeyRevocationReason $reason): int
    {
        return $this->revokeWhere(MobileBiometricKey::query()->where('user_id', $user->id), $reason);
    }

    /**
     * @throws ApplicationException
     */
    public function issueChallenge(User $user, PersonalAccessToken $token): BiometricChallenge
    {
        $key = $this->liveKey($user, $this->familyOf($token));
        $expiresAt = now()->addSeconds((int) config('mobile.biometric.challenge_ttl_seconds'))->toImmutable();
        $challenge = implode('.', [self::CHALLENGE_PREFIX, $key->public_id, $token->getKey(), Str::random(43)]);

        MobileBiometricChallenge::query()->create([
            'mobile_biometric_key_id' => $key->id,
            'access_token_id' => $token->getKey(),
            'challenge_hash' => hash('sha256', $challenge),
            'expires_at' => $expiresAt,
        ]);

        return new BiometricChallenge($challenge, $expiresAt);
    }

    /**
     * Verify a signed challenge and mark the access token as recently authenticated. Returns the
     * window in minutes.
     *
     * @throws ApplicationException
     */
    public function confirm(User $user, PersonalAccessToken $token, string $challenge, string $encodedSignature, ?string $ip): int
    {
        $outcome = DB::transaction(function () use ($user, $token, $challenge, $encodedSignature): array {
            $row = MobileBiometricChallenge::query()->where('challenge_hash', hash('sha256', $challenge))->lockForUpdate()->first();
            $key = $row === null ? null : MobileBiometricKey::query()->whereKey($row->mobile_biometric_key_id)->lockForUpdate()->first();

            if ($row === null || $key === null || $row->consumed_at !== null || $row->expires_at->isPast()
                || (int) $row->access_token_id !== (int) $token->getKey() || $key->user_id !== $user->id) {
                return ['result' => 'challenge_invalid', 'key' => $key];
            }

            $row->forceFill(['consumed_at' => now()])->save();

            if ($key->revoked_at !== null || ! $this->tokens->hasLiveFamily($user->id, $key->family_id) || $key->family_id !== $this->familyOf($token)) {
                return ['result' => 'challenge_invalid', 'key' => $key];
            }

            if (! $this->signatureIsValid($challenge, $encodedSignature, $key->public_key)) {
                $failures = $key->failed_attempts + 1;
                $locked = $failures >= (int) config('mobile.biometric.max_failed_attempts');
                $key->forceFill([
                    'failed_attempts' => $failures,
                    'revoked_at' => $locked ? now() : null,
                    'revocation_reason' => $locked ? BiometricKeyRevocationReason::TooManyFailures : null,
                ])->save();

                return ['result' => $locked ? 'locked' : 'signature_invalid', 'key' => $key];
            }

            $key->forceFill(['failed_attempts' => 0, 'last_used_at' => now()])->save();
            $this->recentAuth->confirmToken($token, RecentAuthMethod::Biometric);

            return ['result' => 'confirmed', 'key' => $key];
        });

        /** @var MobileBiometricKey|null $key */
        $key = $outcome['key'];
        $context = ['key' => $key?->public_id, 'result' => $outcome['result']];

        if ($outcome['result'] === 'confirmed') {
            $this->audited('auth.biometric_confirmed', $user, $context);

            return $this->recentAuth->windowMinutes();
        }

        $this->audited('auth.biometric_failed', $user, $context);
        $this->securityEvents->record('auth.biometric_failed', $outcome['result'] === 'locked' ? SecurityEventSeverity::Warning : SecurityEventSeverity::Info, $ip, $user->id, $context);

        throw match ($outcome['result']) {
            'locked' => new ApplicationException('Biometric unlock was turned off after too many failed attempts. Confirm your password instead.', 'biometric_locked', 423),
            'signature_invalid' => new ApplicationException('Biometric confirmation failed. Try again or confirm your password.', 'biometric_failed', 422),
            default => new ApplicationException('This biometric challenge is not valid. Request a new one.', 'biometric_challenge_invalid', 422),
        };
    }

    /**
     * @throws ApplicationException
     */
    private function liveKey(User $user, string $familyId): MobileBiometricKey
    {
        $key = MobileBiometricKey::query()->where('user_id', $user->id)->where('family_id', $familyId)->whereNull('revoked_at')->latest('id')->first();

        if ($key === null) {
            throw new ApplicationException('Biometric unlock is not set up on this device. Confirm your password instead.', 'biometric_not_registered', 404);
        }

        return $key;
    }

    /**
     * The sign-in (refresh-token family) of a mobile access token.
     *
     * @throws ApplicationException
     */
    private function familyOf(PersonalAccessToken $token): string
    {
        $familyId = $this->tokens->refreshTokenForAccessToken((int) $token->getKey())?->family_id;

        if ($familyId === null) {
            throw new ApplicationException('Biometric unlock needs a signed-in mobile device.', 'biometric_not_available', 403);
        }

        return $familyId;
    }

    /**
     * Accepts base64 (or base64url) of a DER SubjectPublicKeyInfo (Android `getEncoded()`) or of
     * the 65-byte uncompressed point (iOS `SecKeyCopyExternalRepresentation`), P-256 only.
     *
     * @throws ApplicationException
     */
    private function publicKeyPem(string $encoded): string
    {
        $der = self::base64Decode($encoded);

        if ($der !== null && strlen($der) === 65 && $der[0] === "\x04") {
            $der = hex2bin(self::P256_SPKI_PREFIX).$der;
        }

        $pem = $der === null ? null : "-----BEGIN PUBLIC KEY-----\n".chunk_split(base64_encode($der), 64, "\n")."-----END PUBLIC KEY-----\n";
        $key = $pem === null ? false : @openssl_pkey_get_public($pem);
        $details = $key === false ? false : openssl_pkey_get_details($key);

        if ($details === false || ($details['type'] ?? null) !== OPENSSL_KEYTYPE_EC || ($details['ec']['curve_name'] ?? null) !== 'prime256v1') {
            throw new ApplicationException('The public key must be an ECDSA P-256 key.', 'validation_failed', 422, ['public_key' => [__('The public key must be an ECDSA P-256 key.')]]);
        }

        return (string) $details['key'];
    }

    private function signatureIsValid(string $challenge, string $encodedSignature, string $pem): bool
    {
        $signature = self::base64Decode($encodedSignature);

        return $signature !== null && @openssl_verify($challenge, $signature, $pem, OPENSSL_ALGO_SHA256) === 1;
    }

    private static function base64Decode(string $value): ?string
    {
        $value = strtr(trim($value), '-_', '+/');
        $decoded = base64_decode($value.str_repeat('=', (4 - strlen($value) % 4) % 4), true);

        return $decoded === false || $decoded === '' ? null : $decoded;
    }

    /**
     * @param  Builder<MobileBiometricKey>  $query
     */
    private function revokeWhere(Builder $query, BiometricKeyRevocationReason $reason): int
    {
        return $query->whereNull('revoked_at')->update(['revoked_at' => now(), 'revocation_reason' => $reason->value, 'updated_at' => now()]);
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
