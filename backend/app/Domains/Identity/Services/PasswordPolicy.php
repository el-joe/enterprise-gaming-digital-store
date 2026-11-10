<?php

namespace App\Domains\Identity\Services;

use Illuminate\Validation\Rules\Password;

/**
 * Password strength rules (SECURITY.md section 1).
 */
class PasswordPolicy
{
    public static function forCustomer(): Password
    {
        return Password::min(10)->uncompromised();
    }

    public static function forStaff(): Password
    {
        return Password::min(12)->letters()->mixedCase()->numbers()->uncompromised();
    }
}
