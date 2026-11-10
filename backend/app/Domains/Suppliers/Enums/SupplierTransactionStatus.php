<?php

namespace App\Domains\Suppliers\Enums;

enum SupplierTransactionStatus: string
{
    case Submitting = 'submitting';
    case Accepted = 'accepted';
    case PendingVerification = 'pending_verification';
    case Succeeded = 'succeeded';
    case Rejected = 'rejected';
    case NotAcceptedVerified = 'not_accepted_verified';
}
