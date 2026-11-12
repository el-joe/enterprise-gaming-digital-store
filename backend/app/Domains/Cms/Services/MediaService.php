<?php

namespace App\Domains\Cms\Services;

use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\ProductImage;
use App\Domains\Cms\Enums\MediaSource;
use App\Domains\Cms\Exceptions\ImageRejected;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Media;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\Services\ListQueryApplier;
use App\Domains\Suppliers\Models\SupplierProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Stores uploaded images through ImageOptimizer: decoded with GD and re-encoded to AVIF and WebP
 * at the upload widths, so embedded payloads and metadata do not survive; SVG is refused; filenames
 * are random and never come from the client.
 */
class MediaService
{
    public function __construct(private ListQueryApplier $lists, private AuditLogger $audit, private ImageOptimizer $optimizer) {}

    public function list(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(Media::query(), $list, ['folder' => 'exact', 'source' => 'exact'], ['id', 'created_at', 'size_bytes'], ['original_filename', 'alt']);
    }

    /**
     * @param  array{alt?: string|null, folder?: string|null, license_note?: string|null}  $details
     */
    public function upload(UploadedFile $file, array $details): Media
    {
        $disk = (string) config('catalog.media.disk');
        $base = 'media/'.now()->format('Y/m').'/'.Str::ulid();

        try {
            $stored = $this->optimizer->store((string) file_get_contents($file->getRealPath()), $disk, $base, ImageOptimizer::UPLOAD_WIDTHS);
        } catch (ImageRejected $exception) {
            $message = __('admin.image_upload.rejected_'.$exception->reason);

            throw new ApplicationException($message, 'invalid_image', 422, ['file' => [$message]]);
        }

        return DB::transaction(function () use ($file, $details, $disk, $stored): Media {
            $media = Media::query()->create([
                ...$stored,
                'disk' => $disk,
                'original_filename' => Str::limit(basename($file->getClientOriginalName()), 250),
                'alt' => $details['alt'] ?? null,
                'folder' => $details['folder'] ?? null,
                'source' => MediaSource::Upload,
                'license_note' => $details['license_note'] ?? null,
                'uploaded_by' => Auth::id(),
            ]);

            $this->audit->record(new AuditEntry(action: 'cms.media.uploaded', subject: $media, after: ['path' => $media->path, 'mime' => $media->mime]));

            return $media;
        });
    }

    /**
     * @param  array{alt?: string|null, folder?: string|null, license_note?: string|null}  $details
     */
    public function update(Media $media, array $details): Media
    {
        DB::transaction(function () use ($media, $details): void {
            $before = $media->only(array_keys($details));
            $media->fill($details)->save();

            $this->audit->record(new AuditEntry(action: 'cms.media.updated', subject: $media, before: $before, after: $details));
        });

        return $media->refresh();
    }

    public function delete(Media $media): void
    {
        if ($this->isInUse($media->id)) {
            throw new ApplicationException('This media file is in use and cannot be deleted.', 'conflict', 409);
        }

        DB::transaction(function () use ($media): void {
            $media->delete();

            $this->audit->record(new AuditEntry(action: 'cms.media.deleted', subject: $media, before: ['path' => $media->path]));
        });
    }

    public function isInUse(int $mediaId): bool
    {
        return ProductImage::query()->where('media_id', $mediaId)->exists()
            || Banner::query()->where('media_id', $mediaId)->orWhere('mobile_media_id', $mediaId)->exists()
            || GamingPlatform::query()->where('logo_media_id', $mediaId)->orWhere('banner_media_id', $mediaId)->exists()
            || Category::query()->where('banner_media_id', $mediaId)->exists()
            || BlogPost::query()->where('cover_media_id', $mediaId)->exists()
            || SeoMetadata::query()->where('og_image_media_id', $mediaId)->exists()
            || SupplierProduct::query()->where('image_media_id', $mediaId)->exists();
    }
}
