<?php

namespace App\Domains\Seo\DTOs;

/**
 * The SEO values of one page in one language (SEO_STRATEGY.md section 3): from the record's
 * `seo_metadata` row, else the page type's `seo_templates` row, else the generated default.
 * `robots` and `canonical` are null when the page keeps its own rule.
 */
final readonly class ResolvedSeo
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $robots = null,
        public ?string $canonical = null,
        public ?string $image = null,
    ) {}

    public function isNoindex(): bool
    {
        return $this->robots !== null && str_starts_with(strtolower($this->robots), 'noindex');
    }
}
