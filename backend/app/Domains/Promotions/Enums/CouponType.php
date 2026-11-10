<?php

namespace App\Domains\Promotions\Enums;

enum CouponType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';
}
