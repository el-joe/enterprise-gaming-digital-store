<?php

namespace App\Domains\Currency\Services;

use App\Domains\Currency\DTOs\DisplayCurrencyDecision;
use App\Domains\Currency\Enums\DisplayCurrencySource;
use App\Domains\Identity\Models\User;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Shared\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The display currency of any price-bearing request, web page or JSON (owner decision 1, OD-1).
 * One resolver for the Blade pages, `/api/v1` and `/api/v1/mobile`; the decision is kept on the
 * request, so every price in one response uses the same currency and the lookups run once.
 *
 * Order:
 * 1. An explicit choice always wins: the `X-Currency` header (stateless clients; 422 unless an
 *    active currency), else the `currency` cookie (the web switcher), else the signed-in
 *    customer's saved `users.display_currency`. An active currency whose rate is missing falls
 *    back to base, as before; a stale rate is tolerated and flagged on display.
 * 2. Otherwise the customer's country: the signed-in customer's `users.country_code`, else the
 *    GeoIP country (`GeoIpResolver`). Its `countries.default_currency` is used only if that
 *    currency is active with a fresh rate.
 * 3. Otherwise the base currency.
 *
 * Nothing is redirected and the country never appears in a URL.
 */
class DisplayCurrencyResolver
{
    public const string REQUEST_ATTRIBUTE = 'storefront.display_currency';

    public function __construct(
        private CurrencyConverter $converter,
        private ExchangeRateRegistry $registry,
        private GeoIpResolver $geoIp,
    ) {}

    public function currency(Request $request): string
    {
        return $this->resolve($request)->currency;
    }

    /**
     * @throws ValidationException when `X-Currency` is not an active currency
     */
    public function resolve(Request $request): DisplayCurrencyDecision
    {
        $cached = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        if ($cached instanceof DisplayCurrencyDecision) {
            return $cached;
        }

        $decision = $this->decide($request);
        $request->attributes->set(self::REQUEST_ATTRIBUTE, $decision);

        return $decision;
    }

    private function decide(Request $request): DisplayCurrencyDecision
    {
        $header = $request->headers->get('X-Currency');

        if ($header !== null) {
            if (preg_match('/^[A-Za-z]{3}$/', $header) !== 1 || ! $this->converter->isActiveCurrency($header)) {
                throw ValidationException::withMessages(['currency' => [__('The X-Currency header must be the code of an active currency.')]]);
            }

            return new DisplayCurrencyDecision($this->converter->resolveDisplayCurrency(strtoupper($header)), DisplayCurrencySource::Header);
        }

        $cookie = $request->cookie('currency');

        if (is_string($cookie) && $cookie !== '') {
            return new DisplayCurrencyDecision($this->converter->resolveDisplayCurrency($cookie), DisplayCurrencySource::Cookie);
        }

        $user = $request->user();
        $user = $user instanceof User ? $user : null;

        if (is_string($user?->display_currency) && $user->display_currency !== '') {
            return new DisplayCurrencyDecision($this->converter->resolveDisplayCurrency($user->display_currency), DisplayCurrencySource::Profile);
        }

        $country = $this->country($request, $user);
        $currency = $country === null ? null : $this->countryCurrency($country);

        if ($currency !== null) {
            return new DisplayCurrencyDecision($currency, DisplayCurrencySource::Country, $country);
        }

        return new DisplayCurrencyDecision($this->registry->baseCurrency(), DisplayCurrencySource::Base, $country);
    }

    private function country(Request $request, ?User $user): ?string
    {
        $profile = $user?->country_code;

        if (is_string($profile) && $profile !== '') {
            return strtoupper($profile);
        }

        return $this->geoIp->countryFor($request);
    }

    /**
     * The country's currency when it is active with a fresh rate (the checkout test), else null.
     */
    private function countryCurrency(string $country): ?string
    {
        $code = Country::query()->whereKey($country)->value('default_currency');

        if (! is_string($code) || $code === '') {
            return null;
        }

        $decision = $this->converter->resolveCheckoutCurrency($code);

        return $decision->fellBackToBase ? null : $decision->currency;
    }
}
