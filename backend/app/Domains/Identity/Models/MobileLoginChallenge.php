<?php

namespace App\Domains\Identity\Models;

use Database\Factories\Identity\MobileLoginChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A pending two-factor sign-in of the mobile app: short lived, single use, limited attempts.
 */
#[Table('mobile_login_challenges')]
#[Fillable(['user_id', 'token_hash', 'device_name', 'expires_at'])]
#[Hidden(['token_hash'])]
#[UseFactory(MobileLoginChallengeFactory::class)]
class MobileLoginChallenge extends Model
{
    /** @use HasFactory<MobileLoginChallengeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
