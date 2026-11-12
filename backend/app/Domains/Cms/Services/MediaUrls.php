<?php

namespace App\Domains\Cms\Services;

use App\Domains\Cms\Models\Media;
use Illuminate\Support\Facades\Storage;

/**
 * Absolute public URLs of stored media (M-A4, gap G7). `catalog.media.base_url` (a CDN origin,
 * env `MEDIA_BASE_URL`) wins when set; otherwise the URL comes from the media row's own disk.
 * A disk that yields a site-relative URL is made absolute against `app.url`. Pure: no queries.
 */
class MediaUrls
{
    public function url(string $disk, string $path): string
    {
        $base = $this->configuredBase();

        if ($base !== null) {
            return $base.'/'.ltrim($path, '/');
        }

        return $this->absolute(Storage::disk($disk)->url(ltrim($path, '/')));
    }

    /**
     * The URL prefix of the default media disk, for clients that build URLs themselves.
     */
    public function baseUrl(): string
    {
        return $this->configuredBase() ?? rtrim($this->absolute(Storage::disk((string) config('catalog.media.disk'))->url('')), '/');
    }

    /**
     * A storefront image: the main file (WebP) plus the format conversions ImageOptimizer wrote.
     * `formats.avif` and `formats.webp` are the largest width (`width` and `height`); smaller widths
     * are `formats.avif_480` and so on (the storefront tile builds `srcset` from them).
     *
     * @return array{path: string, url: string, alt: string|null, width: int|null, height: int|null, formats: array<string, string>}
     */
    public function image(Media $media, ?string $alt = null): array
    {
        $formats = [];

        foreach ((array) ($media->conversions ?? []) as $format => $path) {
            if (is_string($format) && is_string($path) && $path !== '') {
                $formats[$format] = $this->url($media->disk, $path);
            }
        }

        return [
            'path' => $media->path,
            'url' => $this->url($media->disk, $media->path),
            'alt' => $alt ?? $media->alt,
            'width' => $media->width,
            'height' => $media->height,
            'formats' => $formats,
        ];
    }

    private function configuredBase(): ?string
    {
        $base = config('catalog.media.base_url');

        return is_string($base) && $base !== '' ? rtrim($base, '/') : null;
    }

    private function absolute(string $url): string
    {
        if (preg_match('~^https?://~i', $url) === 1) {
            return $url;
        }

        return rtrim((string) config('app.url'), '/').'/'.ltrim($url, '/');
    }
}
