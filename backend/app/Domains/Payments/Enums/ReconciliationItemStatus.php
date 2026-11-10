<?php

namespace App\Domains\Payments\Enums;

enum ReconciliationItemStatus: string
{
    case Open = 'open';
    case Resolved = 'resolved';
    case Ignored = 'ignored';
}
