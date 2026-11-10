<?php

namespace App\Domains\Payments\Enums;

enum ReconciliationItemType: string
{
    case OrphanProvider = 'orphan_provider';
    case MissingProvider = 'missing_provider';
    case StatusMismatch = 'status_mismatch';
    case AmountMismatch = 'amount_mismatch';
}
