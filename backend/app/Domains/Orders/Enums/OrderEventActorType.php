<?php

namespace App\Domains\Orders\Enums;

enum OrderEventActorType: string
{
    case System = 'system';
    case Customer = 'customer';
    case Staff = 'staff';
    case Webhook = 'webhook';
    case N8n = 'n8n';
    case Mcp = 'mcp';
}
