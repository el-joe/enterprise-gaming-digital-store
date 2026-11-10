<?php

namespace App\Domains\Support\Enums;

enum SupportTicketStatus: string
{
    case Open = 'open';
    case PendingCustomer = 'pending_customer';
    case PendingStaff = 'pending_staff';
    case Resolved = 'resolved';
    case Closed = 'closed';
}
