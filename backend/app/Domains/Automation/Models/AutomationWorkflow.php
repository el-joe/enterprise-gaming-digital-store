<?php

namespace App\Domains\Automation\Models;

use Database\Factories\Automation\AutomationWorkflowFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('automation_workflows')]
#[Fillable(['workflow_id', 'name', 'category', 'is_enabled', 'schedule', 'last_success_at', 'last_failure_at'])]
#[UseFactory(AutomationWorkflowFactory::class)]
class AutomationWorkflow extends Model
{
    /** @use HasFactory<AutomationWorkflowFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_success_at' => 'datetime',
            'last_failure_at' => 'datetime',
        ];
    }
}
