<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Mcp\Services\McpFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The admin approval queue view (MCP_SERVER.md 3a): tool, arguments, tier, requester, decision.
 *
 * @mixin McpPendingAction
 */
class McpAdminActionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...McpPendingActionResource::make($this->resource)->toArray($request),
            'status' => $this->status->value,
            'requested_by' => $this->serviceAccount?->name,
            'decided_by' => $this->approvedBy?->name,
            'decision_note' => $this->decision_note,
            'is_expired' => $this->expires_at !== null && $this->expires_at->isPast(),
            'decided_at' => McpFormat::iso($this->decided_at),
        ];
    }
}
