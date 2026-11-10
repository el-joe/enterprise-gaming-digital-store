<?php

namespace App\Domains\Analytics\Enums;

enum AnalyticsEventDispatchStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}
