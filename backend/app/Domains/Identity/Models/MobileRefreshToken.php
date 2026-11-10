<?php

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\TokenRevocationReason;
use Database\Factories\Identity\MobileRefreshTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * One single-use refresh token of the mobile app. Rotation marks it used and inserts the next
 * token of the same family; presenting a used token revokes the whole family (ADR-035).
 */
#[Table('mobile_refresh_tokens')]
#[Fillable(['user_id', 'family_id', 'token_hash', 'device_name', 'access_token_id', 'ip', 'expires_at'])]
#[Hidden(['token_hash'])]
#[UseFactory(MobileRefreshTokenFactory::class)]
class MobileRefreshToken extends Model
{
    /** @use HasFactory<MobileRefreshTokenFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'revocation_reason' => TokenRevocationReason::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<PersonalAccessToken, $this>
     */
    public function accessToken(): BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class, 'access_token_id');
    }
}
