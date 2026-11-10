<?php

namespace App\Domains\Promotions\Enums;

enum CouponCreatedByType: string
{
    case Staff = 'staff';
    case Mcp = 'mcp';
}
