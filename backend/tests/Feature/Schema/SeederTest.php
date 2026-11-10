<?php

use App\Domains\Automation\Models\AutomationWorkflow;
use App\Domains\Currency\Models\Currency;
use App\Domains\Identity\Enums\RoleName;
use App\Domains\Identity\Enums\UserType;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\PaymentEligibilityRule;
use App\Domains\Payments\Models\PaymentGateway;
use App\Domains\Settings\Models\Setting;
use App\Domains\Shared\Models\Country;
use App\Domains\Suppliers\Enums\SupplierStatus;
use App\Domains\Suppliers\Models\Supplier;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('seeds reference data, no rates, no users and no orders', function (): void {
    $this->seed();

    expect(DB::table('exchange_rates')->count())->toBe(0)
        ->and(DB::table('exchange_rate_history')->count())->toBe(0)
        ->and(User::query()->count())->toBe(0)
        ->and(Order::query()->count())->toBe(0)
        ->and(DB::table('products')->count())->toBe(0)
        ->and(Currency::query()->where('code', 'USD')->value('is_active'))->toBeTrue()
        ->and(Currency::query()->where('is_active', true)->count())->toBe(1)
        ->and(Currency::query()->where('code', 'KWD')->value('exponent'))->toBe(3)
        ->and(Country::query()->where('code', 'EG')->value('default_currency'))->toBe('EGP')
        ->and(Setting::query()->where('group', 'currency')->where('key', 'base')->value('value'))->toBe('USD')
        ->and(Role::query()->count())->toBe(8)
        ->and(AutomationWorkflow::query()->where('is_enabled', true)->count())->toBe(0);
});

it('can be seeded twice without duplicates', function (): void {
    $this->seed();
    $counts = [Currency::query()->count(), Country::query()->count(), PaymentEligibilityRule::query()->count(), AutomationWorkflow::query()->count()];

    $this->seed();

    expect([Currency::query()->count(), Country::query()->count(), PaymentEligibilityRule::query()->count(), AutomationWorkflow::query()->count()])->toBe($counts);
});

it('seeds gateways inactive with no credentials', function (): void {
    $this->seed();

    expect(PaymentGateway::query()->pluck('key')->all())->toEqualCanonicalizing(['stripe', 'paypal', 'paymob', 'wallet'])
        ->and(PaymentGateway::query()->where('is_active', true)->count())->toBe(0)
        ->and(DB::table('payment_gateway_credentials')->count())->toBe(0);
});

it('restricts Paymob to Egypt, in EGP, with a matching IP', function (): void {
    $this->seed();

    $rule = PaymentEligibilityRule::query()
        ->whereBelongsTo(PaymentGateway::query()->where('key', 'paymob')->firstOrFail(), 'paymentGateway')
        ->sole();

    expect($rule->countries)->toBe(['EG'])
        ->and($rule->ip_countries)->toBe(['EG'])
        ->and($rule->require_ip_match)->toBeTrue()
        ->and($rule->currencies)->toBe(['EGP']);
});

it('keeps Reloadly inactive and the built-in fulfilment paths active', function (): void {
    $this->seed();

    expect(Supplier::query()->where('adapter_key', 'reloadly_giftcards')->value('status'))->toBe(SupplierStatus::Inactive)
        ->and(Supplier::query()->where('adapter_key', 'internal_stock')->value('status'))->toBe(SupplierStatus::Active)
        ->and(DB::table('supplier_credentials')->count())->toBe(0);
});

it('fails clearly when super admin credentials are missing', function (?string $email, ?string $password, string $missing): void {
    config(['identity.super_admin' => ['email' => $email, 'password' => $password]]);

    expect(fn () => app(SuperAdminSeeder::class)->run())->toThrow(RuntimeException::class, $missing);
    expect(User::query()->count())->toBe(0);
})->with([
    'no email' => [null, 'Str0ngPassphrase', 'SUPER_ADMIN_EMAIL'],
    'blank password' => ['root@example.com', ' ', 'SUPER_ADMIN_PASSWORD'],
]);

it('rejects a weak super admin password', function (): void {
    config(['identity.super_admin' => ['email' => 'root@example.com', 'password' => 'weak']]);

    expect(fn () => app(SuperAdminSeeder::class)->run())->toThrow(RuntimeException::class, '12 characters');
});

it('creates a verified staff Super Admin from configuration', function (): void {
    config(['identity.super_admin' => ['email' => 'Root@Example.com', 'password' => 'Str0ngPassphrase']]);

    app(SuperAdminSeeder::class)->run();

    $user = User::query()->where('email', 'root@example.com')->sole();

    expect($user->type)->toBe(UserType::Staff)
        ->and($user->hasVerifiedEmail())->toBeTrue()
        ->and($user->hasRole(RoleName::SuperAdmin->value))->toBeTrue()
        ->and(Hash::check('Str0ngPassphrase', $user->password))->toBeTrue();
});

it('refuses to seed the demo catalog in production', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    expect(fn () => app(DemoCatalogSeeder::class)->run())->toThrow(RuntimeException::class, 'production');
    expect(DB::table('products')->count())->toBe(0);
});

it('seeds the demo catalog outside production', function (): void {
    app(DemoCatalogSeeder::class)->run();
    app(DemoCatalogSeeder::class)->run();

    expect(DB::table('products')->count())->toBe(1)->and(DB::table('product_variants')->count())->toBe(3);
});
