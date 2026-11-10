<?php

namespace App\Domains\Fulfillment\Enums;

enum ManualFulfillmentTaskStatus: string
{
    case Open = 'open';
    case Claimed = 'claimed';
    case Done = 'done';
    case Rejected = 'rejected';
}
