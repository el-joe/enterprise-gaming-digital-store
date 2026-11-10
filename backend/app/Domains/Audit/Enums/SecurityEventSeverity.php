<?php

namespace App\Domains\Audit\Enums;

enum SecurityEventSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Critical = 'critical';
}
