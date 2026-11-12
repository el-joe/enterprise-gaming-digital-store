<?php

namespace Database\Seeders;

use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Seed the homepage section slots from DATABASE_DESIGN.md, in the Storefront v2 home order
     * (hero, category tiles, brand logos, best sellers, new arrivals, then the rest).
     */
    public function run(): void
    {
        $sections = [
            'hero' => [HomepageSectionSource::Manual, ['type' => 'hero_banners']],
            'category_tiles' => [HomepageSectionSource::Auto, ['type' => 'category_tiles']],
            'featured_platforms' => [HomepageSectionSource::Manual, ['type' => 'brand_logos']],
            'best_sellers' => [HomepageSectionSource::Auto, null],
            'recently_added' => [HomepageSectionSource::Auto, null],
            'trending_games' => [HomepageSectionSource::Auto, null],
            'popular_gift_cards' => [HomepageSectionSource::Auto, null],
            'special_offers' => [HomepageSectionSource::Manual, null],
            'recommended' => [HomepageSectionSource::Auto, null],
            'promo_banners' => [HomepageSectionSource::Manual, null],
            'customer_reviews' => [HomepageSectionSource::Auto, null],
            'trust_indicators' => [HomepageSectionSource::Manual, null],
            'faq' => [HomepageSectionSource::Manual, null],
            'newsletter' => [HomepageSectionSource::Manual, null],
        ];

        $sortOrder = 0;

        foreach ($sections as $key => [$source, $settings]) {
            HomepageSection::query()->firstOrCreate(['key' => $key], [
                'source' => $source,
                'settings' => $settings,
                'is_active' => true,
                'sort_order' => ++$sortOrder * 10,
            ]);
        }
    }
}
