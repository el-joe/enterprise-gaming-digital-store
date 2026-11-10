<?php

namespace App\Domains\Cms\Enums;

/**
 * What an `auto` homepage section shows (M-A4). Set by `settings.rule`; without one, the section
 * key decides (defaultFor). Product rules run through the storefront listing query, so the
 * same visibility and pricing apply.
 */
enum HomepageAutoRule: string
{
    case BestSellers = 'best_sellers';
    case Newest = 'newest';
    case Trending = 'trending';
    case Featured = 'featured';
    case Reviews = 'reviews';
    case Faqs = 'faqs';

    public static function defaultFor(string $sectionKey): ?self
    {
        return match ($sectionKey) {
            'best_sellers', 'popular_gift_cards' => self::BestSellers,
            'recently_added' => self::Newest,
            'trending_games' => self::Trending,
            'recommended' => self::Featured,
            'customer_reviews' => self::Reviews,
            'faq' => self::Faqs,
            default => null,
        };
    }

    public function listsProducts(): bool
    {
        return in_array($this, [self::BestSellers, self::Newest, self::Trending, self::Featured], true);
    }

    /**
     * The listing sort a product rule uses.
     */
    public function sort(): string
    {
        return $this === self::Newest ? 'newest' : 'popular';
    }

    /**
     * The listing flag a product rule uses.
     */
    public function flag(): ?string
    {
        return match ($this) {
            self::Trending => 'trending',
            self::Featured => 'featured',
            default => null,
        };
    }
}
