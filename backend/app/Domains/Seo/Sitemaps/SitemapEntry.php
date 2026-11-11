<?php

namespace App\Domains\Seo\Sitemaps;

use Carbon\CarbonInterface;

/**
 * One page of the sitemap: a storefront route (base name, without the language prefix) with
 * its parameters, its last content change and the languages in which it is noindex.
 */
final readonly class SitemapEntry
{
    /**
     * @param  array<string, string>  $parameters
     * @param  list<string>  $noindexLocales
     */
    public function __construct(
        public string $route,
        public array $parameters = [],
        public ?CarbonInterface $lastModified = null,
        public array $noindexLocales = [],
    ) {}
}
