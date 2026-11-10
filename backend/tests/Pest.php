<?php

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Catalog\Enums\ProductVariantAvailability;
use App\Domains\Catalog\Models\Product;
use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Services\ExchangeRateWriter;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\TwoFactorService;
use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\Models\Coupon;
use Database\Factories\Currency\CurrencyFactory;
use Database\Factories\Shared\CountryFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\Support\FakeCheckoutCoupons;
use Tests\Support\FakePaymentCurrencyResolver;
use Tests\TestCase;

require_once __DIR__.'/Support/FulfilmentFixtures.php';
require_once __DIR__.'/Support/PaymentFixtures.php';
require_once __DIR__.'/Support/M11Fixtures.php';
require_once __DIR__.'/Support/McpFixtures.php';
require_once __DIR__.'/Support/AutomationFixtures.php';
require_once __DIR__.'/Support/MobileFixtures.php';

pest()->extend(TestCase::class)
    ->use(LazilyRefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Create a staff member signed in with a session that has passed the two-factor challenge.
 */
function actingAsVerifiedStaff(?User $staff = null): User
{
    $staff ??= User::factory()->staff()->withTwoFactor()->create();

    test()->actingAs($staff)->withSession([TwoFactorService::SESSION_PASSED_KEY => now()->getTimestamp()]);

    return $staff;
}

/**
 * Sign in a verified staff member holding exactly the given permissions.
 *
 * @param  list<string>  $permissions
 */
function actingAsStaffWith(array $permissions = []): User
{
    $staff = User::factory()->staff()->withTwoFactor()->create();

    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    $staff->givePermissionTo($permissions);

    return actingAsVerifiedStaff($staff);
}

/**
 * Store a current rate for a currency against USD and drop the cached rate table.
 *
 * @param  array<string, mixed>  $attributes
 */
function givenRate(string $currency, string $rate, array $attributes = []): ExchangeRate
{
    CurrencyFactory::ensure('USD');
    CurrencyFactory::ensure($currency);

    $row = ExchangeRate::factory()->create([
        'base_currency' => 'USD',
        'quote_currency' => $currency,
        'provider_rate' => $rate,
        'effective_rate' => $rate,
        'source' => 'test_provider',
        'fetched_at' => now(),
        ...$attributes,
    ]);

    Cache::forget(ExchangeRateWriter::CACHE_KEY);

    return $row;
}

/**
 * A variant with a manual base cost.
 *
 * @param  array<string, mixed>  $attributes
 */
function variantCosting(string $cost, array $attributes = []): ProductVariant
{
    CurrencyFactory::ensure('USD');

    return ProductVariant::factory()->create(['manual_cost_base' => $cost, ...$attributes]);
}

/**
 * End the wrapping test transaction, for behaviour only visible to committed data: InnoDB
 * fulltext indexes and row locks held by another connection. The test must delete what it
 * creates and then call reopenTestTransaction(); a crashed run is reset by the migrate:fresh
 * at the start of the next one.
 */
function commitTestTransaction(): void
{
    DB::select('select 1');

    while (DB::transactionLevel() > 0) {
        DB::commit();
    }
}

/**
 * Give the framework a transaction to roll back. Without one it assumes the database is dirty
 * and migrates it again for the next test, which costs about 40 seconds each time.
 */
function reopenTestTransaction(): void
{
    DB::beginTransaction();
}

/**
 * All admin permission names, for tests asserting a permission is absent.
 *
 * @return list<string>
 */
function allPermissionNames(): array
{
    return array_map(static fn (PermissionName $name): string => $name->value, PermissionName::cases());
}

/**
 * An active product with one in-stock variant at a manual base cost.
 *
 * @param  array<string, mixed>  $variant
 * @param  array<string, mixed>  $product
 */
function sellableVariant(string $cost = '10', array $variant = [], array $product = []): ProductVariant
{
    CurrencyFactory::ensure('USD');
    CountryFactory::ensure('US');

    $owner = Product::factory()->create(['status' => ProductStatus::Active, ...$product]);

    return ProductVariant::factory()->create([
        'product_id' => $owner->id,
        'manual_cost_base' => $cost,
        'availability' => ProductVariantAvailability::InStock,
        ...$variant,
    ]);
}

/**
 * Bind the M10 test double: gateway key `fakepay`, charging in `$currency` or the display currency.
 */
function fakePayments(?string $currency = null): FakePaymentCurrencyResolver
{
    $gateway = PaymentGateway::query()->where('key', 'fakepay')->first() ?? PaymentGateway::factory()->create(['key' => 'fakepay']);
    $resolver = new FakePaymentCurrencyResolver($gateway->id, 'fakepay', $currency);

    app()->instance(PaymentCurrencyResolver::class, $resolver);

    return $resolver;
}

/**
 * Bind the M11 test double with the given coupons.
 *
 * @param  array<string, array{coupon: Coupon, terms: CouponTerms, variants?: list<int>}>  $coupons
 */
function fakeCoupons(array $coupons = []): FakeCheckoutCoupons
{
    $fake = new FakeCheckoutCoupons($coupons);

    app()->instance(CheckoutCoupons::class, $fake);

    return $fake;
}

/**
 * A place-order body; `expected_total` must match what the cart shows.
 *
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function placeOrderBody(string $expectedTotal, array $overrides = []): array
{
    return [
        'country' => 'US',
        'gateway' => 'fakepay',
        'currency' => 'USD',
        'expected_total' => $expectedTotal,
        'consents' => ['terms' => true],
        ...$overrides,
    ];
}
