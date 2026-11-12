<?php

namespace App\Domains\Suppliers\Enums;

/**
 * What a supplier image candidate is for: a store product's card art, a platform's brand logo, or
 * a category banner (no current supplier provides one; Reloadly categories are only id and name).
 */
enum SupplierImageKind: string
{
    case ProductCard = 'product_card';
    case BrandLogo = 'brand_logo';
    case CategoryBanner = 'category_banner';
}
