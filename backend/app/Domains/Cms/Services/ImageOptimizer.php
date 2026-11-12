<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Exceptions\ImageRejected;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Throwable;

/**
 * The one image pipeline for admin uploads and synced supplier images (owner decision 12). The
 * bytes must be a JPEG, PNG, GIF, WebP or AVIF raster (never SVG), within the size and pixel
 * limits; they are decoded with GD and re-encoded, so metadata and anything embedded is dropped.
 * Each width that is not larger than the original (no upscaling, aspect ratio kept) is written as
 * AVIF and WebP. The largest width is the main file (`{base}.webp`, `{base}.avif`); smaller widths
 * are `{base}-{width}.webp|avif`, listed as `webp_{width}` and `avif_{width}` conversions.
 */
class ImageOptimizer
{
    public const int AVIF_QUALITY = 50;

    public const int WEBP_QUALITY = 80;

    /** Card art widths (storefront product tiles, 1x and 2x). */
    public const array CARD_WIDTHS = [480, 960];

    /** Admin uploads may also be full-width banners. */
    public const array UPLOAD_WIDTHS = [480, 960, 1920];

    private const array RASTER_TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP, IMAGETYPE_AVIF];

    public static function maxBytes(): int
    {
        return (int) config('catalog.media.max_upload_kilobytes') * 1024;
    }

    /**
     * @param  list<int>  $widths
     * @return array{path: string, mime: string, size_bytes: int, width: int, height: int, conversions: array<string, string>}
     *
     * @throws ImageRejected
     */
    public function store(string $bytes, string $disk, string $base, array $widths = self::CARD_WIDTHS): array
    {
        $this->assertRaster($bytes);
        $original = $this->decode($bytes);
        $sourceWidth = $original->width();

        $targets = array_values(array_unique(array_map(static fn (int $width): int => min($width, $sourceWidth), $widths)));
        rsort($targets);
        $largest = $targets[0];
        $avif = (bool) (imagetypes() & IMG_AVIF);
        $conversions = [];
        $main = null;

        foreach ($targets as $width) {
            $image = $this->decode($bytes)->scaleDown(width: $width);
            $suffix = $width === $largest ? '' : '-'.$width;
            $key = $width === $largest ? '' : '_'.$width;

            $webp = (string) $image->encodeUsingFormat(Format::WEBP, quality: self::WEBP_QUALITY);
            Storage::disk($disk)->put("{$base}{$suffix}.webp", $webp);
            $conversions['webp'.$key] = "{$base}{$suffix}.webp";

            if ($avif) {
                Storage::disk($disk)->put("{$base}{$suffix}.avif", (string) $image->encodeUsingFormat(Format::AVIF, quality: self::AVIF_QUALITY));
                $conversions['avif'.$key] = "{$base}{$suffix}.avif";
            }

            $main ??= ['path' => "{$base}.webp", 'mime' => 'image/webp', 'size_bytes' => strlen($webp), 'width' => $image->width(), 'height' => $image->height()];
        }

        return [...$main, 'conversions' => $conversions];
    }

    /**
     * Remove every file an earlier `store()` wrote.
     *
     * @param  array<string, string>|null  $conversions
     */
    public function forget(string $disk, ?array $conversions): void
    {
        $paths = array_values(array_filter((array) $conversions, 'is_string'));

        if ($paths !== []) {
            Storage::disk($disk)->delete($paths);
        }
    }

    /**
     * @throws ImageRejected
     */
    private function assertRaster(string $bytes): void
    {
        if ($bytes === '' || strlen($bytes) > self::maxBytes()) {
            throw new ImageRejected($bytes === '' ? 'empty' : 'too_large');
        }

        $head = substr($bytes, 0, 1024);

        if (stripos($head, '<svg') !== false || (str_starts_with(ltrim($head), '<?xml') && stripos($bytes, '<svg') !== false)) {
            throw new ImageRejected('svg');
        }

        $info = @getimagesizefromstring($bytes);

        if ($info === false || ! in_array($info[2], self::RASTER_TYPES, true)) {
            throw new ImageRejected('not_image');
        }

        $limit = (int) config('catalog.media.max_dimension_pixels');

        if ($info[0] < 1 || $info[1] < 1 || $info[0] > $limit || $info[1] > $limit) {
            throw new ImageRejected('dimensions');
        }
    }

    /**
     * @throws ImageRejected
     */
    private function decode(string $bytes): ImageInterface
    {
        try {
            return (new ImageManager(new Driver))->decodeBinary($bytes);
        } catch (Throwable) {
            throw new ImageRejected('not_image');
        }
    }
}
