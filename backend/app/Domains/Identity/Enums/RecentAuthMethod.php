<?php

namespace App\Domains\Identity\Enums;

/**
 * How a bearer token's recent authentication was given (OD-1, ADR-040). Both satisfy the reveal
 * re-auth (423); only a password confirmation may register a biometric key.
 */
enum RecentAuthMethod: string
{
    case Password = 'password';
    case Biometric = 'biometric';
}
