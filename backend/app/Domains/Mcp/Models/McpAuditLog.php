<?php

namespace App\Domains\Mcp\Models;

use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Mcp\Enums\McpAuditLogOutcome;
use App\Domains\Shared\Models\Concerns\AppendOnly;
use Database\Factories\Mcp\McpAuditLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('mcp_audit_logs')]
#[Fillable(['service_account_id', 'token_id', 'tool', 'arguments_redacted', 'outcome', 'http_status', 'duration_ms', 'ip'])]
#[UseFactory(McpAuditLogFactory::class)]
class McpAuditLog extends Model
{
    /** @use HasFactory<McpAuditLogFactory> */
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
            'token_id' => 'integer',
            'arguments_redacted' => 'array',
            'outcome' => McpAuditLogOutcome::class,
            'http_status' => 'integer',
            'duration_ms' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ServiceAccount, $this>
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }
}
