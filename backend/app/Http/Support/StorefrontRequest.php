<?php

namespace App\Http\Support;

use App\Domains\Orders\DTOs\CartOwner;
use App\Http\Controllers\Storefront\Concerns\InteractsWithCart;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * The cart owner and display currency of a storefront page request, resolved exactly as the
 * cart and checkout JSON resolve them (InteractsWithCart), so a page and the JSON it calls
 * always agree on whose cart and which currency.
 */
final class StorefrontRequest
{
    use InteractsWithCart;

    public function owner(Request $request): CartOwner
    {
        return $this->cartOwner($request);
    }

    /**
     * The owner for read-only page chrome (the header bag count): a suspended account sees an
     * empty bag instead of an error on every page.
     */
    public function ownerOrNull(Request $request): ?CartOwner
    {
        try {
            return $this->cartOwner($request);
        } catch (AccessDeniedHttpException) {
            return null;
        }
    }

    public function currency(Request $request): string
    {
        return $this->displayCurrency($request);
    }
}
