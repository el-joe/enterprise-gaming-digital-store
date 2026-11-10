<?php

namespace App\Domains\Automation\Enums;

enum ScheduledTaskRunStatus: string
{
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
}
