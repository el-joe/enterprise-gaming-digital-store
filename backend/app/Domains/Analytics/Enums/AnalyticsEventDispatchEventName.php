<?php

namespace App\Domains\Analytics\Enums;

enum AnalyticsEventDispatchEventName: string
{
    case Purchase = 'purchase';
    case Refund = 'refund';
}
