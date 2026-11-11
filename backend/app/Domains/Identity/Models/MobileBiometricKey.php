<?php

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\BiometricKeyRevocationReason;
use Database\Factories\Identity\MobileBiometricKeyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * The public half of a device-bound biometric key (OD-1, ADR-040), tied to one sign-in
 * (refresh-token family). It is usable only while that family is signed in and the key is not
 * revoked.
 */
#[Table('mobile_biometric_keys')]
#[Fillable(['public_id', 'user_id', 'family_id', 'algorithm', 'public_key', 'key_fingerprint'])]
#[Hidden(['public_key'])]
#[UseFactory(MobileBiometricKeyFactory::class)]
class MobileBiometricKey extends Model
{
    /** @use HasFactory<MobileBiometricKeyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'failed_attempts' => 'integer',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
            'revocation_reason' => BiometricKeyRevocationReason::class,
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
     * @return HasMany<MobileBiometricChallenge, $this>
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(MobileBiometricChallenge::class, 'mobile_biometric_key_id');
    }
}
