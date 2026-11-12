<?php

/**
 * Admin → Marketing → Homepage sections, Storefront v2 (Figma 116:11200 and the drawer 116:11898):
 * the ordered list with drag handles and keyboard buttons, the read-only category rails, and the
 * drawer's writes (type, English and Arabic title, rule, period, item limit 4–24, visibility),
 * reorder and delete, each behind `content.homepage` and audited.
 */

use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Cms\Enums\HomepageSectionSource;
use App\Domains\Cms\Models\HomepageSection;
use App\Http\Middleware\Admin\SetAdminLocale;

describe('page', function (): void {
    it('lists the sections in order with type, titles, rule, See all and visibility, then the category rails', function (): void {
        adminWith(['content.homepage']);
        $best = HomepageSection::factory()->create(['key' => 'best_sellers', 'title' => 'Best sellers', 'source' => HomepageSectionSource::Auto, 'sort_order' => 2, 'settings' => ['type' => 'product_rail', 'rule' => 'best_sellers']]);
        HomepageSection::factory()->create(['key' => 'hero', 'title' => 'Main hero', 'source' => HomepageSectionSource::Manual, 'sort_order' => 1, 'settings' => ['type' => 'hero_banners']]);
        HomepageSection::factory()->create(['key' => 'deals', 'title' => 'Weekend deals', 'source' => HomepageSectionSource::Auto, 'sort_order' => 3, 'is_active' => false, 'settings' => ['type' => 'product_rail', 'rule' => 'on_sale']]);
        ContentTranslation::factory()->create(['translatable_type' => 'homepage_section', 'translatable_id' => $best->id, 'locale' => 'ar', 'field' => 'title', 'value' => 'الأكثر مبيعًا']);
        Category::factory()->create(['name' => 'Shopping', 'slug' => 'shopping', 'show_on_home' => true, 'home_position' => 1]);

        $response = $this->get('/admin/marketing/homepage-sections')->assertOk()
            ->assertSee('Homepage sections')
            ->assertSee('data-module="homepage-sections"', false)
            ->assertSee('data-drag-handle', false)
            ->assertSee('data-move="up"', false)
            ->assertSee('Hero banners')
            ->assertSee('Product rail')
            ->assertSee('الأكثر مبيعًا')
            ->assertSee('/products?sort=best_selling')
            ->assertSee('/products?on_sale=1')
            ->assertSee('Hidden')
            ->assertSee('From Categories')
            ->assertSee('/c/shopping')
            ->assertSee('Edit on category')
            ->assertSee('data-drawer', false);

        $html = $response->getContent();
        expect(strpos($html, 'Main hero'))->toBeLessThan(strpos($html, 'Best sellers'))
            ->and(strpos($html, 'Weekend deals'))->toBeLessThan(strpos($html, 'From Categories'));

        assertCspSafeHtml($response);
    });

    it('is forbidden without the permission', function (): void {
        adminWith(['catalog.view']);

        $this->get('/admin/marketing/homepage-sections')->assertForbidden();
    });

    it('renders in Arabic', function (): void {
        adminWith(['content.homepage']);
        HomepageSection::factory()->create(['key' => 'hero', 'settings' => ['type' => 'hero_banners']]);

        $this->withSession([SetAdminLocale::SESSION_KEY => 'ar'])->get('/admin/marketing/homepage-sections')->assertOk()
            ->assertHeader('Content-Language', 'ar')
            ->assertSee('أقسام الصفحة الرئيسية')
            ->assertSee('بانرات رئيسية')
            ->assertSee('من الفئات');
    });
});

describe('drawer writes', function (): void {
    it('creates a section with a generated key, a position at the end, its Arabic title and an audit entry', function (): void {
        adminWith(['content.homepage']);
        HomepageSection::factory()->create(['key' => 'hero', 'sort_order' => 40]);

        $id = $this->postJson('/admin/api/homepage-sections', [
            'title' => 'Best sellers',
            'title_ar' => 'الأكثر مبيعًا',
            'source' => 'auto',
            'is_active' => true,
            'settings' => ['type' => 'product_rail', 'rule' => 'best_sellers', 'period' => '30d', 'limit' => 10],
        ])->assertCreated()->assertJsonPath('data.settings.rule', 'best_sellers')->json('data.id');

        $section = HomepageSection::query()->findOrFail($id);

        expect($section->key)->toStartWith('best_sellers_')
            ->and($section->sort_order)->toBe(50)
            ->and($section->settings)->toEqual(['type' => 'product_rail', 'rule' => 'best_sellers', 'period' => '30d', 'limit' => 10]);

        $this->assertDatabaseHas('content_translations', ['translatable_type' => 'homepage_section', 'translatable_id' => $id, 'locale' => 'ar', 'field' => 'title', 'value' => 'الأكثر مبيعًا']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.homepage_section.created', 'subject_id' => $id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'content.translation.updated', 'subject_id' => $id]);
    });

    it('stores a manual pick as a manual section and a category rule with its category', function (): void {
        adminWith(['content.homepage']);
        Category::factory()->create(['slug' => 'gaming']);

        $manual = $this->postJson('/admin/api/homepage-sections', ['title' => 'Staff picks', 'source' => 'auto', 'is_active' => true, 'settings' => ['type' => 'product_rail', 'rule' => 'manual', 'limit' => 8]])->assertCreated();
        expect($manual->json('data.source'))->toBe('manual')->and($manual->json('data.settings'))->toEqual(['type' => 'product_rail', 'limit' => 8]);

        $this->postJson('/admin/api/homepage-sections', ['title' => 'Gaming', 'source' => 'auto', 'is_active' => true, 'settings' => ['type' => 'product_rail', 'rule' => 'category', 'category' => 'gaming']])
            ->assertCreated()->assertJsonPath('data.settings.category', 'gaming');
    });

    it('validates the type, rule, period, item limit and the category or brand of the rule', function (array $settings, string $field): void {
        adminWith(['content.homepage']);

        $this->postJson('/admin/api/homepage-sections', ['title' => 'X', 'source' => 'auto', 'is_active' => true, 'settings' => $settings])
            ->assertUnprocessable()->assertJsonValidationErrors($field);
    })->with([
        'unknown type' => [['type' => 'carousel'], 'settings.type'],
        'unknown rule' => [['type' => 'product_rail', 'rule' => 'random'], 'settings.rule'],
        'unknown period' => [['type' => 'product_rail', 'rule' => 'best_sellers', 'period' => '1y'], 'settings.period'],
        'limit under 4' => [['type' => 'product_rail', 'limit' => 3], 'settings.limit'],
        'limit over 24' => [['type' => 'product_rail', 'limit' => 25], 'settings.limit'],
        'category rule without a category' => [['type' => 'product_rail', 'rule' => 'category'], 'settings.category'],
        'brand rule with an unknown brand' => [['type' => 'product_rail', 'rule' => 'brand', 'platform' => 'nope'], 'settings.platform'],
    ]);

    it('updates the title, rule and visibility, and clears the Arabic title when it is emptied', function (): void {
        adminWith(['content.homepage']);
        $section = HomepageSection::factory()->create(['key' => 'deals', 'title' => 'Deals', 'source' => HomepageSectionSource::Auto, 'settings' => ['type' => 'product_rail', 'rule' => 'newest']]);
        ContentTranslation::factory()->create(['translatable_type' => 'homepage_section', 'translatable_id' => $section->id, 'locale' => 'ar', 'field' => 'title', 'value' => 'عروض']);

        $this->patchJson("/admin/api/homepage-sections/{$section->id}", ['title' => 'Weekend deals', 'title_ar' => '', 'is_active' => false, 'settings' => ['type' => 'product_rail', 'rule' => 'on_sale', 'limit' => 6]])
            ->assertOk()->assertJsonPath('data.title', 'Weekend deals')->assertJsonPath('data.is_active', false)->assertJsonPath('data.settings.rule', 'on_sale');

        $this->assertDatabaseMissing('content_translations', ['translatable_type' => 'homepage_section', 'translatable_id' => $section->id, 'field' => 'title']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.homepage_section.updated', 'subject_id' => $section->id]);
    });

    it('reorders and deletes with the permission, audited', function (): void {
        adminWith(['content.homepage']);
        [$a, $b, $c] = HomepageSection::factory()->count(3)->sequence(['sort_order' => 1], ['sort_order' => 2], ['sort_order' => 3])->create();

        $this->postJson('/admin/api/homepage-sections/reorder', ['ids' => [$c->id, $a->id, $b->id]])->assertOk();
        expect(HomepageSection::query()->orderBy('sort_order')->pluck('id')->all())->toBe([$c->id, $a->id, $b->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.homepage_section.reordered']);

        $this->deleteJson("/admin/api/homepage-sections/{$b->id}")->assertOk();
        $this->assertDatabaseMissing('homepage_sections', ['id' => $b->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'cms.homepage_section.deleted', 'subject_id' => $b->id]);
    });

    it('refuses every write without the permission', function (): void {
        adminWith(['catalog.manage']);
        $section = HomepageSection::factory()->create();

        $this->postJson('/admin/api/homepage-sections', ['title' => 'X', 'source' => 'auto', 'is_active' => true])->assertForbidden();
        $this->patchJson("/admin/api/homepage-sections/{$section->id}", ['title' => 'Y'])->assertForbidden();
        $this->postJson('/admin/api/homepage-sections/reorder', ['ids' => [$section->id]])->assertForbidden();
        $this->deleteJson("/admin/api/homepage-sections/{$section->id}")->assertForbidden();
    });
});
