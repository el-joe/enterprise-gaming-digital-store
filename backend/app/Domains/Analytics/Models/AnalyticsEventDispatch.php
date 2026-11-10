<?php

namespace App\Domains\Analytics\Models;

use App\Domains\Analytics\Enums\AnalyticsEventDispatchEventName;
use App\Domains\Analytics\Enums\AnalyticsEventDispatchStatus;
use App\Domains\Orders\Models\Order;
use Database\Factories\Analytics\AnalyticsEventDispatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('analytics_event_dispatches')]
#[Fillable(['event_name', 'order_id', 'dedupe_key', 'payload', 'sent_at'])]
#[UseFactory(AnalyticsEventDispatchFactory::class)]
class AnalyticsEventDispatch extends Model
{
    /** @use HasFactory<AnalyticsEventDispatchFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'event_name' => AnalyticsEventDispatchEventName::class,
            'payload' => 'array',
            'status' => AnalyticsEventDispatchStatus::class,
            'attempts' => 'integer',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
