<?php

namespace App\Domains\Cms\Services;

use App\Domains\Catalog\Services\ReservedSlugs;
use App\Domains\Cms\DTOs\LinkTarget;
use App\Domains\Cms\Enums\LinkTargetType;

/**
 * Maps a stored web link (banner `cta_url`: a site-relative path or an http(s) URL, SafeUrl) to
 * a structured target (M-A4), following the web URL scheme of SEO_STRATEGY.md section 2:
 * `/products/{slug}` product, `/gaming/{slug}` platform, `/search?q=` search, `/{slug}` category
 * (when not a reserved first segment), an optional `/{locale}` prefix ignored. Anything else,
 * including any URL on another host, is `url` with the absolute URL.
 */
class LinkTargetResolver
{
    public function __construct(private ReservedSlugs $reserved) {}

    public function fromUrl(?string $link): ?LinkTarget
    {
        if ($link === null || trim($link) === '') {
            return null;
        }

        $link = trim($link);
        $parts = parse_url($link);

        if ($parts === false) {
            return null;
        }

        $isAbsolute = isset($parts['scheme']);
        $absolute = $isAbsolute ? $link : $this->webUrl($link);

        if ($isAbsolute && strtolower((string) ($parts['host'] ?? '')) !== $this->siteHost()) {
            return new LinkTarget(LinkTargetType::Url, $absolute, $absolute);
        }

        $segments = array_values(array_filter(explode('/', (string) ($parts['path'] ?? '')), static fn (string $segment): bool => $segment !== ''));

        if ($segments !== [] && in_array(strtolower($segments[0]), config('app.supported_locales'), true)) {
            array_shift($segments);
        }

        parse_str((string) ($parts['query'] ?? ''), $query);
        $slugPattern = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

        return match (true) {
            count($segments) === 2 && $segments[0] === 'products' && preg_match($slugPattern, $segments[1]) === 1 => new LinkTarget(LinkTargetType::Product, $segments[1], $absolute),
            count($segments) === 2 && $segments[0] === 'gaming' && preg_match($slugPattern, $segments[1]) === 1 => new LinkTarget(LinkTargetType::Platform, $segments[1], $absolute),
            count($segments) === 1 && $segments[0] === 'search' && is_string($query['q'] ?? null) && trim($query['q']) !== '' => new LinkTarget(LinkTargetType::Search, trim($query['q']), $absolute),
            count($segments) === 1 && preg_match($slugPattern, $segments[0]) === 1 && ! $this->reserved->isReserved($segments[0]) => new LinkTarget(LinkTargetType::Category, $segments[0], $absolute),
            default => new LinkTarget(LinkTargetType::Url, $absolute, $absolute),
        };
    }

    /**
     * The target of a catalog entity shown on the home page.
     */
    public function forEntity(LinkTargetType $type, string $slug): LinkTarget
    {
        $path = match ($type) {
            LinkTargetType::Product => "/products/{$slug}",
            LinkTargetType::Platform => "/gaming/{$slug}",
            default => "/{$slug}",
        };

        return new LinkTarget($type, $slug, $this->webUrl($path));
    }

    private function webUrl(string $path): string
    {
        return rtrim((string) config('app.url'), '/').'/'.ltrim($path, '/');
    }

    private function siteHost(): string
    {
        return strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
    }
}
