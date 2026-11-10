<?php

namespace App\Domains\Mcp\Enums;

enum McpPendingActionStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Executed = 'executed';
    case Failed = 'failed';
    case Expired = 'expired';
}
