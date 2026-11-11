<?php

namespace App\Http\Controllers\Account;

use App\Domains\Catalog\Services\WishlistService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreWishlistItemRequest;
use App\Http\Resources\Storefront\ProductCardResource;
use App\Http\Support\StorefrontRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET, POST and DELETE /account/wishlist (M13): saved products as listing cards in the display
 * currency. Rows are always the caller's own; there is no id to guess.
 */
class WishlistController extends Controller
{
    public function __construct(private WishlistService $wishlist) {}

    public function index(Request $request, StorefrontRequest $storefront): JsonResponse
    {
        return $this->success(['items' => ProductCardResource::collection($this->wishlist->cards($request->user(), $storefront->currency($request)))->resolve($request)]);
    }

    public function store(StoreWishlistItemRequest $request): JsonResponse
    {
        $product = $this->wishlist->add($request->user(), $request->validated('product_slug'));

        return $this->success(['product_slug' => $product->slug, 'saved' => true], 'Saved to your wishlist.', status: 201);
    }

    public function destroy(Request $request, string $slug): JsonResponse
    {
        $this->wishlist->remove($request->user(), $slug);

        return $this->success(['product_slug' => $slug, 'saved' => false], 'Removed from your wishlist.');
    }
}
