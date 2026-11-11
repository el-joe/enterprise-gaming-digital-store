<?php

/**
 * OD-1, owner decision 1: every price-bearing response shows prices in the customer's country
 * currency (profile country, else GeoIP) when that currency is active with a fresh rate,
 * otherwise base; an explicit choice (X-Currency, the currency cookie, the saved profile
 * currency) always wins. One resolver for the web pages, /api/v1 and /api/v1/mobile.
 */

use App\Domains\Currency\Enums\DisplayCurrencySource;
use App\Domains\Currency\Models\Currency;
use App\Domains\Currency\Services\DisplayCurrencyResolver;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Shared\Models\Country;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    CurrencyFactory::ensure('USD');
    givenRate('EGP', '50');
    givenRate('SAR', '3.75');
    Country::query()->updateOrCreate(['code' => 'EG'], ['name' => 'Egypt', 'default_currency' => 'EGP', 'is_active' => true]);
    Country::query()->updateOrCreate(['code' => 'SA'], ['name' => 'Saudi Arabia', 'default_currency' => 'SAR', 'is_active' => true]);
    Country::query()->updateOrCreate(['code' => 'US'], ['name' => 'United States', 'default_currency' => 'USD', 'is_active' => true]);
    config(['payments.geoip.driver' => 'header']);
    app()->forgetInstance(GeoIpResolver::class);
});

/**
 * @param  array<string, string>  $headers
 * @param  array<string, string>  $cookies
 */
function displayRequest(array $headers = [], array $cookies = [], ?User $user = null): Request
{
    $request = Request::create('/api/v1/storefront/home', 'GET', cookies: $cookies);

    foreach ($headers as $name => $value) {
        $request->headers->set($name, $value);
    }

    $request->setUserResolver(static fn (): ?User => $user);

    return $request;
}

describe('the resolver', function (): void {
    it('uses the GeoIP country currency when it is active with a fresh rate', function (): void {
        $decision = app(DisplayCurrencyResolver::class)->resolve(displayRequest(['CF-IPCountry' => 'EG']));

        expect($decision->currency)->toBe('EGP')
            ->and($decision->source)->toBe(DisplayCurrencySource::Country)
            ->and($decision->country)->toBe('EG');
    });

    it('falls back to base when the country currency is stale, inactive, unknown or missing a rate', function (Closure $break): void {
        $break();

        $decision = app(DisplayCurrencyResolver::class)->resolve(displayRequest(['CF-IPCountry' => 'EG']));

        expect($decision->currency)->toBe('USD')->and($decision->source)->toBe(DisplayCurrencySource::Base);
    })->with([
        'stale rate' => [fn () => DB::table('exchange_rates')->where('quote_currency', 'EGP')->update(['fetched_at' => now()->subDays(3)]) && cache()->flush()],
        'inactive currency' => [fn () => Currency::query()->whereKey('EGP')->update(['is_active' => false]) && cache()->flush()],
        'country without currency' => [fn () => Country::query()->whereKey('EG')->update(['default_currency' => null])],
        'no rate' => [fn () => DB::table('exchange_rates')->where('quote_currency', 'EGP')->delete() && cache()->flush()],
    ]);

    it('falls back to base with no country at all (null GeoIP driver, guest)', function (): void {
        config(['payments.geoip.driver' => 'null']);
        app()->forgetInstance(GeoIpResolver::class);

        $decision = app(DisplayCurrencyResolver::class)->resolve(displayRequest(['CF-IPCountry' => 'EG']));

        expect($decision->currency)->toBe('USD')->and($decision->source)->toBe(DisplayCurrencySource::Base)->and($decision->country)->toBeNull();
    });

    it('prefers the signed-in customer profile country over GeoIP', function (): void {
        $user = User::factory()->create(['country_code' => 'SA']);

        $decision = app(DisplayCurrencyResolver::class)->resolve(displayRequest(['CF-IPCountry' => 'EG'], user: $user));

        expect($decision->currency)->toBe('SAR')->and($decision->country)->toBe('SA');
    });

    it('lets an explicit choice win over the country', function (array $headers, array $cookies, array $profile, string $expected, DisplayCurrencySource $source): void {
        $user = $profile === [] ? null : User::factory()->create(['country_code' => 'EG', ...$profile]);

        $decision = app(DisplayCurrencyResolver::class)->resolve(displayRequest(['CF-IPCountry' => 'EG', ...$headers], $cookies, $user));

        expect($decision->currency)->toBe($expected)->and($decision->source)->toBe($source);
    })->with([
        'X-Currency' => [['X-Currency' => 'SAR'], ['currency' => 'USD'], ['display_currency' => 'USD'], 'SAR', DisplayCurrencySource::Header],
        'cookie' => [[], ['currency' => 'USD'], ['display_currency' => 'SAR'], 'USD', DisplayCurrencySource::Cookie],
        'saved profile currency' => [[], [], ['display_currency' => 'USD'], 'USD', DisplayCurrencySource::Profile],
    ]);

    it('decides once per request', function (): void {
        $request = displayRequest(['CF-IPCountry' => 'EG']);
        $resolver = app(DisplayCurrencyResolver::class);
        $first = $resolver->resolve($request);

        DB::enableQueryLog();
        $second = $resolver->resolve($request);

        expect($second)->toBe($first)->and(DB::getQueryLog())->toBe([]);
    });
});

describe('over HTTP', function (): void {
    it('prices the public storefront for web and app by GeoIP country, without redirecting, and varies the cache on it', function (string $prefix): void {
        $response = $this->getJson($prefix.'/storefront/home', ['CF-IPCountry' => 'EG'])->assertOk();

        expect($response->json('meta.display_currency'))->toBe('EGP')
            ->and($response->headers->get('Vary'))->toContain('CF-IPCountry');

        $this->getJson($prefix.'/storefront/home', ['CF-IPCountry' => 'EG', 'X-Currency' => 'USD'])->assertOk()->assertJsonPath('meta.display_currency', 'USD');
    })->with(['web' => ['/api/v1'], 'mobile' => ['/api/v1/mobile']]);

    it('does not vary the cache on a GeoIP header that is not used', function (): void {
        config(['payments.geoip.driver' => 'null']);
        app()->forgetInstance(GeoIpResolver::class);

        $response = $this->getJson('/api/v1/storefront/home', ['CF-IPCountry' => 'EG'])->assertOk()->assertJsonPath('meta.display_currency', 'USD');

        expect($response->headers->get('Vary'))->not->toContain('CF-IPCountry');
    });

    it('prices a signed-in customer cart in the profile country currency, on the web session and the mobile bearer', function (string $client): void {
        $user = User::factory()->create(['country_code' => 'EG']);
        $variant = sellableVariant('10');

        $response = $client === 'web'
            ? $this->actingAs($user)->postJson('/api/v1/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1])
            : statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => 1], [], accountMobileToken($user));

        $response->assertCreated()->assertJsonPath('data.cart.currency', 'EGP');
    })->with(['web', 'mobile']);

    it('refuses an X-Currency that is not an active currency, as before', function (): void {
        $this->getJson('/api/v1/storefront/home', ['X-Currency' => 'ZZZ'])->assertUnprocessable();
    });
});
