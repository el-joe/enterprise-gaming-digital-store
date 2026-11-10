<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierProductMappingRole: string
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Other = 'other';
}
