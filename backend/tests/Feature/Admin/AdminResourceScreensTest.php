<?php

use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Models\AffiliateCommission;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Catalog\Models\AttributeDefinition;
use App\Domains\Catalog\Models\Category;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\DeliveryFieldDefinition;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\Region;
use App\Domains\Cms\Models\Banner;
use App\Domains\Cms\Models\BlogCategory;
use App\Domains\Cms\Models\BlogPost;
use App\Domains\Cms\Models\Faq;
use App\Domains\Cms\Models\FaqGroup;
use App\Domains\Cms\Models\HomepageSection;
use App\Domains\Cms\Models\Menu;
use App\Domains\Cms\Models\MenuItem;
use App\Domains\Cms\Models\Page;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Pricing\Models\VariantPrice;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\Promotion;
use App\Domains\Seo\Models\Redirect;
use App\Domains\Seo\Models\SeoMetadata;
use App\Domains\Shared\Models\Country;
use App\Http\Support\AdminScreens\ResourceScreens;
use Database\Factories\Currency\CurrencyFactory;

/*
| M15: the declarative list, create and edit screens over the admin JSON resources (catalog
| taxonomy, pricing and currency, marketing, content), their tables, translations, SEO fields
| and the country display currency.
*/

/**
 * A record for each screen's edit page.
 */
function screenRecord(string $key): string
{
    return match ($key) {
        'catalog.platforms' => (string) GamingPlatform::factory()->create()->id,
        'catalog.categories' => (string) Category::factory()->create()->id,
        'catalog.regions' => (string) Region::factory()->create()->id,
        'catalog.attributes' => (string) AttributeDefinition::factory()->create()->id,
        'catalog.delivery-fields' => (string) DeliveryFieldDefinition::factory()->create()->id,
        'pricing.pricing-rules' => (string) PricingRule::factory()->create()->id,
        'pricing.variant-prices' => (string) VariantPrice::factory()->create()->id,
        'pricing.currencies' => (static function (): string {
            CurrencyFactory::ensure('EGP');

            return 'EGP';
        })(),
        'pricing.countries' => Country::factory()->create(['code' => 'EG'])->code,
        'marketing.coupons' => (string) Coupon::factory()->create()->id,
        'marketing.promotions' => (string) Promotion::factory()->create()->id,
        'marketing.affiliates' => (string) Affiliate::factory()->create()->id,
        'marketing.banners' => (string) Banner::factory()->create()->id,
        'marketing.homepage-sections' => (string) HomepageSection::factory()->create()->id,
        'content.pages' => (string) Page::factory()->create()->id,
        'content.blog-posts' => (string) BlogPost::factory()->create()->id,
        'content.blog-categories' => (string) BlogCategory::factory()->create()->id,
        'content.faqs' => (string) Faq::factory()->create()->id,
        'content.faq-groups' => (string) FaqGroup::factory()->create()->id,
        'content.menus' => (string) Menu::factory()->create()->id,
        'content.redirects' => (string) Redirect::factory()->create()->id,
    };
}

dataset('screens', fn (): array => array_map(static fn (string $key): array => [$key], array_keys(ResourceScreens::all())));

describe('pages and permissions', function (): void {
    it('answers 403 on the list, create and edit pages without the view permission', function (string $key): void {
        $screen = ResourceScreens::all()[$key];
        $record = screenRecord($key);
        adminWith(['orders.view']);

        $this->get(route($screen::routeName('index')))->assertForbidden();
        $this->get(route($screen::routeName('edit'), ['record' => $record]))->assertForbidden();

        if ($screen::creatable()) {
            $this->get(route($screen::routeName('create')))->assertForbidden();
        }
    })->with('screens');

    it('renders the list and a read-only edit page with only the view permission', function (string $key): void {
        $screen = ResourceScreens::all()[$key];
        $record = screenRecord($key);
        $viewer = adminWith([explode('|', $screen::viewPermission())[0]]);

        $list = $this->get(route($screen::routeName('index')))->assertOk();
        assertCspSafeHtml($list);
        // Storefront v2: a screen with its own list page (homepage sections, Figma 116:11200) has no table.
        $list->assertSee(ResourceScreens::make($key, $viewer)->indexView() === null ? 'data-module="table"' : 'data-section-list', false);

        $edit = $this->get(route($screen::routeName('edit'), ['record' => $record]))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        assertCspSafeHtml($edit);

        if ($screen::managePermission() !== $screen::viewPermission()) {
            $edit->assertSee(__('admin.common.read_only'))->assertDontSee('data-resource-submit', false);

            if ($screen::creatable()) {
                $list->assertDontSee(route($screen::routeName('create'), absolute: true));
                $this->get(route($screen::routeName('create')))->assertForbidden();
            }
        }
    })->with('screens');

    it('renders the create and edit forms in English and Arabic for staff who may manage', function (string $key): void {
        $screen = ResourceScreens::all()[$key];
        $record = screenRecord($key);
        adminWith([...explode('|', $screen::viewPermission()), ...explode('|', $screen::managePermission())]);

        $this->get(route($screen::routeName('edit'), ['record' => $record]))->assertOk()
            ->assertSee('data-method="'.ResourceScreens::make($key, auth()->user())->updateMethod().'"', false)
            ->assertSee('data-resource-submit', false);

        if ($screen::creatable()) {
            $this->withSession(['admin.locale' => 'ar'])->get(route($screen::routeName('create')))->assertOk()
                ->assertSee('dir="rtl"', false)->assertSee('data-method="POST"', false)->assertSee(__('admin.common.create', locale: 'ar'));
        }
    })->with('screens');

    it('serves each screen table and refuses it without the table permission', function (string $key): void {
        $screen = ResourceScreens::all()[$key];
        screenRecord($key);
        adminWith(['orders.view']);
        postTable($screen::table())->assertForbidden();

        adminWith(explode('|', $screen::viewPermission()));
        postTable($screen::table())->assertOk()->assertJsonPath('recordsTotal', fn (int $total): bool => $total >= 1);
    })->with('screens');

    it('lists the new sections in the sidebar per permission and opens the first permitted tab', function (): void {
        adminWith(['content.media', 'seo.redirects']);
        $page = $this->get('/admin')->assertRedirect(route('admin.content.redirects.index'));
        $nav = (string) str($this->get($page->headers->get('Location'))->getContent())->between('data-admin-sidebar', '</aside>');

        expect($nav)->toContain('>Content</span>')->not->toContain('>Catalog</span>')->not->toContain('>Marketing</span>');
    });

    it('escapes record values in table cells', function (): void {
        adminWith(['catalog.view']);
        GamingPlatform::factory()->create(['name' => '<img src=x onerror=alert(1)>']);

        $html = (string) postTable('platforms')->assertOk()->json('data.0.name');

        expect($html)->toContain('&lt;img src=x onerror=alert(1)&gt;')->not->toContain('<img');
    });
});

describe('writes through the admin API', function (): void {
    it('creates a platform from the form fields and audits it, with envelope errors beside fields', function (): void {
        adminWith(['catalog.view', 'catalog.manage']);

        $this->postJson('/admin/api/platforms', ['name' => 'Steam'])->assertUnprocessable()
            ->assertJsonValidationErrors(['slug', 'is_active']);

        $this->postJson('/admin/api/platforms', ['name' => 'Steam', 'slug' => 'steam', 'is_active' => true, 'sort_order' => '3'])->assertCreated();

        expect(AuditLog::query()->where('action', 'like', 'catalog.%platform%.created')->exists())->toBeTrue();
    });

    it('updates a country display currency (OD-1) with currency.manage only, audited', function (): void {
        CurrencyFactory::ensure('EGP');
        $country = Country::factory()->create(['code' => 'EG', 'default_currency' => 'USD']);

        adminWith(['pricing.view']);
        $this->patchJson('/admin/api/countries/EG', ['default_currency' => 'EGP'])->assertForbidden();

        adminWith(['currency.manage']);
        $this->patchJson('/admin/api/countries/EG', ['default_currency' => 'XXX'])->assertUnprocessable()->assertJsonValidationErrors('default_currency');
        $this->patchJson('/admin/api/countries/EG', ['default_currency' => 'EGP'])->assertOk()->assertJsonPath('data.default_currency', 'EGP');

        expect($country->refresh()->default_currency)->toBe('EGP')
            ->and(AuditLog::query()->where('action', 'currency.country_currency.updated')->first()?->after)->toMatchArray(['default_currency' => 'EGP']);
    });

    it('lists an affiliate commission with an approve action for pending ones', function (): void {
        $affiliate = Affiliate::factory()->create();
        AffiliateCommission::factory()->create(['affiliate_id' => $affiliate->id, 'status' => 'pending']);
        adminWith(['marketing.affiliates']);

        postTable('affiliate-commissions')->assertUnprocessable()->assertJsonValidationErrors('filters.affiliate');
        $cell = (string) postTable('affiliate-commissions', ['filters' => ['affiliate' => (string) $affiliate->id]])->assertOk()->json('data.0.actions');

        expect($cell)->toContain('data-action-url="'.e(url('/admin/api/affiliate-commissions/')));
    });
});

describe('translations (content_translations write endpoint)', function (): void {
    it('saves Arabic text for a product, removes empty fields and audits field names only', function (): void {
        $product = Product::factory()->create(['name' => 'Steam Wallet']);
        adminWith(['catalog.view', 'catalog.manage']);

        $this->putJson("/admin/api/translations/product/{$product->id}", ['locale' => 'ar', 'values' => ['name' => 'محفظة ستيم', 'description' => '<p>نص</p><script>x</script>']])
            ->assertOk()->assertJsonPath('data.values.ar.name', 'محفظة ستيم');

        expect(ContentTranslation::query()->where(['translatable_type' => 'product', 'translatable_id' => $product->id, 'field' => 'description'])->value('value'))->not->toContain('<script');

        $this->putJson("/admin/api/translations/product/{$product->id}", ['locale' => 'ar', 'values' => ['name' => '']])->assertOk();

        expect(ContentTranslation::query()->where(['translatable_type' => 'product', 'field' => 'name'])->exists())->toBeFalse();

        $audit = AuditLog::query()->where('action', 'content.translation.updated')->latest('id')->first();
        expect($audit->after)->toEqual(['locale' => 'ar', 'fields' => ['name']])
            ->and(json_encode($audit->after))->not->toContain('محفظة');
    });

    it('refuses the base locale, unknown fields and staff without the type permission', function (): void {
        $page = Page::factory()->create();
        adminWith(['catalog.manage']);
        $this->putJson("/admin/api/translations/page/{$page->id}", ['locale' => 'ar', 'values' => ['title' => 'x']])->assertForbidden();

        adminWith(['content.pages']);
        $this->putJson("/admin/api/translations/page/{$page->id}", ['locale' => 'en', 'values' => ['title' => 'x']])->assertUnprocessable()->assertJsonValidationErrors('locale');
        $this->putJson("/admin/api/translations/page/{$page->id}", ['locale' => 'ar', 'values' => ['slug' => 'x']])->assertUnprocessable()->assertJsonValidationErrors('values');
        $this->putJson('/admin/api/translations/page/999999', ['locale' => 'ar', 'values' => ['title' => 'x']])->assertNotFound();
    });

    it('shows saved translations on the edit page in an RTL field', function (): void {
        $faq = Faq::factory()->create();
        ContentTranslation::factory()->create(['translatable_type' => 'faq', 'translatable_id' => $faq->id, 'locale' => 'ar', 'field' => 'question', 'value' => 'سؤال محفوظ']);
        adminWith(['content.faq']);

        $this->get(route('admin.content.faqs.edit', ['record' => $faq->id]))->assertOk()
            ->assertSee('سؤال محفوظ')->assertSee('lang="ar"', false);
    });
});

describe('SEO fields', function (): void {
    it('saves SEO per locale with seo.manage and removes an empty row', function (): void {
        $page = Page::factory()->create();
        adminWith(['content.pages']);
        $this->putJson("/admin/api/seo/page/{$page->id}", ['locale' => 'en', 'title' => 'T'])->assertForbidden();

        adminWith(['content.pages', 'seo.manage']);
        $this->putJson("/admin/api/seo/page/{$page->id}", ['locale' => 'en', 'title' => 'Terms', 'robots' => 'index,follow'])->assertOk();
        $this->putJson("/admin/api/seo/page/{$page->id}", ['locale' => 'en', 'robots' => 'bogus'])->assertUnprocessable()->assertJsonValidationErrors('robots');
        $this->putJson("/admin/api/seo/page/{$page->id}", ['locale' => 'en', 'canonical_override' => 'javascript:alert(1)'])->assertUnprocessable()->assertJsonValidationErrors('canonical_override');

        expect(SeoMetadata::query()->where(['seoable_type' => 'page', 'seoable_id' => $page->id, 'locale' => 'en'])->value('title'))->toBe('Terms');

        $this->putJson("/admin/api/seo/page/{$page->id}", ['locale' => 'en', 'title' => '', 'robots' => ''])->assertOk();
        expect(SeoMetadata::query()->where(['seoable_type' => 'page', 'seoable_id' => $page->id])->exists())->toBeFalse()
            ->and(AuditLog::query()->where('action', 'seo.metadata.updated')->count())->toBe(2);
    });

    it('refuses types without SEO fields', function (): void {
        adminWith(['seo.manage']);
        $this->putJson('/admin/api/seo/faq/1', ['locale' => 'en'])->assertNotFound();
    });
});

describe('menu items panel', function (): void {
    it('shows the items of a menu with their edit forms', function (): void {
        $menu = Menu::factory()->create();
        MenuItem::factory()->create(['menu_id' => $menu->id, 'label' => 'Gift cards']);
        adminWith(['content.menus']);

        $this->get(route('admin.content.menus.edit', ['record' => $menu->id]))->assertOk()
            ->assertSee('Gift cards')->assertSee(url('/admin/api/menus/'.$menu->id.'/items'));
    });
});

it('shows the fixed gateway charge currencies on a currency page, without a form for them', function (): void {
    CurrencyFactory::ensure('EGP');
    PaymentGateway::factory()->create(['name' => 'Paymob', 'default_currency' => 'EGP']);
    adminWith(['pricing.view']);

    $this->get(route('admin.pricing.currencies.edit', ['record' => 'EGP']))->assertOk()
        ->assertSee('Gateway charge currency')->assertSee('Paymob');
});
