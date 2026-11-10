<?php

namespace App\Domains\Affiliates\Enums;

enum AffiliateReferralStatus: string
{
    case Clicked = 'clicked';
    case Converted = 'converted';
    case Rejected = 'rejected';
}
