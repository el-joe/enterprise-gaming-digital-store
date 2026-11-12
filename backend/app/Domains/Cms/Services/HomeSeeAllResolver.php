<?php

namespace App\Domains\Cms\Services;

use App\Domains\Catalog\Models\Category;
use App\Domains\Cms\DTOs\LinkTarget;
use App\Domains\Cms\Enums\HomepageAutoRule;
use App\Domains\Cms\Enums\HomepageSectionType;
use App\Domains\Cms\Enums\LinkTargetType;
use App\Domains\Cms\Models\HomepageSection;

/**
 * The "See all" target of a homepage section (Storefront v2), computed from its type and rule,
 * never typed by staff: best sellers `/products?sort=best_selling`, new arrivals
 * `/products?sort=newest`, on sale `/products?on_sale=1`, a category rule or rail `/c/{slug}`, a
 * brand rule `/b/{slug}`, tiles, logos and a manual pick `/products`. Hero, banners, reviews,
 * FAQ, trust and newsletter sections have none.
 */
class HomeSeeAllResolver
{
    public function __construct(private LinkTargetResolver $links) {}

    public function forSection(HomepageSection $section, HomepageSectionType $type, ?HomepageAutoRule $rule): ?LinkTarget
    {
        $setting = static fn (string $key): ?string => is_string($section->settings[$key] ?? null) && $section->settings[$key] !== '' ? $section->settings[$key] : null;

        return match ($type) {
            HomepageSectionType::CategoryTiles, HomepageSectionType::BrandLogos => $this->links->forListing(),
            HomepageSectionType::ProductRail => match ($rule) {
                HomepageAutoRule::Newest => $this->links->forListing('sort=newest'),
                HomepageAutoRule::OnSale => $this->links->forListing('on_sale=1'),
                HomepageAutoRule::Category => ($slug = $setting('category')) === null ? $this->links->forListing() : $this->links->forEntity(LinkTargetType::Category, $slug),
                HomepageAutoRule::Brand => ($slug = $setting('platform')) === null ? $this->links->forListing() : $this->links->forEntity(LinkTargetType::Platform, $slug),
                HomepageAutoRule::BestSellers, HomepageAutoRule::Trending, HomepageAutoRule::Featured => $this->links->forListing('sort=best_selling'),
                default => $this->links->forListing(),
            },
            default => null,
        };
    }

    public function forCategory(Category $category): LinkTarget
    {
        return $this->links->forEntity(LinkTargetType::Category, $category->slug);
    }

    /**
     * The site-relative path of a target (admin "See all" field), `/products?sort=newest`.
     */
    public static function path(?LinkTarget $target): ?string
    {
        if ($target === null) {
            return null;
        }

        $parts = parse_url($target->url);

        return ($parts['path'] ?? '/').(isset($parts['query']) ? '?'.$parts['query'] : '');
    }
}
