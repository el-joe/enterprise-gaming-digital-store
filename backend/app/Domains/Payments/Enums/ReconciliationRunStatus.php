<?php

namespace App\Domains\Payments\Enums;

enum ReconciliationRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
