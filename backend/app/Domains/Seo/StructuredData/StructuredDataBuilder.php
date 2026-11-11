<?php

namespace App\Domains\Seo\StructuredData;

use App\Http\Support\LocalizedRoutes;
use Illuminate\Support\Carbon;

/**
 * JSON-LD nodes (SEO_STRATEGY.md section 4), built on the server from the values the page
 * renders, never hand-written in Blade. Every node is a plain array for `PageSchema`.
 *
 * - Organization and WebSite (with SearchAction) on every storefront page.
 * - Product with an Offer (one priced variant) or an AggregateOffer (several), prices and
 *   currency exactly as rendered (display currency), and AggregateRating only when approved
 *   reviews exist (`rating_count` counts approved reviews only, ReviewService).
 * - BreadcrumbList from the visible breadcrumb, CollectionPage for listings, BlogPosting for
 *   posts, FAQPage only while the FAQ setting is on (Google no longer shows FAQ results).
 */
class StructuredDataBuilder
{
    private const array AVAILABILITY = [
        'in_stock' => 'https://schema.org/InStock',
        'limited' => 'https://schema.org/LimitedAvailability',
        'out_of_stock' => 'https://schema.org/OutOfStock',
    ];

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        return [
            '@type' => 'Organization',
            '@id' => $this->home(LocalizedRoutes::defaultLocale()).'#organization',
            'name' => (string) config('brand.name'),
            'url' => $this->home(LocalizedRoutes::defaultLocale()),
            'logo' => asset('images/brand/icon-512.png'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function website(string $locale): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $this->home($locale).'#website',
            'name' => (string) config('brand.name'),
            'url' => $this->home($locale),
            'inLanguage' => $locale,
            'publisher' => ['@id' => $this->home(LocalizedRoutes::defaultLocale()).'#organization'],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => LocalizedRoutes::url('storefront.search', locale: $locale).'?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * @param  array<string, string|null>  $items  label => URL (null for the current page)
     * @return array<string, mixed>|null
     */
    public function breadcrumbs(array $items, string $currentUrl): ?array
    {
        if (count($items) < 2) {
            return null;
        }

        $elements = [];
        $position = 1;

        foreach ($items as $label => $url) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => (string) $label,
                'item' => $url ?? $currentUrl,
            ];
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $elements];
    }

    /**
     * @param  array<string, mixed>  $product  ProductDetailResource
     * @param  list<array<string, mixed>>  $variants  VariantOfferResource
     * @return array<string, mixed>
     */
    public function product(array $product, array $variants, string $url, string $locale): array
    {
        $node = [
            '@type' => 'Product',
            '@id' => $url.'#product',
            'name' => (string) $product['name'],
            'url' => $url,
            'sku' => (string) $product['sku'],
            'inLanguage' => $locale,
        ];

        $description = trim(strip_tags((string) ($product['short_description'] ?? '')));

        if ($description !== '') {
            $node['description'] = $description;
        }

        $images = array_values(array_filter(array_map(static fn (array $image): ?string => $image['url'] ?? null, (array) ($product['images'] ?? []))));

        if ($images === [] && isset($product['image']['url'])) {
            $images = [$product['image']['url']];
        }

        if ($images !== []) {
            $node['image'] = $images;
        }

        if (! empty($product['category']['name'])) {
            $node['category'] = (string) $product['category']['name'];
        }

        $offers = $this->offers($variants, $url);

        if ($offers !== null) {
            $node['offers'] = $offers;
        }

        $count = (int) ($product['rating']['count'] ?? 0);

        if ($count > 0) {
            $node['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $product['rating']['average'],
                'reviewCount' => $count,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        return $node;
    }

    /**
     * @param  list<array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function collectionPage(string $name, ?string $description, string $url, string $locale, array $items): array
    {
        return array_filter([
            '@type' => 'CollectionPage',
            '@id' => $url.'#collection',
            'name' => $name,
            'description' => $description === null || trim($description) === '' ? null : trim(strip_tags($description)),
            'url' => $url,
            'inLanguage' => $locale,
            'isPartOf' => ['@id' => $this->home($locale).'#website'],
            'mainEntity' => $items === [] ? null : [
                '@type' => 'ItemList',
                'numberOfItems' => count($items),
                'itemListElement' => array_map(static fn (array $item, int $index): array => [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'],
                    'url' => $item['url'],
                ], $items, array_keys($items)),
            ],
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @return array<string, mixed>
     */
    public function blogPosting(string $headline, ?string $description, string $url, string $locale, ?Carbon $published, ?Carbon $modified, ?string $image): array
    {
        return array_filter([
            '@type' => 'BlogPosting',
            '@id' => $url.'#article',
            'headline' => mb_substr($headline, 0, 110),
            'description' => $description === null || trim($description) === '' ? null : trim(strip_tags($description)),
            'url' => $url,
            'mainEntityOfPage' => $url,
            'inLanguage' => $locale,
            'datePublished' => $published?->toIso8601String(),
            'dateModified' => ($modified ?? $published)?->toIso8601String(),
            'image' => $image === null ? null : [$image],
            'author' => ['@type' => 'Organization', 'name' => (string) config('brand.name'), 'url' => $this->home(LocalizedRoutes::defaultLocale())],
            'publisher' => ['@id' => $this->home(LocalizedRoutes::defaultLocale()).'#organization'],
        ], static fn (mixed $value): bool => $value !== null);
    }

    /**
     * @param  list<array{question: string, answer: string}>  $faqs
     * @return array<string, mixed>|null
     */
    public function faqPage(array $faqs, string $url): ?array
    {
        $faqs = array_values(array_filter($faqs, static fn (array $faq): bool => trim($faq['question']) !== '' && trim($faq['answer']) !== ''));

        if ($faqs === []) {
            return null;
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $url.'#faq',
            'mainEntity' => array_map(static fn (array $faq): array => [
                '@type' => 'Question',
                'name' => trim($faq['question']),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => trim($faq['answer'])],
            ], $faqs),
        ];
    }

    /**
     * Offer for one priced variant, AggregateOffer (with each Offer) for several; null when no
     * variant has a price on the page.
     *
     * @param  list<array<string, mixed>>  $variants
     * @return array<string, mixed>|null
     */
    private function offers(array $variants, string $url): ?array
    {
        $validUntil = now()->addDays((int) config('seo.structured_data.price_valid_days', 7))->toDateString();
        $offers = [];

        foreach ($variants as $variant) {
            $price = $variant['price'] ?? null;

            if (! is_array($price) || ! isset($price['amount'], $price['currency'])) {
                continue;
            }

            $offers[] = [
                '@type' => 'Offer',
                'name' => (string) $variant['title'],
                'sku' => (string) $variant['sku'],
                'price' => (string) $price['amount'],
                'priceCurrency' => (string) $price['currency'],
                'availability' => ($variant['purchasable'] ?? false) ? (self::AVAILABILITY[$variant['availability']] ?? self::AVAILABILITY['out_of_stock']) : self::AVAILABILITY['out_of_stock'],
                'priceValidUntil' => $validUntil,
                'url' => $url,
                'itemCondition' => 'https://schema.org/NewCondition',
            ];
        }

        if ($offers === []) {
            return null;
        }

        if (count($offers) === 1) {
            return $offers[0];
        }

        $amounts = array_map(static fn (array $offer): string => $offer['price'], $offers);
        usort($amounts, static fn (string $a, string $b): int => bccomp($a, $b, 6));
        $inStock = array_filter($offers, static fn (array $offer): bool => $offer['availability'] !== self::AVAILABILITY['out_of_stock']);

        return [
            '@type' => 'AggregateOffer',
            'priceCurrency' => $offers[0]['priceCurrency'],
            'lowPrice' => $amounts[0],
            'highPrice' => $amounts[count($amounts) - 1],
            'offerCount' => count($offers),
            'availability' => $inStock === [] ? self::AVAILABILITY['out_of_stock'] : self::AVAILABILITY['in_stock'],
            'offers' => $offers,
        ];
    }

    private function home(string $locale): string
    {
        return LocalizedRoutes::url('storefront.home', locale: $locale);
    }
}
