<?php

namespace App\Domains\Suppliers\Enums;

/**
 * Classification of a supplier order call (architecture 7.2). Only `Rejected` permits a fallback,
 * and only an adapter's explicit allow-list produces it; anything else unclear is `Ambiguous`.
 */
enum SupplierOutcomeKind: string
{
    case Fulfilled = 'fulfilled';
    case InProgress = 'in_progress';
    case Rejected = 'rejected';
    case Ambiguous = 'ambiguous';
    case NotFound = 'not_found';
}
