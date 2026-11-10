<?php

namespace App\Domains\Payments\Models;

use App\Domains\Payments\Enums\WebhookStatus;
use Database\Factories\Payments\PaymentWebhookFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_webhooks')]
#[Fillable(['payment_gateway_id', 'provider_event_id', 'event_type', 'signature_valid', 'headers', 'payload', 'payment_id', 'error', 'processed_at'])]
#[UseFactory(PaymentWebhookFactory::class)]
#[Hidden(['payload'])]
class PaymentWebhook extends Model
{
    /** @use HasFactory<PaymentWebhookFactory> */
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
            'headers' => 'array',
            'payload' => 'encrypted',
            'status' => WebhookStatus::class,
            'attempts' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }

    /**
     * @return BelongsTo<Payment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
