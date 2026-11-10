<?php

namespace App\Domains\Identity\Services;

use Carbon\CarbonImmutable;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Recent authentication before sensitive actions such as a code reveal. A web session keeps the
 * confirmation time in the framework's `auth.password_confirmed_at` key; a bearer client keeps it
 * on its access token (`personal_access_tokens.password_confirmed_at`, M-A3, ADR-036). Both use
 * the same window, `fulfillment.reveal_recent_auth_minutes`.
 */
class RecentAuthService
{
    public const string SESSION_KEY = 'auth.password_confirmed_at';

    public function windowMinutes(): int
    {
        return (int) config('fulfillment.reveal_recent_auth_minutes');
    }

    public function isRecent(?int $confirmedAtTimestamp): bool
    {
        return $confirmedAtTimestamp !== null && $confirmedAtTimestamp > 0
            && now()->getTimestamp() - $confirmedAtTimestamp <= 60 * $this->windowMinutes();
    }

    public function confirmToken(PersonalAccessToken $token): void
    {
        $token->forceFill(['password_confirmed_at' => now()])->save();
    }

    public function tokenIsRecent(PersonalAccessToken $token): bool
    {
        return $this->isRecent(self::tokenConfirmedAt($token)?->getTimestamp());
    }

    /**
     * Keep the confirmation time when a refresh replaces the access token of the same sign-in;
     * the window still runs from the original confirmation.
     */
    public function carryOver(?PersonalAccessToken $from, PersonalAccessToken $to): void
    {
        $confirmedAt = $from === null ? null : self::tokenConfirmedAt($from);

        if ($confirmedAt !== null) {
            $to->forceFill(['password_confirmed_at' => $confirmedAt])->save();
        }
    }

    private static function tokenConfirmedAt(PersonalAccessToken $token): ?CarbonImmutable
    {
        $value = $token->getAttribute('password_confirmed_at');

        return $value === null ? null : CarbonImmutable::parse($value);
    }
}
