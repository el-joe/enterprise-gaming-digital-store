<?php

namespace App\Domains\Analytics\Enums;

enum SearchConsoleMetricDimension: string
{
    case Query = 'query';
    case Page = 'page';
    case Total = 'total';
}
