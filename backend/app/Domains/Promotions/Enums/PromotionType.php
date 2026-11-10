<?php

namespace App\Domains\Promotions\Enums;

enum PromotionType: string
{
    case Percent = 'percent';
    case Fixed = 'fixed';
    case FixedPrice = 'fixed_price';
}
