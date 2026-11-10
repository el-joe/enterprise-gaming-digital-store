<?php

namespace App\Domains\Audit\Models;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Audit\SecurityEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('security_events')]
#[Fillable(['type', 'severity', 'ip', 'context'])]
#[UseFactory(SecurityEventFactory::class)]
class SecurityEvent extends Model
{
    /** @use HasFactory<SecurityEventFactory> */
    use AppendOnly, HasFactory;

    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'severity' => SecurityEventSeverity::class,
            'context' => 'array',
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
