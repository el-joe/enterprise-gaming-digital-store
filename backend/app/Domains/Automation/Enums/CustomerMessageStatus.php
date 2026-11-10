<?php

namespace App\Domains\Automation\Enums;

enum CustomerMessageStatus: string
{
    case Sent = 'sent';
    case Failed = 'failed';
}
