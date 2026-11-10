<?php

namespace App\Domains\Fulfillment\Enums;

enum DigitalCodeSource: string
{
    case Supplier = 'supplier';
    case Stock = 'stock';
}
