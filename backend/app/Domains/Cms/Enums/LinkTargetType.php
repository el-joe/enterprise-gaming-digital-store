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
    /** Storefront v2: the product listing `/products` with `value` as its query string (filters, sort). */
    case Listing = 'listing';
    // M-A5: account targets of inbox notifications; value is the public id.
    case Order = 'order';
    case Ticket = 'ticket';
}
