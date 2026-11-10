<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierTransactionCodeRetrievalStatus: string
{
    case NotApplicable = 'not_applicable';
    case Pending = 'pending';
    case Retrieved = 'retrieved';
    case Failed = 'failed';
}
