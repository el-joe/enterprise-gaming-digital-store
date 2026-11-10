<?php

namespace App\Domains\Pricing\Enums;

enum PriceSource: string
{
    case Explicit = 'explicit';
    case Markup = 'markup';
    case CostOnly = 'cost_only';
}
