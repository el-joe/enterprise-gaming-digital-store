<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Domains\Currency\Services\DisplayCurrencyResolver;
use Illuminate\Http\Request;

/**
 * The display currency of a request (owner decision 1, OD-1): `DisplayCurrencyResolver`, shared
 * by the Blade pages, `/api/v1` and `/api/v1/mobile`. An explicit choice (`X-Currency`, the
 * `currency` cookie, the saved profile currency) wins; otherwise the customer's country currency
 * when active with a fresh rate; otherwise base. An invalid `X-Currency` is a 422.
 */
trait ResolvesDisplayCurrency
{
    protected function displayCurrency(Request $request): string
    {
        return app(DisplayCurrencyResolver::class)->currency($request);
    }
}
