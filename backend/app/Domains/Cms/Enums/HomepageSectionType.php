<?php

namespace App\Domains\Cms\Enums;

use App\Domains\Cms\Models\HomepageSection;

/**
 * What a homepage section shows (Storefront v2, Figma "Admin Homepage sections"), stored in
 * `settings.type`. The first four are the types the admin drawer creates; the others are the
 * v1 slots (reviews, FAQ, trust, newsletter, promo banners, banner items) kept as they were.
 * A section without `settings.type` gets one from its key, rule or items (of()).
 */
enum HomepageSectionType: string
{
    case HeroBanners = 'hero_banners';
    case CategoryTiles = 'category_tiles';
    case BrandLogos = 'brand_logos';
    case ProductRail = 'product_rail';
    case Banners = 'banners';
    case Reviews = 'reviews';
    case Faq = 'faq';
    case Trust = 'trust';
    case Newsletter = 'newsletter';
    case PromoBanners = 'promo_banners';

    /**
     * The types the admin drawer offers.
     *
     * @return list<self>
     */
    public static function editable(): array
    {
        return [self::HeroBanners, self::CategoryTiles, self::BrandLogos, self::ProductRail];
    }

    public static function of(HomepageSection $section): self
    {
        $configured = $section->settings['type'] ?? null;

        if (is_string($configured) && ($type = self::tryFrom($configured)) !== null) {
            return $type;
        }

        $byKey = match ($section->key) {
            'hero' => self::HeroBanners,
            'category_tiles' => self::CategoryTiles,
            'featured_platforms', 'brand_logos' => self::BrandLogos,
            'trust_indicators' => self::Trust,
            'newsletter' => self::Newsletter,
            'promo_banners' => self::PromoBanners,
            default => null,
        };

        if ($byKey !== null) {
            return $byKey;
        }

        if ($section->source === HomepageSectionSource::Auto) {
            $rule = is_string($section->settings['rule'] ?? null) ? HomepageAutoRule::tryFrom($section->settings['rule']) : null;
            $rule ??= HomepageAutoRule::defaultFor($section->key);

            return match ($rule) {
                HomepageAutoRule::Reviews => self::Reviews,
                HomepageAutoRule::Faqs => self::Faq,
                default => self::ProductRail,
            };
        }

        $itemType = $section->relationLoaded('homepageSectionItems') ? $section->homepageSectionItems->first()?->item_type : null;

        return match ($itemType) {
            'gaming_platform' => self::BrandLogos,
            'category' => self::CategoryTiles,
            'banner' => self::Banners,
            'faq' => self::Faq,
            default => self::ProductRail,
        };
    }
}
