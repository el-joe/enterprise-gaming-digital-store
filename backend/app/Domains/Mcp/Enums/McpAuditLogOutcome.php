<?php

namespace App\Domains\Mcp\Enums;

enum McpAuditLogOutcome: string
{
    case Ok = 'ok';
    case Denied = 'denied';
    case Invalid = 'invalid';
    case Error = 'error';
    case ConfirmationRequired = 'confirmation_required';
    case RateLimited = 'rate_limited';
}
