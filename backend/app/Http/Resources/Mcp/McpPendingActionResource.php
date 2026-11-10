<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Enums\McpPendingActionStatus;
use App\Domains\Mcp\Models\McpPendingAction;
use App\Domains\Mcp\Services\McpFormat;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A pending action as the MCP client sees it. The confirmation hash is never included; the
 * plain confirmation token is added once, by the controller, to the 202 response only.
 *
 * @mixin McpPendingAction
 */
class McpPendingActionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'action_id' => $this->public_id,
            'tool' => $this->tool,
            'status' => $this->status === McpPendingActionStatus::Pending ? 'pending_approval' : $this->status->value,
            'risk_tier' => $this->risk_tier,
            'summary' => $this->summary,
            'arguments' => $this->arguments,
            'approved_via' => $this->approved_via?->value,
            'expires_at' => McpFormat::iso($this->expires_at),
            'decided_at' => McpFormat::iso($this->decided_at),
            'executed_at' => McpFormat::iso($this->executed_at),
            'result' => $this->result,
            'created_at' => McpFormat::iso($this->created_at),
        ];
    }
}
