<?php

namespace App\Domains\Promotions\Enums;

enum CouponRedemptionStatus: string
{
    case Reserved = 'reserved';
    case Redeemed = 'redeemed';
    case Released = 'released';
}
