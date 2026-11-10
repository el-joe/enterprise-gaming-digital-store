<?php

namespace App\Domains\Identity\Enums;

enum OtpChallengePurpose: string
{
    case GuestOrderAccess = 'guest_order_access';
    case EmailVerification = 'email_verification';
    case SensitiveAction = 'sensitive_action';
}
