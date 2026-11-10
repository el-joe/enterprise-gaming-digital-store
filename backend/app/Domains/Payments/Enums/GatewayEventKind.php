<?php

namespace App\Domains\Payments\Enums;

/**
 * What a verified webhook is about. Webhooks are triggers only (ADR-011): every kind leads to
 * a provider re-read before anything changes.
 */
enum GatewayEventKind: string
{
    case Payment = 'payment';
    case Refund = 'refund';
    case Dispute = 'dispute';
    case Ignored = 'ignored';
}
