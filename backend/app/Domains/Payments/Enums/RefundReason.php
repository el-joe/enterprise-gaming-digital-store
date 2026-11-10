<?php

namespace App\Domains\Payments\Enums;

enum RefundReason: string
{
    case Undelivered = 'undelivered';
    case CustomerRequest = 'customer_request';
    case Fraud = 'fraud';
    case Duplicate = 'duplicate';
    case Admin = 'admin';
}
