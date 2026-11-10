<?php

namespace App\Domains\Payments\Models;

use App\Domains\Payments\Enums\PaymentDisputeStatus;
use Database\Factories\Payments\PaymentDisputeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_disputes')]
#[Fillable(['payment_id', 'provider_dispute_id', 'reason', 'amount', 'currency', 'evidence_due_at', 'closed_at'])]
#[UseFactory(PaymentDisputeFactory::class)]
class PaymentDispute extends Model
{
    /** @use HasFactory<PaymentDisputeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => PaymentDisputeStatus::class,
            'amount' => 'decimal:6',
            'evidence_due_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
