<?php

namespace App\Domains\Payments\Models;

use App\Domains\Payments\Enums\ReconciliationRunKind;
use App\Domains\Payments\Enums\ReconciliationRunStatus;
use Database\Factories\Payments\ReconciliationRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('reconciliation_runs')]
#[Fillable(['kind', 'subject_key', 'window_start', 'window_end', 'checked', 'mismatches', 'started_at', 'finished_at'])]
#[UseFactory(ReconciliationRunFactory::class)]
class ReconciliationRun extends Model
{
    /** @use HasFactory<ReconciliationRunFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ReconciliationRunKind::class,
            'window_start' => 'datetime',
            'window_end' => 'datetime',
            'status' => ReconciliationRunStatus::class,
            'checked' => 'integer',
            'mismatches' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<ReconciliationItem, $this>
     */
    public function reconciliationItems(): HasMany
    {
        return $this->hasMany(ReconciliationItem::class, 'reconciliation_run_id');
    }
}
