<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Catalog\Services\CategoryService;
use App\Domains\Catalog\Services\GamingPlatformService;
use App\Domains\Catalog\Services\ProductImageService;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Exceptions\ImageRejected;
use App\Domains\Cms\Models\Media;
use App\Domains\Cms\Services\ImageOptimizer;
use App\Domains\Cms\Services\MediaService;
use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Supplier images per subject (owner request 2026-11-12): every active supplier's image of a
 * product (card art), platform (brand logo) or category is kept as a candidate, one per subject,
 * supplier and kind. The subject's own image field is the active image: the product's primary
 * image, `gaming_platforms.logo_media_id` or `categories.banner_media_id`.
 *
 * Activation rules, tracked in the subject's `image_source` (`staff` or `supplier:{id}`):
 * - a candidate is activated automatically only while the subject has no image, or to refresh the
 *   image the same supplier set before (a changed supplier image);
 * - an image a staff member uploaded or chose ("Use this") is never replaced automatically;
 * - staff "Use this" activates any candidate and marks the source `staff` (audited).
 *
 * Downloads use SupplierImageDownloader (SSRF rules) and ImageOptimizer (AVIF and WebP), and are
 * deduplicated by source URL and by the SHA-256 of the bytes.
 */
class SupplierImageCandidates
{
    public const string STAFF = 'staff';

    public function __construct(
        private SupplierImageDownloader $downloader,
        private ImageOptimizer $optimizer,
        private MediaService $media,
        private ProductImageService $productImages,
        private GamingPlatformService $platforms,
        private CategoryService $categories,
        private AuditLogger $audit,
    ) {}

    public static function supplierSource(int $supplierId): string
    {
        return 'supplier:'.$supplierId;
    }

    /**
     * Download `$url` as the supplier's candidate for the subject and offer it for activation.
     *
     * @return string `stored`, `unchanged` or the rejection reason
     *
     * @throws ConnectionException for a network failure worth retrying
     */
    public function fetch(Model $subject, int $supplierId, SupplierImageKind $kind, string $url, bool $force = false): string
    {
        $this->assertSubject($subject);
        $candidate = $this->candidateFor($subject, $supplierId, $kind);

        if (! $force && $candidate->exists && $candidate->source_url === $url && $candidate->media_id !== null) {
            $this->offer($candidate);

            return 'unchanged';
        }

        $disk = (string) config('catalog.media.disk');

        try {
            $bytes = $this->downloader->download($url);
            $hash = hash('sha256', $bytes);

            if ($hash === $candidate->image_hash && $candidate->media()->exists()) {
                $candidate->fill(['source_url' => $url, 'fetched_at' => now(), 'error' => null])->save();
                $this->offer($candidate);

                return 'unchanged';
            }

            $base = 'media/supplier-'.str_replace('_', '-', $kind->value).'s/'.$subject->getMorphClass().'-'.$subject->getKey().'/'.$supplierId.'-'.substr($hash, 0, 16);
            $stored = $this->optimizer->store($bytes, $disk, $base);
        } catch (ImageRejected $exception) {
            $candidate->fill(['source_url' => $url, 'error' => Str::limit($exception->reason, 190, ''), 'fetched_at' => now()])->save();

            return $exception->reason;
        }

        $previous = $candidate->media;

        DB::transaction(function () use ($candidate, $subject, $url, $hash, $stored, $disk): void {
            $media = Media::query()->create([
                ...$stored,
                'disk' => $disk,
                'original_filename' => Str::limit(basename((string) parse_url($url, PHP_URL_PATH)) ?: 'supplier-image', 250, ''),
                'alt' => Str::limit((string) $subject->getAttribute('name'), 190, ''),
                'folder' => 'supplier-images',
                'source' => MediaSource::Supplier,
                'license_note' => 'Supplier image, owner decision 12 (reseller agreement).',
            ]);

            $candidate->fill(['source_url' => $url, 'image_hash' => $hash, 'media_id' => $media->id, 'fetched_at' => now(), 'error' => null])->save();
        });

        $this->offer($candidate->refresh());
        $this->discard($previous);

        return 'stored';
    }

    /**
     * Record an image the supplier sync already stored (product card art) as the supplier's
     * candidate for the subject, and offer it for activation.
     */
    public function record(Model $subject, int $supplierId, SupplierImageKind $kind, int $mediaId, ?string $url = null, ?string $hash = null): SupplierImageCandidate
    {
        $this->assertSubject($subject);
        $candidate = $this->candidateFor($subject, $supplierId, $kind);

        if (! $candidate->exists || $candidate->media_id !== $mediaId || $candidate->source_url !== $url) {
            $candidate->fill(['media_id' => $mediaId, 'source_url' => $url, 'image_hash' => $hash, 'fetched_at' => now(), 'error' => null])->save();
        }

        $this->offer($candidate);

        return $candidate;
    }

    /**
     * Activate the candidate automatically when the rules allow it.
     *
     * @return bool true when the candidate is now the subject's active image
     */
    public function offer(SupplierImageCandidate $candidate): bool
    {
        $subject = $candidate->subject;

        if ($candidate->media_id === null || $subject === null) {
            return false;
        }

        $mine = self::supplierSource($candidate->supplier_id);
        $source = $subject->getAttribute('image_source');
        $active = $this->activeMediaId($subject);

        $allowed = match (true) {
            $active === null => true,
            $source === $mine => true,
            $source === null && $subject instanceof Product => $this->hasOnlySupplierImages($subject),
            default => false,
        };

        if (! $allowed) {
            return false;
        }

        if ($active !== $candidate->media_id || $source !== $mine) {
            $this->apply($subject, $candidate, $mine);
        }

        return true;
    }

    /**
     * Staff "Use this": make the candidate the active image and mark the choice as staff's, so no
     * supplier sync replaces it. Audited `catalog.image.activated_from_supplier`.
     */
    public function activate(SupplierImageCandidate $candidate): void
    {
        $subject = $candidate->subject ?? throw new InvalidArgumentException('The image subject no longer exists.');

        if ($candidate->media_id === null) {
            throw new InvalidArgumentException('The supplier image has not been downloaded.');
        }

        $before = ['media_id' => $this->activeMediaId($subject), 'image_source' => $subject->getAttribute('image_source')];

        DB::transaction(function () use ($subject, $candidate, $before): void {
            $this->apply($subject, $candidate, self::STAFF);

            $this->audit->record(new AuditEntry(
                action: 'catalog.image.activated_from_supplier',
                subject: $subject,
                before: $before,
                after: [
                    'media_id' => $candidate->media_id,
                    'image_source' => self::STAFF,
                    'candidate_id' => $candidate->id,
                    'supplier_id' => $candidate->supplier_id,
                    'supplier' => $candidate->supplier?->name,
                    'kind' => $candidate->kind->value,
                ],
            ));
        });
    }

    /**
     * The subject's candidates with their media and supplier, newest first, for the admin gallery.
     *
     * @return Collection<int, SupplierImageCandidate>
     */
    public function forSubject(Model $subject): Collection
    {
        return SupplierImageCandidate::query()
            ->where('subject_type', $subject->getMorphClass())->where('subject_id', $subject->getKey())
            ->whereNotNull('media_id')
            ->with(['media', 'supplier:id,name'])
            ->orderByDesc('fetched_at')->orderByDesc('id')
            ->get();
    }

    /**
     * The media id of the subject's active image, or null.
     */
    public function activeMediaId(Model $subject): ?int
    {
        $id = match (true) {
            $subject instanceof Product => ProductImage::query()->where('product_id', $subject->id)->orderBy('sort_order')->orderBy('id')->value('media_id'),
            $subject instanceof GamingPlatform => $subject->logo_media_id,
            $subject instanceof Category => $subject->banner_media_id,
            default => null,
        };

        return $id === null ? null : (int) $id;
    }

    /**
     * Delete a replaced supplier image once nothing uses it.
     */
    public function discard(?Media $media): void
    {
        if ($media === null || $media->source !== MediaSource::Supplier || $this->media->isInUse($media->id)) {
            return;
        }

        $this->optimizer->forget($media->disk, $media->conversions);
        $media->delete();
    }

    private function apply(Model $subject, SupplierImageCandidate $candidate, string $source): void
    {
        $mediaId = (int) $candidate->media_id;

        match (true) {
            $subject instanceof Product => $this->applyToProduct($subject, $mediaId, $source),
            $subject instanceof GamingPlatform => $this->platforms->update($subject, ['logo_media_id' => $mediaId, 'image_source' => $source]),
            $subject instanceof Category => $this->categories->update($subject, ['banner_media_id' => $mediaId, 'image_source' => $source]),
            default => null,
        };
    }

    /**
     * The candidate becomes the primary image: an existing image with that media moves first, a
     * supplier image in first place is swapped, otherwise the image is added in first place.
     * Staff-uploaded images stay attached.
     */
    private function applyToProduct(Product $product, int $mediaId, string $source): void
    {
        $images = ProductImage::query()->with('media')->where('product_id', $product->id)->orderBy('sort_order')->orderBy('id')->get();
        $primary = $images->first();
        $existing = $images->firstWhere('media_id', $mediaId);

        if ($existing !== null) {
            if ($existing->isNot($primary)) {
                $this->productImages->makePrimary($product, $existing);
            }
        } elseif ($primary !== null && $primary->media?->source === MediaSource::Supplier) {
            $this->productImages->update($primary, ['media_id' => $mediaId]);
        } else {
            $image = $this->productImages->createFor($product, ['media_id' => $mediaId, 'alt' => Str::limit($product->name, 190, ''), 'sort_order' => 0]);
            $this->productImages->makePrimary($product, $image);
        }

        $this->productImages->markSource($product, $source);
    }

    private function hasOnlySupplierImages(Product $product): bool
    {
        return ! ProductImage::query()->where('product_id', $product->id)
            ->whereNotIn('media_id', Media::query()->where('source', MediaSource::Supplier)->select('id'))
            ->exists();
    }

    private function candidateFor(Model $subject, int $supplierId, SupplierImageKind $kind): SupplierImageCandidate
    {
        return SupplierImageCandidate::query()->firstOrNew([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'supplier_id' => $supplierId,
            'kind' => $kind->value,
        ]);
    }

    private function assertSubject(Model $subject): void
    {
        if (! $subject instanceof Product && ! $subject instanceof GamingPlatform && ! $subject instanceof Category) {
            throw new InvalidArgumentException('Supplier images are kept for products, platforms and categories only.');
        }
    }
}
