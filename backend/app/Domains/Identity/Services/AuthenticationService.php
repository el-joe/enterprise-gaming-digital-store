<?php

namespace App\Domains\Identity\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\DTOs\LoginData;
use App\Domains\Identity\DTOs\LoginResult;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Exceptions\ApplicationException;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    private const string PENDING_KEY = 'auth.two_factor.pending';

    private const int PENDING_TTL_SECONDS = 300;

    public function __construct(
        private TwoFactorService $twoFactor,
        private AuditLogger $audit,
    ) {}

    /**
     * Verify the password, then sign in or start the two-factor challenge.
     */
    public function login(LoginData $data, Session $session): LoginResult
    {
        $guard = Auth::guard('web');

        if (! $guard->once(['email' => $data->email, 'password' => $data->password])) {
            throw $this->failedLogin();
        }

        /** @var User $user */
        $user = $guard->user();

        if ($user->status !== UserStatus::Active) {
            $guard->forgetUser();

            throw $this->failedLogin();
        }

        if ($user->hasTwoFactorEnabled()) {
            $guard->forgetUser();
            $session->migrate(true);
            $session->put(self::PENDING_KEY, [
                'user_id' => $user->id,
                'remember' => $data->remember,
                'expires_at' => now()->addSeconds(self::PENDING_TTL_SECONDS)->getTimestamp(),
            ]);

            return new LoginResult(null, true, false);
        }

        $this->finalizeLogin($user, $data->remember, $data->ip, $session);

        return new LoginResult($user, false, $user->type === UserType::Staff);
    }

    /**
     * Complete a pending two-factor challenge.
     */
    public function completeTwoFactorChallenge(?string $code, ?string $recoveryCode, ?string $ip, Session $session): User
    {
        /** @var array{user_id: int, remember: bool, expires_at: int}|null $pending */
        $pending = $session->get(self::PENDING_KEY);

        if ($pending === null || $pending['expires_at'] < now()->getTimestamp()) {
            $session->forget(self::PENDING_KEY);

            throw new ApplicationException('The login attempt has expired. Please sign in again.', 'two_factor_expired');
        }

        $user = User::query()->whereKey($pending['user_id'])->where('status', UserStatus::Active)->first();

        if ($user === null || ! $this->twoFactor->verifyChallenge($user, $code, $recoveryCode)) {
            throw new ApplicationException('The authentication code is invalid.', 'two_factor_invalid');
        }

        $session->forget(self::PENDING_KEY);
        $this->finalizeLogin($user, $pending['remember'], $ip, $session);
        $session->put(TwoFactorService::SESSION_PASSED_KEY, now()->getTimestamp());

        return $user;
    }

    public function logout(Session $session): void
    {
        Auth::guard('web')->logout();

        $session->invalidate();
        $session->regenerateToken();
    }

    private function finalizeLogin(User $user, bool $remember, ?string $ip, Session $session): void
    {
        Auth::guard('web')->login($user, $remember);
        $session->regenerateToken();

        $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $ip])->save();

        if ($user->type === UserType::Staff) {
            $this->audit->record(new AuditEntry(action: 'auth.staff_login', subject: $user));
        }
    }

    private function failedLogin(): ValidationException
    {
        return ValidationException::withMessages(['email' => [__('auth.failed')]]);
    }
}
