<?php

namespace App\Domains\Payments\Enums;

enum RefundStatus: string
{
    case Requested = 'requested';
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Unknown = 'unknown';
}
