<?php

namespace App\Domains\Support\Enums;

enum SupportTicketMessageAuthorType: string
{
    case Customer = 'customer';
    case Staff = 'staff';
    case System = 'system';
}
