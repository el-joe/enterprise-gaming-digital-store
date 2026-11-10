<?php

namespace App\Domains\Fulfillment\Enums;

enum DigitalCodeAccessActorType: string
{
    case Customer = 'customer';
    case Guest = 'guest';
    case Staff = 'staff';
}
