<?php

namespace App\Domains\Notifications\Models;

use App\Domains\Identity\Models\User;
use App\Domains\Notifications\Enums\PushPlatform;
use Database\Factories\Notifications\MobilePushTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The FCM registration token of one signed-in app device (M-A18, ADR-042), tied to its sign-in
 * (refresh-token family). The token is encrypted at rest and never serialized, logged or audited.
 */
#[Table('mobile_push_tokens')]
#[Fillable(['user_id', 'family_id', 'platform', 'token', 'token_hash'])]
#[Hidden(['token', 'token_hash'])]
#[UseFactory(MobilePushTokenFactory::class)]
class MobilePushToken extends Model
{
    /** @use HasFactory<MobilePushTokenFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => PushPlatform::class,
            'token' => 'encrypted',
            'last_sent_at' => 'datetime',
        ];
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
