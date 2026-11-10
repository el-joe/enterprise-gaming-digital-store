<?php

namespace App\Http\Resources\Admin\Suppliers;

use App\Domains\Suppliers\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Credentials are never part of this resource; only whether each mode is configured.
 *
 * @mixin Supplier
 */
class SupplierResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'adapter_key' => $this->adapter_key,
            'status' => $this->status?->value,
            'mode' => $this->mode?->value,
            'base_url_override' => $this->base_url_override,
            'priority' => $this->priority,
            'fallback_enabled' => $this->fallback_enabled,
            'fallback_supplier_id' => $this->fallback_supplier_id,
            'max_fallback_attempts' => $this->max_fallback_attempts,
            'connect_timeout_ms' => $this->connect_timeout_ms,
            'request_timeout_ms' => $this->request_timeout_ms,
            'retry_policy' => $this->retry_policy,
            'verification_deadline_minutes' => $this->verification_deadline_minutes,
            'per_request_cap_base' => $this->per_request_cap_base,
            'low_balance_threshold' => $this->low_balance_threshold,
            'balance' => $this->last_balance === null ? null : ['amount' => $this->last_balance, 'currency' => $this->balance_currency, 'at' => $this->last_balance_at?->toIso8601String()],
            'health' => $this->health?->value,
            'circuit_open_until' => $this->circuit_open_until?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
