<?php

namespace App\Domains\Payments\Enums;

enum PaymentEligibilityRuleEffect: string
{
    case Allow = 'allow';
    case Deny = 'deny';
}
