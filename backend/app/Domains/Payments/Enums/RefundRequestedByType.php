<?php

namespace App\Domains\Payments\Enums;

enum RefundRequestedByType: string
{
    case System = 'system';
    case Staff = 'staff';
}
