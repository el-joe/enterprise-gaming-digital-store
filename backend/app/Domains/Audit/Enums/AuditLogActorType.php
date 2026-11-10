<?php

namespace App\Domains\Audit\Enums;

enum AuditLogActorType: string
{
    case Staff = 'staff';
    case Customer = 'customer';
    case System = 'system';
    case N8n = 'n8n';
    case Mcp = 'mcp';
}
