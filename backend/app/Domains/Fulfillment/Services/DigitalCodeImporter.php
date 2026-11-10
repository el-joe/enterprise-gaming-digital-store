<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Exceptions\DuplicateDigitalCode;
use App\Domains\Identity\Models\User;
use App\Domains\Pricing\Services\PricingDirtyMarker;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Stock import (M9). Each code goes through the vault; a duplicate of an existing or earlier
 * code in the batch is rejected and reported by its position, never by its value.
 */
class DigitalCodeImporter
{
    public function __construct(private DigitalCodeVault $vault, private AuditLogger $audit, private PricingDirtyMarker $dirty) {}

    /**
     * @param  list<array{code: string, pin?: ?string, redemption_url?: ?string, expires_at?: ?string}>  $codes
     * @return array{batch: string, imported: int, rejected: list<array{index: int, reason: string}>}
     */
    public function import(int $productVariantId, array $codes, User $staff): array
    {
        $variant = ProductVariant::query()->findOrFail($productVariantId);
        $batch = (string) Str::ulid();
        $imported = 0;
        $rejected = [];

        foreach ($codes as $index => $row) {
            try {
                $this->vault->store(new DeliverableItem($row['code'], $row['pin'] ?? null, $row['redemption_url'] ?? null), DigitalCodeSource::Stock, [
                    'product_variant_id' => $variant->id,
                    'stock_status' => DigitalCodeStockStatus::Available,
                    'expires_at' => isset($row['expires_at']) ? Carbon::parse($row['expires_at']) : null,
                    'imported_by' => $staff->id,
                    'import_batch' => $batch,
                ]);
                $imported++;
            } catch (DuplicateDigitalCode) {
                $rejected[] = ['index' => $index, 'reason' => 'duplicate_code'];
            }
        }

        $this->audit->record(new AuditEntry('digital_codes.imported', $variant, after: ['batch' => $batch, 'imported' => $imported, 'rejected' => count($rejected)]));
        $this->dirty->forVariants([$variant->id]);

        return ['batch' => $batch, 'imported' => $imported, 'rejected' => $rejected];
    }
}
