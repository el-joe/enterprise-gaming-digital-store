<?php

namespace App\Domains\Settings\Enums;

enum ExportJobStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
