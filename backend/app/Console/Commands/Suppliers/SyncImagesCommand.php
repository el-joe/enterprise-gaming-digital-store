<?php

namespace App\Console\Commands\Suppliers;

use App\Domains\Suppliers\Jobs\FetchSupplierProductImage;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use App\Domains\Suppliers\Services\SupplierImageService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('suppliers:sync-images {supplier? : Supplier id or adapter key}')]
#[Description('Queue image downloads for supplier products that have no image yet (owner decision 12)')]
class SyncImagesCommand extends Command
{
    public function handle(SupplierImageService $images): int
    {
        $query = SupplierProduct::query()->whereNull('removed_at');
        $supplier = $this->argument('supplier');

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

        $this->components->info("Queued {$queued} image downloads; checked {$attached} products with an image.");

        return self::SUCCESS;
    }
}
