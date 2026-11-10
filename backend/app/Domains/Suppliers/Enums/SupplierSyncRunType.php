<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierSyncRunType: string
{
    case Catalog = 'catalog';
    case Pricing = 'pricing';
    case Balance = 'balance';
}
