<?php

namespace App\Domains\Automation\Enums;

enum AutomationOutboxStatus: string
{
    case Pending = 'pending';
    case Delivered = 'delivered';
    case Failed = 'failed';
}
