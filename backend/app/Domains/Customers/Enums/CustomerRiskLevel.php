<?php

namespace App\Domains\Customers\Enums;

enum CustomerRiskLevel: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Blocked = 'blocked';
}
