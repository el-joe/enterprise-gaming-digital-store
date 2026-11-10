<?php

namespace App\Domains\Orders\Models;

use App\Domains\Orders\Enums\GuestOrderTokenSource;
use Database\Factories\Orders\GuestOrderTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A stateless client's proof of access to one guest order (M-A3, ADR-036). Only the SHA-256
 * hash of the token is stored; every column is written by `GuestOrderTokenService`.
 */
#[Table('guest_order_tokens')]
#[Hidden(['token_hash'])]
#[UseFactory(GuestOrderTokenFactory::class)]
class GuestOrderToken extends Model
{
    /** @use HasFactory<GuestOrderTokenFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'source' => GuestOrderTokenSource::class,
            'link_verified_at' => 'datetime',
            'otp_verified_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_used_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function opensOrder(): bool
    {
        return $this->source->opensOrder();
    }

    /**
     * Both proofs a guest reveal needs: the signed delivery link and the email one-time code.
     */
    public function provesGuestIdentity(): bool
    {
        return $this->link_verified_at !== null && $this->otp_verified_at !== null;
    }
}
