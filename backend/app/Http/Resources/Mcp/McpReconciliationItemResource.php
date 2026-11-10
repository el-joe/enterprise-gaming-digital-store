<?php

namespace App\Http\Resources\Mcp;

use App\Domains\Mcp\Services\McpFormat;
use App\Domains\Payments\Models\ReconciliationItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An open reconciliation item without its `details` payload.
 *
 * @mixin ReconciliationItem
 */
class McpReconciliationItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type?->value,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'status' => $this->status->value,
            'run' => $this->reconciliationRun === null ? null : [
                'kind' => $this->reconciliationRun->kind?->value,
                'subject' => $this->reconciliationRun->subject_key,
                'window_start' => McpFormat::iso($this->reconciliationRun->window_start),
                'window_end' => McpFormat::iso($this->reconciliationRun->window_end),
            ],
            'created_at' => McpFormat::iso($this->created_at),
        ];
    }
}
