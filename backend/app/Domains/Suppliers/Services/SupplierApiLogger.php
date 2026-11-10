<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Shared\Services\SensitiveDataRedactor;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierApiLog;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes supplier_api_logs. Bodies are redacted by key; deliverable responses are never passed
 * in at all. A logging failure never breaks the supplier call it describes.
 */
class SupplierApiLogger
{
    public function __construct(private SensitiveDataRedactor $redactor) {}

    /**
     * @param  array<array-key, mixed>|null  $request
     * @param  array<array-key, mixed>|null  $response
     * @param  list<string>  $sensitiveKeys  extra keys this supplier marks sensitive
     */
    public function record(
        Supplier $supplier,
        string $operation,
        string $method,
        string $url,
        ?int $status,
        int $durationMs,
        bool $succeeded,
        ?string $errorClass = null,
        ?array $request = null,
        ?array $response = null,
        ?int $supplierTransactionId = null,
        array $sensitiveKeys = [],
    ): void {
        try {
            (new SupplierApiLog)->forceFill([
                'supplier_id' => $supplier->id,
                'supplier_transaction_id' => $supplierTransactionId,
                'operation' => $operation,
                'method' => $method,
                'url' => Str::limit(strtok($url, '?') ?: $url, 490),
                'http_status' => $status,
                'duration_ms' => $durationMs,
                'succeeded' => $succeeded,
                'error_class' => $errorClass === null ? null : Str::limit($errorClass, 118),
                'request_redacted' => $request === null ? null : $this->redact($request, $sensitiveKeys),
                'response_redacted' => $response === null ? null : $this->redact($response, $sensitiveKeys),
            ])->save();
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     * @param  list<string>  $sensitiveKeys
     * @return array<array-key, mixed>
     */
    private function redact(array $data, array $sensitiveKeys): array
    {
        $data = $this->redactor->redact($data);

        foreach ($data as $key => $value) {
            if (is_string($key) && in_array($key, $sensitiveKeys, true)) {
                $data[$key] = SensitiveDataRedactor::REPLACEMENT;
            } elseif (is_array($value)) {
                $data[$key] = $this->redact($value, $sensitiveKeys);
            }
        }

        return $data;
    }
}
