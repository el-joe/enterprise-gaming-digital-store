<?php

namespace App\Domains\Identity\Enums;

/**
 * Why a mobile refresh-token family was revoked (mobile_refresh_tokens.revocation_reason).
 */
enum TokenRevocationReason: string
{
    case Logout = 'logout';
    case LogoutAll = 'logout_all';
    case Reuse = 'reuse';
    case AccountInactive = 'account_inactive';
}
