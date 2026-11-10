<?php

namespace App\Domains\Identity\Enums;

enum ServiceAccountKind: string
{
    case Mcp = 'mcp';
    case N8n = 'n8n';
}
