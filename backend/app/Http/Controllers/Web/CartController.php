<?php

namespace App\Http\Controllers\Web;

use App\Domains\Orders\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Storefront\CartResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The bag page (`/cart`). Quantity, removal and coupons post to the cart JSON; the page then
 * asks for the `cart` fragment again (`X-Fragment: cart`), so every amount is rendered by the
 * server from the same CartResource the JSON returns.
 */
class CartController extends Controller
{
    public function show(Request $request, CartService $carts, StorefrontRequest $storefront): View|string
    {
        $cart = $carts->current($storefront->owner($request), $storefront->currency($request));

        return view('storefront.cart', [
            'cart' => $cart === null ? null : (new CartResource($carts->quote($cart)))->resolve($request),
        ])->fragmentIf($request->header('X-Fragment') === 'cart', 'cart');
    }
}
