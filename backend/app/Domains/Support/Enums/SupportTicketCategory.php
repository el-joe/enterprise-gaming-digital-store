<?php

namespace App\Domains\Support\Enums;

enum SupportTicketCategory: string
{
    case Order = 'order';
    case Payment = 'payment';
    case Refund = 'refund';
    case Account = 'account';
    case Other = 'other';
}
