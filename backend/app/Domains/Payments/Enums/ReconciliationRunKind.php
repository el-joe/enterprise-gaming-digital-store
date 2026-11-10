<?php

namespace App\Domains\Payments\Enums;

enum ReconciliationRunKind: string
{
    case Payments = 'payments';
    case Suppliers = 'suppliers';
}
