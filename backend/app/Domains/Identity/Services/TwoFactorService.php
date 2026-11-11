<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\DTOs\TwoFactorEnrollment;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * TOTP enrolment and verification. A code is single-use within its validity window.
 */
class TwoFactorService
{
    public const string SESSION_PASSED_KEY = 'auth.two_factor.passed_at';

    private const int RECOVERY_CODE_COUNT = 8;

    private const int REPLAY_WINDOW_SECONDS = 120;

    public function __construct(
        private Google2FA $google2fa,
        private AuditLogger $audit,
    ) {}

    public function beginEnrollment(User $user): TwoFactorEnrollment
    {
        if ($user->hasTwoFactorEnabled()) {
            throw new ApplicationException('Two-factor authentication is already enabled.', 'two_factor_already_enabled', 409);
        }

        $secret = $this->google2fa->generateSecretKey(32);

        $user->forceFill(['two_factor_secret' => $secret])->save();

        return new TwoFactorEnrollment(
            $secret,
            $this->google2fa->getQRCodeUrl((string) config('app.name'), $user->email, $secret),
        );
    }

    /**
     * The `otpauth://` URI of an enrolment that was started and not confirmed yet (the web QR
     * code, M13); null when none is pending.
     */
    public function pendingOtpauthUri(User $user): ?string
    {
        if ($user->hasTwoFactorEnabled() || $user->two_factor_secret === null) {
            return null;
        }

        return $this->google2fa->getQRCodeUrl((string) config('app.name'), $user->email, (string) $user->two_factor_secret);
    }

    /**
     * Confirm enrolment with a live code and return the recovery codes, shown only once.
     *
     * @return list<string>
     */
    public function confirmEnrollment(User $user, string $code): array
    {
        if ($user->two_factor_secret === null || $user->hasTwoFactorEnabled() || ! $this->verifyTotp($user, $code)) {
            throw new ApplicationException('The authentication code is invalid.', 'two_factor_invalid');
        }

        $recoveryCodes = array_map(
            static fn (): string => Str::lower(Str::random(10).'-'.Str::random(10)),
            range(1, self::RECOVERY_CODE_COUNT),
        );

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ])->save();

        $this->audit->record(new AuditEntry(action: 'auth.two_factor_enabled', subject: $user));

        return $recoveryCodes;
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $this->audit->record(new AuditEntry(action: 'auth.two_factor_disabled', subject: $user));
    }

    public function verifyChallenge(User $user, ?string $code, ?string $recoveryCode): bool
    {
        if ($code !== null && $code !== '') {
            return $this->verifyTotp($user, $code);
        }

        return $recoveryCode !== null && $this->consumeRecoveryCode($user, $recoveryCode);
    }

    private function verifyTotp(User $user, string $code): bool
    {
        if (! $this->google2fa->verifyKey((string) $user->two_factor_secret, $code, 1)) {
            return false;
        }

        return Cache::add("two_factor:used:{$user->id}:{$code}", true, self::REPLAY_WINDOW_SECONDS);
    }

    private function consumeRecoveryCode(User $user, string $recoveryCode): bool
    {
        $remaining = $user->two_factor_recovery_codes ?? [];

        foreach ($remaining as $index => $candidate) {
            if (hash_equals($candidate, $recoveryCode)) {
                unset($remaining[$index]);

                $user->forceFill(['two_factor_recovery_codes' => array_values($remaining)])->save();

                return true;
            }
        }

        return false;
    }
}
