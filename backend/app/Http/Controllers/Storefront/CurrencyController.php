<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\SetCurrencyRequest;
use Illuminate\Http\JsonResponse;

class CurrencyController extends Controller
{
    private const int COOKIE_MINUTES = 525600;

    /**
     * Remember the display currency. An unusable choice falls back to the base currency.
     */
    public function store(SetCurrencyRequest $request, CurrencyConverter $converter): JsonResponse
    {
        $currency = $converter->resolveDisplayCurrency($request->validated('currency'));

        return $this->success(['currency' => $currency])
            ->withCookie(cookie('currency', $currency, self::COOKIE_MINUTES, secure: $request->isSecure(), httpOnly: false, sameSite: 'lax'));
    }
}
