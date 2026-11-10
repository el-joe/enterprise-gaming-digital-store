<?php

namespace App\Domains\Pricing\Enums;

enum PricingRuleBelowMarginPolicy: string
{
    case Floor = 'floor';
    case Block = 'block';
    case Allow = 'allow';
}
