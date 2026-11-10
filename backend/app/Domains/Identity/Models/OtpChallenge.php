<?php

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\OtpChallengePurpose;
use Database\Factories\Identity\OtpChallengeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('otp_challenges')]
#[Fillable(['purpose', 'identifier_hash', 'expires_at', 'consumed_at'])]
#[UseFactory(OtpChallengeFactory::class)]
#[Hidden(['identifier_hash', 'code_hash'])]
class OtpChallenge extends Model
{
    /** @use HasFactory<OtpChallengeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purpose' => OtpChallengePurpose::class,
            'attempts' => 'integer',
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}
