<?php

namespace App\Domains\Mcp\Services;

use App\Domains\Mcp\DTOs\McpCaller;
use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Shared\Services\SensitiveDataRedactor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Context;

/**
 * Writes one mcp_audit_logs row per MCP API call, including denials, validation failures and
 * rate-limited calls. Arguments are redacted (secrets, codes, tokens) and customer identifiers
 * masked before storage.
 */
class McpAuditRecorder
{
    private const array NEVER_STORED = ['confirmation_token'];

    public function __construct(private SensitiveDataRedactor $redactor, private PiiMasker $masker) {}

    /**
     * @param  array<string, mixed>  $arguments
     */
    public function record(?McpCaller $caller, string $tool, array $arguments, int $status, ?string $errorCode, int $durationMs, ?string $ip): McpAuditLog
    {
        $arguments = Arr::except($arguments, self::NEVER_STORED);

        return McpAuditLog::query()->create([
            'service_account_id' => $caller?->account->id,
            'token_id' => $caller?->tokenId(),
            'tool' => mb_substr($tool, 0, 64),
            'arguments_redacted' => $arguments === [] ? null : $this->masker->arguments($this->redactor->redact($arguments)),
            'outcome' => $this->outcome($status),
            'error_code' => $errorCode === null ? null : mb_substr($errorCode, 0, 64),
            'http_status' => $status,
            'duration_ms' => max(0, $durationMs),
            'ip' => $ip,
            'request_id' => Context::get('request_id'),
        ]);
    }

    private function outcome(int $status): McpAuditLogOutcome
    {
        return match (true) {
            $status === 202 => McpAuditLogOutcome::ConfirmationRequired,
            $status < 300 => McpAuditLogOutcome::Ok,
            $status === 401, $status === 403 => McpAuditLogOutcome::Denied,
            $status === 429 => McpAuditLogOutcome::RateLimited,
            $status < 500 => McpAuditLogOutcome::Invalid,
            default => McpAuditLogOutcome::Error,
        };
    }
}
