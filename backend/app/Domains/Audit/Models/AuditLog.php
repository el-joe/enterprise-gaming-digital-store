<?php

namespace App\Domains\Audit\Models;

use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Audit\AuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Table('audit_logs')]
#[Fillable(['actor_type', 'actor_id', 'action', 'subject_type', 'subject_id', 'before', 'after', 'reason', 'ip', 'user_agent', 'request_id'])]
#[UseFactory(AuditLogFactory::class)]
class AuditLog extends Model
{
    /** @use HasFactory<AuditLogFactory> */
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
            'actor_type' => AuditLogActorType::class,
            'actor_id' => 'integer',
            'subject_id' => 'integer',
            'before' => 'array',
            'after' => 'array',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subject(): MorphTo
    {
        return $this->morphTo('subject', 'subject_type', 'subject_id');
    }
}
