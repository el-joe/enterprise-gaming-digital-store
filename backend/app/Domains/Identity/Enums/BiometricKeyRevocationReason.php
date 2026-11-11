<?php

namespace App\Domains\Identity\Enums;

/**
 * Why a mobile biometric key stopped working (mobile_biometric_keys.revocation_reason).
 */
enum BiometricKeyRevocationReason: string
{
    case RevokedByUser = 'revoked_by_user';
    case Replaced = 'replaced';
    case TooManyFailures = 'too_many_failures';
    case PasswordChanged = 'password_changed';
    case PasswordReset = 'password_reset';
}
