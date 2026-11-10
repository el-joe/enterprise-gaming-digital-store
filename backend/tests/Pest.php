<?php

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Currency\Models\ExchangeRate;
use App\Domains\Currency\Services\ExchangeRateWriter;
use App\Domains\Identity\Enums\PermissionName;
use App\Domains\Identity\Models\User;
use App\Domains\Identity\Services\TwoFactorService;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

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
