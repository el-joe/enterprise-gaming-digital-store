<?php

namespace App\Domains\Seo\StructuredData;

/**
 * The JSON-LD of the page being rendered (request scoped). Controllers and the breadcrumb
 * component add nodes; the storefront layout prints one `application/ld+json` script with the
 * CSP nonce. Organization and WebSite are always first. The encoding escapes `<`, `>` and `&`,
 * so text from the catalog can never close the script element.
 */
class PageSchema
{
    /** @var list<array<string, mixed>> */
    private array $nodes = [];

    private bool $hasBreadcrumbs = false;

    public function __construct(private StructuredDataBuilder $builder) {}

    /**
     * @param  array<string, mixed>|null  $node
     */
    public function add(?array $node): void
    {
        if ($node === null) {
            return;
        }

        if (($node['@type'] ?? null) === 'BreadcrumbList') {
            if ($this->hasBreadcrumbs) {
                return;
            }

            $this->hasBreadcrumbs = true;
        }

        $this->nodes[] = $node;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function nodes(string $locale): array
    {
        return [$this->builder->organization(), $this->builder->website($locale), ...$this->nodes];
    }

    public function toJson(string $locale): string
    {
        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => $this->nodes($locale)],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
        );
    }
}
