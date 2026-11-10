<?php

namespace App\Domains\Fulfillment\Models;

use App\Domains\Fulfillment\Enums\ManualFulfillmentTaskStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use Database\Factories\Fulfillment\ManualFulfillmentTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('manual_fulfillment_tasks')]
#[Fillable(['order_item_unit_id', 'assigned_to', 'claimed_at', 'completion_note'])]
#[UseFactory(ManualFulfillmentTaskFactory::class)]
class ManualFulfillmentTask extends Model
{
    /** @use HasFactory<ManualFulfillmentTaskFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ManualFulfillmentTaskStatus::class,
            'claimed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<OrderItemUnit, $this>
     */
    public function orderItemUnit(): BelongsTo
    {
        return $this->belongsTo(OrderItemUnit::class, 'order_item_unit_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
