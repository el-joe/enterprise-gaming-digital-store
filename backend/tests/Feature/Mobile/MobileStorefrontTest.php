<?php

/**
 * M-A3 (G2, G3): the cart through `X-Cart-Token`, the display currency through `X-Currency`, and
 * the locale through `Accept-Language`, all without cookies or a session.
 */

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Models\ContentTranslation;
use App\Domains\Catalog\Models\GamingPlatform;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Cart;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Support\Facades\DB;

const CART_ITEMS = 'api/v1/mobile/storefront/cart/items';

function arabicContent(string $type, int $id, string $field, string $value, string $locale = 'ar'): void
{
    ContentTranslation::factory()->create(['translatable_type' => $type, 'translatable_id' => $id, 'locale' => $locale, 'field' => $field, 'value' => $value]);
}

function pricedProduct(string $name, string $price = '10', array $product = []): Product
{
    CurrencyFactory::ensure('USD');

    $model = Product::factory()->create(['status' => ProductStatus::Active, 'name' => $name, ...$product]);
    ProductVariant::factory()->for($model)->create()->forceFill(['price_base' => $price])->save();

    return $model;
}

describe('guest cart by header', function (): void {
    it('issues a cart token on the first add, returns it once and stores only its hash, without a cookie', function (): void {
        $response = statelessCall('POST', CART_ITEMS, ['variant_id' => sellableVariant('10')->public_id, 'quantity' => 1])
            ->assertCreated()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertCookieMissing('cart_token');

        $token = $response->headers->get('X-Cart-Token');

        expect($token)->toMatch('/^[A-Za-z0-9]{64}$/')
            ->and($response->json('meta.cart_token'))->toBe($token)
            ->and(Cart::query()->sole()->session_token)->toBe(hash('sha256', $token));

        statelessCall('POST', CART_ITEMS, ['variant_id' => sellableVariant('5')->public_id, 'quantity' => 1], ['X-Cart-Token' => $token])
            ->assertCreated()
            ->assertHeaderMissing('X-Cart-Token')
            ->assertJsonMissingPath('meta.cart_token')
            ->assertJsonCount(2, 'data.cart.items');
    });

    it('updates, removes and applies a coupon with the header only', function (): void {
        $token = mobileGuestCart(sellableVariant('10'));
        $itemId = statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token])->assertOk()->json('data.cart.items.0.id');

        statelessCall('PATCH', CART_ITEMS."/{$itemId}", ['quantity' => 3], ['X-Cart-Token' => $token])
            ->assertOk()->assertJsonPath('data.cart.items.0.quantity', 3)->assertJsonPath('data.cart.totals.total.amount', '30.00');

        $coupon = Coupon::factory()->create(['code' => 'SAVE5']);
        fakeCoupons(['SAVE5' => ['coupon' => $coupon, 'terms' => new CouponTerms(CouponType::Fixed, '5', allowBelowMargin: true)]]);
        statelessCall('POST', 'api/v1/mobile/storefront/cart/coupon', ['code' => 'SAVE5'], ['X-Cart-Token' => $token])
            ->assertOk()->assertJsonPath('data.cart.coupon.applied', true)->assertJsonPath('data.cart.totals.total.amount', '25.00');

        statelessCall('DELETE', CART_ITEMS."/{$itemId}", headers: ['X-Cart-Token' => $token])->assertOk()->assertJsonCount(0, 'data.cart.items');
    });

    it('never shows or changes a cart to a client without its token', function (): void {
        $token = mobileGuestCart(sellableVariant('10'));
        $itemId = statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token])->json('data.cart.items.0.id');

        statelessCall('GET', 'api/v1/mobile/storefront/cart')->assertOk()->assertJsonPath('data.cart', null);
        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => str_repeat('x', 64)])->assertOk()->assertJsonPath('data.cart', null);
        statelessCall('PATCH', CART_ITEMS."/{$itemId}", ['quantity' => 2], ['X-Cart-Token' => str_repeat('x', 64)])->assertUnprocessable()->assertJsonPath('code', 'cart_empty');

        expect(Cart::query()->sole()->cartItems()->sole()->quantity)->toBe(1);
    });

    it('needs no CSRF token and ignores the browser cookie', function (): void {
        $token = mobileGuestCart(sellableVariant('10'));

        $this->withCredentials()->withCookie('cart_token', $token);
        statelessCall('GET', 'api/v1/mobile/storefront/cart')->assertOk()->assertJsonPath('data.cart', null);
    });
});

describe('cart adoption on mobile sign-in', function (): void {
    it('moves the guest cart to the customer who presents its token with a bearer token', function (): void {
        $user = User::factory()->create();
        $token = mobileGuestCart(sellableVariant('10'), 2);

        $access = mobileSignIn($user)['access_token'];

        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token], token: $access)
            ->assertOk()->assertJsonPath('data.cart.items.0.quantity', 2);

        expect(Cart::query()->sole())->user_id->toBe($user->id)->session_token->toBeNull();

        statelessCall('GET', 'api/v1/mobile/storefront/cart', token: $access)->assertOk()->assertJsonPath('data.cart.items.0.quantity', 2);
        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token])->assertOk()->assertJsonPath('data.cart', null);
    });

    it('keeps the customer cart when they already have one', function (): void {
        $user = User::factory()->create();
        $access = mobileSignIn($user)['access_token'];
        statelessCall('POST', CART_ITEMS, ['variant_id' => sellableVariant('7')->public_id, 'quantity' => 1], token: $access)->assertCreated()->assertHeaderMissing('X-Cart-Token');
        $token = mobileGuestCart(sellableVariant('10'));

        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token], token: $access)
            ->assertOk()->assertJsonPath('data.cart.totals.total.amount', '7.00');

        expect(Cart::query()->whereNull('user_id')->count())->toBe(1);
    });
});

describe('display currency header', function (): void {
    beforeEach(function (): void {
        givenRate('EGP', '50');
        Currency::query()->where('code', 'EGP')->update(['is_active' => true]);
    });

    it('prices the listing, the variants and the cart in the X-Currency currency', function (): void {
        $variant = sellableVariant('10');
        $variant->forceFill(['price_base' => '10'])->save();

        statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['X-Currency' => 'egp'])
            ->assertOk()->assertJsonPath('meta.display_currency', 'EGP')->assertJsonPath('data.0.price_from.currency', 'EGP');
        statelessCall('GET', "api/v1/storefront/catalog/products/{$variant->product->slug}/variants", headers: ['X-Currency' => 'EGP'])
            ->assertOk()->assertJsonPath('meta.display_currency', 'EGP')->assertJsonPath('data.variants.0.price.currency', 'EGP');

        $token = mobileGuestCart($variant);
        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token, 'X-Currency' => 'EGP'])
            ->assertOk()->assertJsonPath('data.cart.currency', 'EGP');
    });

    it('overrides the currency cookie', function (): void {
        pricedProduct('Card');

        $this->withCredentials()->withUnencryptedCookie('currency', 'EGP');
        statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['X-Currency' => 'USD'])->assertOk()->assertJsonPath('meta.display_currency', 'USD');
    });

    it('refuses a currency that is malformed, unknown or inactive', function (string $currency): void {
        CurrencyFactory::ensure('SAR');
        Currency::query()->where('code', 'SAR')->update(['is_active' => false]);

        statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['X-Currency' => $currency])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonPath('errors.currency.0', 'The X-Currency header must be the code of an active currency.');
    })->with(['malformed' => ['US'], 'unknown' => ['XYZ'], 'inactive' => ['SAR']]);
});

describe('locale', function (): void {
    it('returns Arabic names from content_translations and falls back to the English base column', function (): void {
        $platform = GamingPlatform::factory()->create(['name' => 'Orbit']);
        $translated = pricedProduct('Steam Card', '10', ['gaming_platform_id' => $platform->id, 'short_description' => 'Top up', 'sales_count' => 9]);
        pricedProduct('Plain Card', '5', ['sales_count' => 1]);
        arabicContent('product', $translated->id, 'name', 'بطاقة ستيم');
        arabicContent('product', $translated->id, 'short_description', 'اشحن رصيدك');
        arabicContent('gaming_platform', $platform->id, 'name', 'أوربت');
        arabicContent('product', $translated->id, 'name', 'Carte Steam', 'fr');

        statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['Accept-Language' => 'ar-EG,ar;q=0.9,en;q=0.5'])
            ->assertOk()
            ->assertHeader('Content-Language', 'ar')
            ->assertJsonPath('data.0.name', 'بطاقة ستيم')
            ->assertJsonPath('data.0.short_description', 'اشحن رصيدك')
            ->assertJsonPath('data.0.platform.name', 'أوربت')
            ->assertJsonPath('data.1.name', 'Plain Card');

        statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['Accept-Language' => 'fr-FR'])
            ->assertOk()->assertHeader('Content-Language', 'en')->assertJsonPath('data.0.name', 'Steam Card');
    });

    it('translates variant titles and cart lines', function (): void {
        $variant = sellableVariant('10', ['title' => '10 USD'], ['name' => 'Wallet Card']);
        arabicContent('product_variant', $variant->id, 'title', '10 دولار');
        arabicContent('product', $variant->product_id, 'name', 'بطاقة محفظة');

        statelessCall('GET', "api/v1/storefront/catalog/products/{$variant->product->slug}/variants", headers: ['Accept-Language' => 'ar'])
            ->assertOk()->assertJsonPath('data.variants.0.title', '10 دولار');

        $token = mobileGuestCart($variant);
        statelessCall('GET', 'api/v1/mobile/storefront/cart', headers: ['X-Cart-Token' => $token, 'Accept-Language' => 'ar'])
            ->assertOk()
            ->assertJsonPath('data.cart.items.0.variant.title', '10 دولار')
            ->assertJsonPath('data.cart.items.0.product.name', 'بطاقة محفظة');
    });

    it('adds one translation query to a listing page in Arabic and none in English', function (): void {
        foreach (range(1, 5) as $index) {
            pricedProduct("Card {$index}", '10', ['gaming_platform_id' => GamingPlatform::factory()->create()->id]);
        }

        $count = function (string $language): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            statelessCall('GET', 'api/v1/storefront/catalog/products', headers: ['Accept-Language' => $language])->assertOk();
            DB::disableQueryLog();

            return collect(DB::getQueryLog())->filter(fn (array $query): bool => str_contains($query['query'], 'content_translations'))->count();
        };

        expect($count('en'))->toBe(0)->and($count('ar'))->toBe(1);
    });

    it('localizes validation and domain messages but never the codes', function (): void {
        CountryFactory::ensure('US');

        statelessCall('POST', CART_ITEMS, [], ['Accept-Language' => 'ar'])
            ->assertUnprocessable()
            ->assertHeader('Content-Language', 'ar')
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonPath('message', 'البيانات المرسلة غير صالحة.')
            ->assertJsonPath('errors.variant_id.0', 'حقل المنتج مطلوب.');

        statelessCall('POST', 'api/v1/mobile/checkout/validate', ['country' => 'US'], ['Accept-Language' => 'ar', 'X-Cart-Token' => str_repeat('a', 64)])
            ->assertUnprocessable()
            ->assertJsonPath('code', 'cart_empty')
            ->assertJsonPath('message', 'سلتك فارغة.');

        statelessCall('POST', CART_ITEMS, [])
            ->assertUnprocessable()
            ->assertJsonPath('errors.variant_id.0', 'The variant id field is required.');
    });

    it('prefers the saved locale of a signed-in web customer over the header', function (): void {
        $user = User::factory()->create(['locale' => 'ar']);

        $this->actingAs($user)->getJson('/api/v1/account/wallet', ['Accept-Language' => 'en'])->assertOk()->assertHeader('Content-Language', 'ar');
        $this->actingAs(User::factory()->create(['locale' => null]))->getJson('/api/v1/account/wallet', ['Accept-Language' => 'ar'])->assertOk()->assertHeader('Content-Language', 'ar');
    });
});
