<?php

namespace App\Domains\Suppliers\Models;

use App\Domains\Suppliers\Enums\WebhookStatus;
use Database\Factories\Suppliers\SupplierWebhookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('supplier_webhooks')]
#[Fillable(['supplier_id', 'provider_event_id', 'signature_valid', 'payload', 'processed_at'])]
#[UseFactory(SupplierWebhookFactory::class)]
#[Hidden(['payload'])]
class SupplierWebhook extends Model
{
    /** @use HasFactory<SupplierWebhookFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'signature_valid' => 'boolean',
            'payload' => 'encrypted',
            'status' => WebhookStatus::class,
            'processed_at' => 'datetime',
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
