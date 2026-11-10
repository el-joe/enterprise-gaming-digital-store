<?php

namespace App\Domains\Promotions\Enums;

enum PromotionTargetTargetType: string
{
    case Product = 'product';
    case Variant = 'variant';
    case Category = 'category';
    case Platform = 'platform';
}
