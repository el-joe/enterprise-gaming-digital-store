<?php

namespace App\Domains\Audit\Services;

use App\Domains\Audit\Enums\SecurityEventSeverity;
use App\Domains\Audit\Models\SecurityEvent;
use App\Domains\Shared\Services\SensitiveDataRedactor;

/**
 * The only writer of security_events.
 */
class SecurityEventRecorder
{
    public function __construct(private SensitiveDataRedactor $redactor) {}

    /**
     * @param  array<string, mixed>  $context
     */
    public function record(
        string $type,
        SecurityEventSeverity $severity,
        ?string $ip = null,
        ?int $userId = null,
        array $context = [],
    ): SecurityEvent {
        return SecurityEvent::query()->forceCreate([
            'type' => $type,
            'severity' => $severity,
            'ip' => $ip,
            'user_id' => $userId,
            'context' => $this->redactor->redact($context),
        ]);
    }
}
