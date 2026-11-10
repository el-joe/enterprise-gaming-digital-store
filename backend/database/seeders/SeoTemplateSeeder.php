<?php

namespace Database\Seeders;

use App\Domains\Seo\Enums\SeoTemplatePageType;
use App\Domains\Seo\Models\SeoTemplate;
use Illuminate\Database\Seeder;

class SeoTemplateSeeder extends Seeder
{
    /**
     * Seed title and description templates. Placeholders are resolved by the SEO service in M18.
     */
    public function run(): void
    {
        foreach (SeoTemplatePageType::cases() as $pageType) {
            foreach (['en', 'ar'] as $locale) {
                SeoTemplate::query()->firstOrCreate(['page_type' => $pageType, 'locale' => $locale], [
                    'title_template' => '{name} | {site_name}',
                    'description_template' => '{description}',
                ]);
            }
        }
    }
}
