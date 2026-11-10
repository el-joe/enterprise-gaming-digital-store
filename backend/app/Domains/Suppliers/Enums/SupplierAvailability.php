<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierAvailability: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Unknown = 'unknown';
}
