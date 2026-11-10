<?php

namespace App\Domains\Fulfillment\Enums;

enum DigitalCodeStockStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Delivered = 'delivered';
    case Void = 'void';
}
