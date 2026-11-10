<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierHealth: string
{
    case Healthy = 'healthy';
    case Degraded = 'degraded';
    case Down = 'down';
    case Unknown = 'unknown';
}
