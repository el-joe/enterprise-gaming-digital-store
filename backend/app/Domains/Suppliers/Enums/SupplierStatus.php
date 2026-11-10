<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
