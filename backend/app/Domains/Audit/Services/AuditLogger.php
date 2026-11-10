<?php

namespace App\Domains\Audit\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Enums\AuditLogActorType;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Shared\Services\SensitiveDataRedactor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;

/**
 * The only writer of audit_logs. Before and after snapshots are redacted before storage.
 */
class AuditLogger
{
    public function __construct(private SensitiveDataRedactor $redactor) {}

    public function record(AuditEntry $entry): AuditLog
    {
        [$actorType, $actorId] = $this->resolveActor($entry);

        return AuditLog::query()->create([
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'action' => $entry->action,
            'subject_type' => $entry->subject?->getMorphClass(),
            'subject_id' => $entry->subject?->getKey(),
            'before' => $entry->before === null ? null : $this->redactor->redact($entry->before),
            'after' => $entry->after === null ? null : $this->redactor->redact($entry->after),
            'reason' => $entry->reason,
            'ip' => request()->ip(),
            'user_agent' => Str::limit((string) request()->userAgent(), 252),
            'request_id' => Context::get('request_id'),
        ]);
    }

    /**
     * @return array{0: AuditLogActorType, 1: int|null}
     */
    private function resolveActor(AuditEntry $entry): array
    {
        if ($entry->actorType !== null) {
            return [$entry->actorType, $entry->actorId];
        }

        $user = Auth::user();

        if ($user instanceof User) {
            return [$user->type === UserType::Staff ? AuditLogActorType::Staff : AuditLogActorType::Customer, $user->id];
        }

        return [AuditLogActorType::System, null];
    }
}
