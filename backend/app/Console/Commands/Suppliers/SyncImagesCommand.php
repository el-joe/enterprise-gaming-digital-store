<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Jobs\FetchSupplierProductImage;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Services\SupplierBrandLogos;
use App\Domains\Suppliers\Services\SupplierCategoryImages;
use App\Domains\Suppliers\Services\SupplierImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:sync-images {supplier? : Supplier id or adapter key}')]
#[Description('Queue supplier image downloads: product images not stored yet, brand logos of existing platforms and category images (owner decision 12)')]
class SyncImagesCommand extends Command
{
    public function handle(SupplierImageService $images, SupplierBrandLogos $brandLogos, SupplierCategoryImages $categoryImages): int
    {
        $query = SupplierProduct::query()->whereNull('removed_at');
        $supplier = $this->argument('supplier');
        $found = null;

        if ($supplier !== null) {
            $found = Supplier::query()->where('id', is_numeric($supplier) ? (int) $supplier : 0)->orWhere('adapter_key', $supplier)->first();

            if ($found === null) {
                $this->components->error('Supplier not found.');

                return self::FAILURE;
            }

            $query->where('supplier_id', $found->id);
        }

        $queued = 0;
        $attached = 0;

        $query->clone()->whereNull('image_media_id')->select('id', 'raw')->chunkById(500, function ($rows) use (&$queued): void {
            foreach ($rows as $row) {
                if ($row->sourceImageUrl() !== null) {
                    FetchSupplierProductImage::dispatch($row->id, true);
                    $queued++;
                }
            }
        });

        // Products added to the store before their image arrived (or whose staff image was removed).
        $query->clone()->whereNotNull('image_media_id')->whereHas('supplierProductMappings')->chunkById(200, function ($rows) use ($images, &$attached): void {
            foreach ($rows as $row) {
                $images->attachToMappedProducts($row);
                $attached++;
            }
        });

        $logos = $brandLogos->backfill($found?->id);
        $categories = 0;

        foreach ($found !== null ? [$found] : Supplier::query()->where('status', SupplierStatus::Active)->get() as $each) {
            $categories += $categoryImages->queue($each);
        }

        $this->components->info("Queued {$queued} image downloads, {$logos} brand logos and {$categories} category images; checked {$attached} products with an image.");

        return self::SUCCESS;
    }
}
