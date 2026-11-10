<?php

namespace Database\Seeders;

use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Seed the homepage section slots from DATABASE_DESIGN.md.
     */
    public function run(): void
    {
        $sections = [
            'hero' => HomepageSectionSource::Manual,
            'featured_platforms' => HomepageSectionSource::Manual,
            'trending_games' => HomepageSectionSource::Auto,
            'best_sellers' => HomepageSectionSource::Auto,
            'popular_gift_cards' => HomepageSectionSource::Auto,
            'special_offers' => HomepageSectionSource::Manual,
            'recommended' => HomepageSectionSource::Auto,
            'recently_added' => HomepageSectionSource::Auto,
            'promo_banners' => HomepageSectionSource::Manual,
            'customer_reviews' => HomepageSectionSource::Auto,
            'trust_indicators' => HomepageSectionSource::Manual,
            'faq' => HomepageSectionSource::Manual,
            'newsletter' => HomepageSectionSource::Manual,
        ];

        $sortOrder = 0;

        foreach ($sections as $key => $source) {
            HomepageSection::query()->firstOrCreate(['key' => $key], [
                'source' => $source,
                'is_active' => true,
                'sort_order' => ++$sortOrder * 10,
            ]);
        }
    }
}
