<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationLogKind;
use App\Domains\Automation\Enums\AutomationLogStatus;
use Database\Factories\Automation\AutomationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('automation_logs')]
/**
 * Execution rows (`kind = execution`) are the n8n execution log, unique per workflow and execution
 * key; call rows (`kind = call`) record each request to the automation API.
 */
#[Fillable(['workflow_id', 'execution_key', 'started_at', 'finished_at', 'summary', 'error'])]
#[UseFactory(AutomationLogFactory::class)]
class AutomationLog extends Model
{
    /** @use HasFactory<AutomationLogFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => AutomationLogKind::class,
            'status' => AutomationLogStatus::class,
            'service_account_id' => 'integer',
            'http_status' => 'integer',
            'duration_ms' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'summary' => 'array',
        ];
    }
}
