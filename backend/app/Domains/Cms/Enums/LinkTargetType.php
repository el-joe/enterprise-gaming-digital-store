<?php

namespace App\Domains\Cms\Enums;

/**
 * Where a storefront link points, for clients that route natively (M-A4).
 */
enum LinkTargetType: string
{
    case Product = 'product';
    case Category = 'category';
    case Platform = 'platform';
    case Search = 'search';
    case Url = 'url';
}
