<?php

namespace App\Domains\Suppliers\Enums;

enum WebhookStatus: string
{
    case Received = 'received';
    case Processed = 'processed';
    case Ignored = 'ignored';
    case Failed = 'failed';
}
