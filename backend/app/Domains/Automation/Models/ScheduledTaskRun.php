<?php

namespace App\Domains\Automation\Models;

use App\Domains\Automation\Enums\ScheduledTaskRunStatus;
use Database\Factories\Automation\ScheduledTaskRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('scheduled_task_runs')]
#[Fillable(['command', 'started_at', 'finished_at', 'duration_ms', 'error'])]
#[UseFactory(ScheduledTaskRunFactory::class)]
class ScheduledTaskRun extends Model
{
    /** @use HasFactory<ScheduledTaskRunFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'status' => ScheduledTaskRunStatus::class,
            'duration_ms' => 'integer',
        ];
    }
}
