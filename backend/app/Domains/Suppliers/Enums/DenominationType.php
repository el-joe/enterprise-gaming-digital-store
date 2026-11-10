<?php

namespace App\Domains\Suppliers\Enums;

enum DenominationType: string
{
    case Fixed = 'fixed';
    case Range = 'range';
}
