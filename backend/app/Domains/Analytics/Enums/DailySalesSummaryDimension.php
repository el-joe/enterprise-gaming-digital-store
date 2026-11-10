<?php

namespace App\Domains\Analytics\Enums;

enum DailySalesSummaryDimension: string
{
    case Total = 'total';
    case Product = 'product';
    case Platform = 'platform';
    case Gateway = 'gateway';
    case Supplier = 'supplier';
    case Country = 'country';
    case Currency = 'currency';
}
