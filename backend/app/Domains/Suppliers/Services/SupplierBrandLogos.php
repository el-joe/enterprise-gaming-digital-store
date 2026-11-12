<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Jobs\FetchSupplierImageCandidate;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Support\Str;

/**
 * Brand logos from the supplier (owner request 2026-11-12): `raw.brand.logoUrl` of a supplier
 * product becomes that supplier's brand-logo candidate for the store platform resolved for the
 * brand (SupplierPlatformResolver). One download per brand and supplier: the queued job is unique
 * per platform, supplier and kind, and an unchanged URL is not downloaded again.
 */
class SupplierBrandLogos
{
    public function __construct(private SupplierPlatformResolver $platforms) {}

    public static function urlOf(SupplierProduct $product): ?string
    {
        $url = data_get(is_array($product->raw) ? $product->raw : [], 'brand.logoUrl');

        return is_string($url) && trim($url) !== '' ? trim($url) : null;
    }

    /**
     * Queue the brand logo of `$source` for `$platform`; false when the supplier has none.
     */
    public function queue(SupplierProduct $source, GamingPlatform $platform, bool $force = false): bool
    {
        $url = self::urlOf($source);

        if ($url === null) {
            return false;
        }

        FetchSupplierImageCandidate::dispatch($platform->getMorphClass(), $platform->id, $source->supplier_id, SupplierImageKind::BrandLogo->value, $url, $force)->afterCommit();

        return true;
    }

    /**
     * Queue the brand logo of every brand and supplier whose platform already exists (platforms
     * are never created here).
     *
     * @return int logos queued
     */
    public function backfill(?int $supplierId = null, bool $force = false): int
    {
        $seen = [];
        $queued = 0;

        SupplierProduct::query()->whereNull('removed_at')
            ->when($supplierId !== null, static fn ($query) => $query->where('supplier_id', $supplierId))
            ->select(['id', 'supplier_id', 'brand_name', 'raw'])
            ->chunkById(500, function ($rows) use (&$seen, &$queued, $force): void {
                foreach ($rows as $row) {
                    $brand = SupplierPlatformResolver::brandOf($row);
                    $key = $row->supplier_id.'|'.Str::lower((string) $brand);

                    if ($brand === null || isset($seen[$key]) || self::urlOf($row) === null) {
                        continue;
                    }

                    $seen[$key] = true;
                    $platform = $this->platforms->match($brand);

                    if ($platform !== null && $this->queue($row, $platform, $force)) {
                        $queued++;
                    }
                }
            });

        return $queued;
    }
}
