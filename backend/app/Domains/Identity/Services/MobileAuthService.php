<?php

namespace App\Domains\Identity\Services;

use App\Domains\Identity\DTOs\IssuedTokens;
use App\Domains\Identity\DTOs\MobileLoginData;
use App\Domains\Identity\DTOs\MobileLoginResult;
use App\Domains\Identity\DTOs\RegistrationData;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\MobileLoginChallenge;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Repositories\MobileTokenRepository;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Customer sign-in for the mobile app (M-A2, ADR-035). Credentials, account status and the
 * failed-login error come from AuthenticationService and codes from TwoFactorService, so the web
 * and the app accept and refuse the same accounts; the app additionally refuses staff. The
 * pending two-factor state is a hashed, short-lived, single-use challenge row, not a session.
 */
class MobileAuthService
{
    public function __construct(
        private AuthenticationService $authentication,
        private RegistrationService $registration,
        private TwoFactorService $twoFactor,
        private MobileTokenService $tokens,
        private MobileTokenRepository $repository,
    ) {}

    /**
     * @return array{user: User, tokens: IssuedTokens}
     */
    public function register(RegistrationData $data, string $deviceName, ?string $ip): array
    {
        $user = $this->registration->createCustomer($data);

        $this->authentication->recordSuccessfulLogin($user, $ip);

        return ['user' => $user, 'tokens' => $this->tokens->issueForNewSignIn($user, $deviceName, $ip)];
    }

    public function login(MobileLoginData $data): MobileLoginResult
    {
        $user = $this->authentication->verifyCredentials($data->credentials());

        if ($user->isStaff()) {
            throw $this->authentication->failedLogin();
        }

        if ($user->hasTwoFactorEnabled()) {
            $challengeToken = Str::random(64);
            $expiresAt = now()->addSeconds((int) config('mobile.auth.challenge_ttl_seconds'))->toImmutable();

            MobileLoginChallenge::query()->create([
                'user_id' => $user->id,
                'token_hash' => MobileTokenService::hash($challengeToken),
                'device_name' => $data->deviceName,
                'expires_at' => $expiresAt,
            ]);

            return new MobileLoginResult(null, null, $challengeToken, $expiresAt);
        }

        $this->authentication->recordSuccessfulLogin($user, $data->ip);

        return new MobileLoginResult($user, $this->tokens->issueForNewSignIn($user, $data->deviceName, $data->ip));
    }

    /**
     * Complete a pending two-factor sign-in. A missing, spent or expired challenge gets
     * `two_factor_expired`; a wrong code `two_factor_invalid` (the challenge is spent after
     * `mobile.auth.challenge_max_attempts` wrong codes).
     *
     * @return array{user: User, tokens: IssuedTokens}
     */
    public function completeTwoFactorChallenge(string $challengeToken, ?string $code, ?string $recoveryCode, ?string $ip): array
    {
        /** @var array{user: User, device_name: string}|string $outcome */
        $outcome = DB::transaction(function () use ($challengeToken, $code, $recoveryCode): array|string {
            $challenge = $this->repository->lockChallengeByHash(MobileTokenService::hash($challengeToken));

            if ($challenge === null || $challenge->consumed_at !== null || $challenge->expires_at->isPast()) {
                return 'two_factor_expired';
            }

            $user = $challenge->user;

            if ($user === null || $user->status !== UserStatus::Active || $user->isStaff()) {
                $challenge->forceFill(['consumed_at' => now()])->save();

                return 'two_factor_expired';
            }

            if (! $this->twoFactor->verifyChallenge($user, $code, $recoveryCode)) {
                $challenge->attempts++;

                if ($challenge->attempts >= (int) config('mobile.auth.challenge_max_attempts')) {
                    $challenge->consumed_at = now();
                }

                $challenge->save();

                return 'two_factor_invalid';
            }

            $challenge->forceFill(['consumed_at' => now()])->save();

            return ['user' => $user, 'device_name' => $challenge->device_name];
        });

        if ($outcome === 'two_factor_expired') {
            throw new ApplicationException('The login attempt has expired. Please sign in again.', 'two_factor_expired');
        }

        if ($outcome === 'two_factor_invalid') {
            throw new ApplicationException('The authentication code is invalid.', 'two_factor_invalid');
        }

        $this->authentication->recordSuccessfulLogin($outcome['user'], $ip);

        return ['user' => $outcome['user'], 'tokens' => $this->tokens->issueForNewSignIn($outcome['user'], $outcome['device_name'], $ip)];
    }
}
