<?php

namespace App\Domains\Payments\Enums;

enum WebhookStatus: string
{
    case Received = 'received';
    case Processed = 'processed';
    case Ignored = 'ignored';
    case Failed = 'failed';
}
