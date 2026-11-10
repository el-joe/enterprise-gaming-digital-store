<?php

namespace App\Domains\Payments\Enums;

/**
 * A payment's state as the provider reports it on a server-side read, normalised across
 * gateways. Only `Succeeded` can mark an order paid, and only after amount and currency match.
 */
enum ProviderPaymentStatus: string
{
    case Pending = 'pending';
    case RequiresAction = 'requires_action';
    case Approved = 'approved';
    case Processing = 'processing';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case NotFound = 'not_found';
}
