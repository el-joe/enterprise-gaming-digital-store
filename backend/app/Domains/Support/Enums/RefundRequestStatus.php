<?php

namespace App\Domains\Support\Enums;

enum RefundRequestStatus: string
{
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Completed = 'completed';
}
