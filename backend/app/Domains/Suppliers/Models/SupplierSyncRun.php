<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Suppliers\Enums\SupplierSyncRunStatus;
use App\Domains\Suppliers\Enums\SupplierSyncRunType;
use Database\Factories\Suppliers\SupplierSyncRunFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_sync_runs')]
#[Fillable(['supplier_id', 'type', 'triggered_by', 'items_seen', 'items_created', 'items_updated', 'items_failed', 'error', 'started_at', 'finished_at', 'window_key'])]
#[UseFactory(SupplierSyncRunFactory::class)]
class SupplierSyncRun extends Model
{
    /** @use HasFactory<SupplierSyncRunFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SupplierSyncRunType::class,
            'status' => SupplierSyncRunStatus::class,
            'items_seen' => 'integer',
            'items_created' => 'integer',
            'items_updated' => 'integer',
            'items_failed' => 'integer',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}
