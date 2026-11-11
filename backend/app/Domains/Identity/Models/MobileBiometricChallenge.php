<?php

namespace App\Domains\Identity\Models;

use Database\Factories\Identity\MobileBiometricChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A single-use biometric challenge (OD-1, ADR-040): bound to one key and the access token that
 * asked for it; short lived; only its SHA-256 is stored.
 */
#[Table('mobile_biometric_challenges')]
#[Fillable(['mobile_biometric_key_id', 'access_token_id', 'challenge_hash', 'expires_at'])]
#[Hidden(['challenge_hash'])]
#[UseFactory(MobileBiometricChallengeFactory::class)]
class MobileBiometricChallenge extends Model
{
    /** @use HasFactory<MobileBiometricChallengeFactory> */
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
            'consumed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<MobileBiometricKey, $this>
     */
    public function biometricKey(): BelongsTo
    {
        return $this->belongsTo(MobileBiometricKey::class, 'mobile_biometric_key_id');
    }
}
