<?php

namespace App\Domains\Audit\DTOs;

use App\Domains\Audit\Enums\AuditLogActorType;
use Illuminate\Database\Eloquent\Model;

final readonly class AuditEntry
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     */
    public function __construct(
        public string $action,
        public ?Model $subject = null,
        public ?array $before = null,
        public ?array $after = null,
        public ?string $reason = null,
        public ?AuditLogActorType $actorType = null,
        public ?int $actorId = null,
    ) {}
}
