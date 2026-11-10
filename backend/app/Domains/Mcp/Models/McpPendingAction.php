<?php

namespace App\Domains\Mcp\Models;

use App\Domains\Identity\Models\ServiceAccount;
use App\Domains\Identity\Models\User;
use App\Domains\Mcp\Enums\McpPendingActionApprovedVia;
use App\Domains\Mcp\Enums\McpPendingActionStatus;
use Database\Factories\Mcp\McpPendingActionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('mcp_pending_actions')]
#[Fillable(['service_account_id', 'tool', 'arguments', 'arguments_hash', 'risk_tier', 'summary', 'approved_via', 'expires_at', 'executed_at', 'result'])]
#[UseFactory(McpPendingActionFactory::class)]
#[Hidden(['arguments_hash'])]
class McpPendingAction extends Model
{
    /** @use HasFactory<McpPendingActionFactory> */
    use HasFactory, HasUlids;

    /**
     * Get the columns that receive a ULID on creation.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['public_id'];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'arguments' => 'array',
            'risk_tier' => 'integer',
            'status' => McpPendingActionStatus::class,
            'approved_via' => McpPendingActionApprovedVia::class,
            'expires_at' => 'datetime',
            'executed_at' => 'datetime',
            'result' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ServiceAccount, $this>
     */
    public function serviceAccount(): BelongsTo
    {
        return $this->belongsTo(ServiceAccount::class, 'service_account_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
