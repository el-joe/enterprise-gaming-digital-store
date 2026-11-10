<?php

namespace App\Domains\Automation\Enums;

/**
 * `execution`: one n8n execution (start and finish). `call`: one request to the automation API.
 */
enum AutomationLogKind: string
{
    case Execution = 'execution';
    case Call = 'call';
}
