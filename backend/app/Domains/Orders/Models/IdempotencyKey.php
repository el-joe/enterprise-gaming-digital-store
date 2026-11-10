<?php

namespace App\Domains\Orders\Models;

use Database\Factories\Orders\IdempotencyKeyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('idempotency_keys')]
#[Fillable(['scope', 'key', 'owner_hash', 'request_hash', 'response_status', 'response_body', 'locked_at', 'expires_at'])]
#[UseFactory(IdempotencyKeyFactory::class)]
#[Hidden(['owner_hash', 'request_hash'])]
class IdempotencyKey extends Model
{
    /** @use HasFactory<IdempotencyKeyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'response_status' => 'integer',
            'response_body' => 'array',
            'locked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }
}
