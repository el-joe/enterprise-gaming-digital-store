<?php

namespace App\Domains\Mcp\Enums;

enum McpPendingActionApprovedVia: string
{
    case Elicitation = 'elicitation';
    case AdminPanel = 'admin_panel';
}
