<?php

namespace App\Http\Controllers\Web\Account;

use App\Domains\Catalog\Services\ContentTranslator;
use App\Domains\Catalog\Services\WishlistService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Account\Concerns\PresentsAccountData;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Wishlist (`/account/wishlist`, M13, Figma "Account Wishlist"): saved products as listing
 * cards in the display currency, each with a remove button (`DELETE /api/v1/account/wishlist/{slug}`).
 */
class WishlistController extends Controller
{
    use PresentsAccountData;

    public function show(Request $request, WishlistService $wishlist, ContentTranslator $translator, StorefrontRequest $storefront): View
    {
        return view('account.wishlist', [
            'products' => $this->productCards($wishlist->cards($request->user(), $storefront->currency($request)), $translator, $request),
        ]);
    }
}
