<?php

namespace App\Domains\Affiliates\Enums;

enum AffiliatePayoutStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
}
