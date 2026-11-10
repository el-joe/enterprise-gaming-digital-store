<?php

namespace App\Domains\Identity\Models;

use App\Domains\Identity\Enums\ServiceAccountKind;
use App\Domains\Mcp\Models\McpAuditLog;
use App\Domains\Mcp\Models\McpPendingAction;
use Database\Factories\Identity\ServiceAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Table('service_accounts')]
#[Fillable(['name', 'kind', 'is_read_only', 'allowed_ips', 'rate_limit_per_minute', 'hmac_secret', 'last_used_at', 'disabled_at'])]
#[UseFactory(ServiceAccountFactory::class)]
#[Hidden(['hmac_secret'])]
class ServiceAccount extends Model
{
    /** @use HasFactory<ServiceAccountFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => ServiceAccountKind::class,
            'is_read_only' => 'boolean',
            'allowed_ips' => 'array',
            'rate_limit_per_minute' => 'integer',
            'hmac_secret' => 'encrypted',
            'last_used_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<McpPendingAction, $this>
     */
    public function mcpPendingActions(): HasMany
    {
        return $this->hasMany(McpPendingAction::class, 'service_account_id');
    }

    /**
     * @return HasMany<McpAuditLog, $this>
     */
    public function mcpAuditLogs(): HasMany
    {
        return $this->hasMany(McpAuditLog::class, 'service_account_id');
    }
}
