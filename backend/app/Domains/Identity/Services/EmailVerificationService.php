<?php

namespace App\Domains\Identity\Services;

use App\Domains\Identity\Models\User;
use Illuminate\Auth\Events\Verified;

/**
 * Email verification by the signed link alone (M-A5, gap G10): the link works from any mail
 * client, with no session or token, because the signature (checked by the caller) binds the user
 * id and the hash of the address, and the hash must still match the account's current address.
 * Opening it signs nobody in.
 */
class EmailVerificationService
{
    public const string VERIFIED = 'verified';

    public const string ALREADY_VERIFIED = 'already_verified';

    public const string INVALID = 'invalid';

    /**
     * `verified`, `already_verified`, or `invalid` for an unknown user or a hash that does not
     * match the current address.
     */
    public function verify(string $userId, string $hash): string
    {
        $user = ctype_digit($userId) ? User::query()->find((int) $userId) : null;

        if ($user === null || ! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return self::INVALID;
        }

        if ($user->hasVerifiedEmail()) {
            return self::ALREADY_VERIFIED;
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return self::VERIFIED;
    }
}
