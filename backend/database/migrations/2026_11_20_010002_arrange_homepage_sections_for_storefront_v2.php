<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Storefront v2 home order (docs/STOREFRONT_IA.md, Figma "Home v2"): hero, category tiles, brand
 * logos, best sellers, new arrivals, then every other managed section in its current order. Only
 * an installation that already has sections is arranged (a fresh database gets the same order
 * from HomepageSectionSeeder); the schema is unchanged, the section type lives in `settings.type`.
 */
return new class extends Migration
{
    private const array LEADING = ['hero', 'category_tiles', 'featured_platforms', 'best_sellers', 'recently_added'];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! DB::table('homepage_sections')->exists()) {
            return;
        }

        $now = now();

        if (! DB::table('homepage_sections')->where('key', 'category_tiles')->exists()) {
            DB::table('homepage_sections')->insert([
                'key' => 'category_tiles',
                'source' => 'auto',
                'settings' => json_encode(['type' => 'category_tiles']),
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $types = ['hero' => 'hero_banners', 'featured_platforms' => 'brand_logos'];

        foreach ($types as $key => $type) {
            $row = DB::table('homepage_sections')->where('key', $key)->first(['id', 'settings']);

            if ($row !== null) {
                $settings = json_decode((string) $row->settings, true);
                $settings = is_array($settings) ? $settings : [];

                if (! isset($settings['type'])) {
                    DB::table('homepage_sections')->where('id', $row->id)->update(['settings' => json_encode([...$settings, 'type' => $type]), 'updated_at' => $now]);
                }
            }
        }

        $rows = DB::table('homepage_sections')->orderBy('sort_order')->orderBy('id')->get(['id', 'key']);
        $leading = $rows->filter(static fn (object $row): bool => in_array($row->key, self::LEADING, true))
            ->sortBy(static fn (object $row): int => (int) array_search($row->key, self::LEADING, true));
        $ordered = $leading->concat($rows->reject(static fn (object $row): bool => in_array($row->key, self::LEADING, true)))->values();

        foreach ($ordered as $position => $row) {
            DB::table('homepage_sections')->where('id', $row->id)->update(['sort_order' => ($position + 1) * 10]);
        }
    }

    /**
     * Reverse the migrations: the added section goes; the order and types stay (harmless).
     */
    public function down(): void
    {
        DB::table('homepage_sections')->where('key', 'category_tiles')->delete();
    }
};
