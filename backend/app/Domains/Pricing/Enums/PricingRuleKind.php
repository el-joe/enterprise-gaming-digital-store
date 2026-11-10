<?php

namespace App\Domains\Pricing\Enums;

enum PricingRuleKind: string
{
    case Markup = 'markup';
    case Dynamic = 'dynamic';
}
