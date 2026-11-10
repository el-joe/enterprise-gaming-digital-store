<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\AutomationLogStatus;
use Database\Factories\Automation\AutomationLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('automation_logs')]
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
            'status' => AutomationLogStatus::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'summary' => 'array',
        ];
    }
}
