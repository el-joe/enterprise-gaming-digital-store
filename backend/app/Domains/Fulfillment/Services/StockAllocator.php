<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Enums\DigitalCodeStockStatus;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Internal stock allocation (idx_dc_stock). A code is reserved with SELECT ... FOR UPDATE SKIP
 * LOCKED, so concurrent buyers never wait on, or receive, the same row.
 *
 * The index is forced: with some statistics MySQL 8.0 picks an index-merge intersection of
 * idx_dc_stock and uq_dc_unit, and that plan waits on a row locked by another buyer instead of
 * skipping it (seen in the test suite as a lock wait timeout).
 */
class StockAllocator
{
    public function availableCount(int $productVariantId): int
    {
        return $this->available($productVariantId)->count();
    }

    public function reserve(int $productVariantId, int $supplierTransactionId): ?DigitalCode
    {
        return DB::transaction(function () use ($productVariantId, $supplierTransactionId): ?DigitalCode {
            $code = $this->available($productVariantId)->forceIndex('idx_dc_stock')->orderBy('id')->limit(1)->lock('for update skip locked')->first();

            if ($code === null) {
                return null;
            }

            $code->forceFill(['stock_status' => DigitalCodeStockStatus::Reserved, 'supplier_transaction_id' => $supplierTransactionId])->save();

            return $code;
        });
    }

    public function findByPublicId(string $publicId): ?DigitalCode
    {
        return DigitalCode::query()->where('public_id', $publicId)->where('source', DigitalCodeSource::Stock)->whereNotNull('supplier_transaction_id')->first();
    }

    public function findReservedForReference(int $supplierId, string $clientReference): ?DigitalCode
    {
        return DigitalCode::query()
            ->where('source', DigitalCodeSource::Stock)
            ->whereIn('supplier_transaction_id', SupplierTransaction::query()->select('id')->where('supplier_id', $supplierId)->where('client_reference', $clientReference))
            ->latest('id')
            ->first();
    }

    /**
     * @return Builder<DigitalCode>
     */
    private function available(int $productVariantId): Builder
    {
        return DigitalCode::query()
            ->where('product_variant_id', $productVariantId)
            ->where('source', DigitalCodeSource::Stock)
            ->where('stock_status', DigitalCodeStockStatus::Available)
            ->whereNull('order_item_unit_id')
            ->where(static fn (Builder $query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }
}
