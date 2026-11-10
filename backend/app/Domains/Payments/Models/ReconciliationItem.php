<?php

namespace App\Domains\Payments\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Payments\Enums\ReconciliationItemStatus;
use App\Domains\Payments\Enums\ReconciliationItemType;
use Database\Factories\Payments\ReconciliationItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('reconciliation_items')]
#[Fillable(['reconciliation_run_id', 'type', 'reference_type', 'reference_id', 'provider_reference', 'details', 'resolution_note'])]
#[UseFactory(ReconciliationItemFactory::class)]
class ReconciliationItem extends Model
{
    /** @use HasFactory<ReconciliationItemFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ReconciliationItemType::class,
            'reference_id' => 'integer',
            'details' => 'array',
            'status' => ReconciliationItemStatus::class,
        ];
    }

    /**
     * @return BelongsTo<ReconciliationRun, $this>
     */
    public function reconciliationRun(): BelongsTo
    {
        return $this->belongsTo(ReconciliationRun::class, 'reconciliation_run_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }
}
