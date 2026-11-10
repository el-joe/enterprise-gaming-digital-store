<?php

namespace App\Domains\Affiliates\Enums;

enum AffiliateCommissionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Paid = 'paid';
    case Reversed = 'reversed';
}
