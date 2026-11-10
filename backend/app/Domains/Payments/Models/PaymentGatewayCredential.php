<?php

namespace App\Domains\Payments\Models;

use App\Domains\Shared\Enums\EnvironmentMode;
use Database\Factories\Payments\PaymentGatewayCredentialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('payment_gateway_credentials')]
#[Fillable(['payment_gateway_id', 'mode', 'credentials', 'webhook_secret', 'rotated_at'])]
#[UseFactory(PaymentGatewayCredentialFactory::class)]
#[Hidden(['credentials', 'webhook_secret'])]
class PaymentGatewayCredential extends Model
{
    /** @use HasFactory<PaymentGatewayCredentialFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'mode' => EnvironmentMode::class,
            'credentials' => 'encrypted:array',
            'webhook_secret' => 'encrypted',
            'rotated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<PaymentGateway, $this>
     */
    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class, 'payment_gateway_id');
    }
}
