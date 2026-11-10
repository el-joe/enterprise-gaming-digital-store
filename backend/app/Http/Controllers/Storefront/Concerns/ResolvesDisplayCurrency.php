<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Domains\Currency\Services\CurrencyConverter;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * The display currency of a request: the `X-Currency` header (M-A3, stateless clients), else the
 * `currency` cookie (the browser). The header must name an active currency, otherwise 422; an
 * active currency without a usable rate falls back to the base currency, as the cookie does.
 */
trait ResolvesDisplayCurrency
{
    protected function displayCurrency(Request $request): string
    {
        $converter = app(CurrencyConverter::class);
        $header = $request->headers->get('X-Currency');

        if ($header !== null) {
            if (preg_match('/^[A-Za-z]{3}$/', $header) !== 1 || ! $converter->isActiveCurrency($header)) {
                throw ValidationException::withMessages(['currency' => [__('The X-Currency header must be the code of an active currency.')]]);
            }

            return $converter->resolveDisplayCurrency(strtoupper($header));
        }

        $cookie = $request->cookie('currency');

        return $converter->resolveDisplayCurrency(is_string($cookie) ? $cookie : null);
    }
}
