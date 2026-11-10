<?php

namespace App\Http\Controllers\Web;

use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Services\CartService;
use App\Domains\Payments\Contracts\GeoIpResolver;
use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Domains\Shared\Models\Country;
use App\Domains\Shared\Services\CountryDirectory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\CartResource;
use App\Http\Support\LocalizedRoutes;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Checkout page (`/checkout`). Contact, country and payment method; payment options come from
 * `GET /api/v1/checkout/payment-options` and the order from `POST /api/v1/checkout/place-order`,
 * whose responses decide the payment currency and amount the page shows. While guest checkout
 * is off (owner decision 4, `storefront.guest_checkout`), a guest is sent to sign in and back.
 */
class CheckoutController extends Controller
{
    public function show(Request $request, CartService $carts, StorefrontRequest $storefront, CountryDirectory $countries, GeoIpResolver $geoIp, PaymentPublicCredentials $credentials, StorefrontCatalogService $catalog): View|RedirectResponse|string
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user === null && ! config('storefront.guest_checkout')) {
            return redirect()->setIntendedUrl(LocalizedRoutes::url('storefront.checkout'))
                ->to(LocalizedRoutes::url('storefront.login'));
        }

        $cart = $carts->current($storefront->owner($request), $storefront->currency($request));

        if ($cart === null || $cart->cartItems->isEmpty()) {
            return redirect()->to(LocalizedRoutes::url('storefront.cart'));
        }

        $active = $countries->active();
        $codes = $active->pluck('code')->all();
        $preferred = collect([$cart->country_code, $user?->country_code, $geoIp->countryFor($request)])
            ->first(static fn (?string $code): bool => $code !== null && in_array($code, $codes, true));

        return view('storefront.checkout', [
            'cart' => (new CartResource($carts->quote($cart)))->resolve($request),
            'countries' => $active->map(static fn (Country $country): array => ['code' => $country->code, 'name' => $countries->localizedName($country)])
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values()->all(),
            'country' => $preferred,
            'currencies' => $catalog->currencies(),
            'guest' => $user === null,
            'email' => $user?->email,
            'stripeKey' => $credentials->forActiveGateways()['stripe']['publishable_key'] ?? null,
        ])->fragmentIf($request->header('X-Fragment') === 'summary', 'summary');
    }
}
