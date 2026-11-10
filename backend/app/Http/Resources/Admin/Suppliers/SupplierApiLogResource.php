<?php

namespace App\Http\Resources\Admin\Suppliers;

use App\Domains\Suppliers\Models\SupplierApiLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Bodies are already redacted at write time; deliverable responses were never stored.
 *
 * @mixin SupplierApiLog
 */
class SupplierApiLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'supplier_transaction_id' => $this->supplier_transaction_id,
            'operation' => $this->operation,
            'method' => $this->method,
            'url' => $this->url,
            'http_status' => $this->http_status,
            'duration_ms' => $this->duration_ms,
            'succeeded' => $this->succeeded,
            'error_class' => $this->error_class,
            'request' => $this->request_redacted,
            'response' => $this->response_redacted,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
