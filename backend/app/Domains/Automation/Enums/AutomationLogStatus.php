<?php

namespace App\Domains\Automation\Enums;

enum AutomationLogStatus: string
{
    case Started = 'started';
    case Success = 'success';
    case Failed = 'failed';
    case SkippedDuplicate = 'skipped_duplicate';
}
