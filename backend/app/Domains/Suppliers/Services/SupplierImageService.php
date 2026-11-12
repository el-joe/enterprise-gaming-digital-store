<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Exceptions\ImageRejected;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\ImageOptimizer;
use App\Domains\Cms\Services\MediaService;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Supplier product images (owner decision 12, overriding BD8 for supplier-provided images).
 *
 * `refresh()` downloads the supplier's image (SupplierImageDownloader), skips it when the source
 * URL is unchanged or the bytes hash the same, stores it with ImageOptimizer at the card widths
 * under `media/supplier-products/{id}/` as `supplier` media, and attaches it to every store product
 * mapped to the supplier product as that supplier's candidate (SupplierImageCandidates), which
 * activates it only under the candidate rules: a staff image or choice is never replaced.
 */
class SupplierImageService
{
    public function __construct(
        private SupplierImageDownloader $downloader,
        private ImageOptimizer $optimizer,
        private MediaService $media,
        private SupplierImageCandidates $candidates,
    ) {}

    /**
     * @return string `stored`, `unchanged`, `none` or the rejection reason
     */
    public function refresh(SupplierProduct $source, bool $force = false): string
    {
        $url = $source->sourceImageUrl();

        if ($url === null) {
            return 'none';
        }

        if (! $force && $url === $source->image_url && $source->image_media_id !== null) {
            $this->attachToMappedProducts($source);

            return 'unchanged';
        }

        try {
            $bytes = $this->downloader->download($url);
            $hash = hash('sha256', $bytes);

            if ($hash === $source->image_hash && $source->imageMedia()->exists()) {
                $source->forceFill(['image_url' => $url, 'image_synced_at' => now(), 'image_error' => null])->save();
                $this->attachToMappedProducts($source);

                return 'unchanged';
            }

            $disk = (string) config('catalog.media.disk');
            $stored = $this->optimizer->store($bytes, $disk, 'media/supplier-products/'.$source->id.'/'.substr($hash, 0, 16));
        } catch (ImageRejected $exception) {
            $source->forceFill(['image_url' => $url, 'image_error' => Str::limit($exception->reason, 190, ''), 'image_synced_at' => now()])->save();

            return $exception->reason;
        }

        $previous = $source->imageMedia;

        DB::transaction(function () use ($source, $url, $hash, $stored, $disk): void {
            $media = Media::query()->create([
                ...$stored,
                'disk' => $disk,
                'original_filename' => Str::limit(basename((string) parse_url($url, PHP_URL_PATH)) ?: 'supplier-image', 250, ''),
                'alt' => Str::limit($source->name, 190, ''),
                'folder' => 'supplier-products',
                'source' => MediaSource::Supplier,
                'license_note' => 'Supplier image, owner decision 12 (reseller agreement).',
            ]);

            $source->forceFill(['image_url' => $url, 'image_hash' => $hash, 'image_media_id' => $media->id, 'image_synced_at' => now(), 'image_error' => null])->save();
        });

        $this->attachToMappedProducts($source->refresh());
        $this->discard($previous);

        return 'stored';
    }

    public function attachToMappedProducts(SupplierProduct $source): void
    {
        if ($source->image_media_id === null) {
            return;
        }

        $productIds = ProductVariant::query()
            ->whereIn('id', $source->supplierProductMappings()->select('product_variant_id'))
            ->distinct()->pluck('product_id');

        Product::query()->whereKey($productIds)->get()->each(fn (Product $product) => $this->attach($source, $product));
    }

    /**
     * Record the supplier image as this supplier's candidate for the store product
     * (SupplierImageCandidates): it becomes the primary image only while the product has none, or
     * refreshes the image this supplier set; a staff image or choice is never replaced.
     *
     * @return bool true when the product now shows the supplier image
     */
    public function attach(SupplierProduct $source, Product $product): bool
    {
        if ($source->image_media_id === null) {
            return false;
        }

        $this->candidates->record($product, $source->supplier_id, SupplierImageKind::ProductCard, $source->image_media_id, $source->image_url, $source->image_hash);

        return $this->candidates->activeMediaId($product) === $source->image_media_id;
    }

    /**
     * Delete a replaced supplier image once nothing uses it.
     */
    private function discard(?Media $media): void
    {
        if ($media === null || $media->source !== MediaSource::Supplier || $this->media->isInUse($media->id)) {
            return;
        }

        $this->optimizer->forget($media->disk, $media->conversions);
        $media->delete();
    }
}
