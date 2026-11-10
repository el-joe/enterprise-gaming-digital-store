<?php

namespace App\Domains\Catalog\Enums;

enum ProductVariantAvailability: string
{
    case InStock = 'in_stock';
    case Limited = 'limited';
    case OutOfStock = 'out_of_stock';
}
