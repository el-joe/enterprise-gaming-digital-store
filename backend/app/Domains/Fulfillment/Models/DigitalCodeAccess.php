<?php

namespace App\Domains\Fulfillment\Models;

use App\Domains\Fulfillment\Enums\DigitalCodeAccessActorType;
use App\Domains\Fulfillment\Enums\DigitalCodeAccessPurpose;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Fulfillment\DigitalCodeAccessFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('digital_code_accesses')]
#[Fillable(['digital_code_id', 'actor_type', 'actor_id', 'purpose', 'reason', 'granted', 'denial_reason', 'ip', 'user_agent'])]
#[UseFactory(DigitalCodeAccessFactory::class)]
class DigitalCodeAccess extends Model
{
    /** @use HasFactory<DigitalCodeAccessFactory> */
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
            'actor_type' => DigitalCodeAccessActorType::class,
            'actor_id' => 'integer',
            'purpose' => DigitalCodeAccessPurpose::class,
            'granted' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<DigitalCode, $this>
     */
    public function digitalCode(): BelongsTo
    {
        return $this->belongsTo(DigitalCode::class, 'digital_code_id');
    }
}
