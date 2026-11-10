<?php

namespace App\Domains\Affiliates\Enums;

enum AffiliateStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Suspended = 'suspended';
}
