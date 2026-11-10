<?php

namespace App\Domains\Pricing\Enums;

enum PricingRuleScope: string
{
    case Global = 'global';
    case Category = 'category';
    case Platform = 'platform';
    case Product = 'product';
    case Variant = 'variant';
}
