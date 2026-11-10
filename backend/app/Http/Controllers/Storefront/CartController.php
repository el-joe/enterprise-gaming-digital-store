<?php

namespace App\Http\Controllers\Storefront;

use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Storefront\Concerns\InteractsWithCart;
use App\Http\Requests\Storefront\AddCartItemRequest;
use App\Http\Requests\Storefront\ApplyCouponRequest;
use App\Http\Requests\Storefront\UpdateCartItemRequest;
use App\Http\Resources\Storefront\CartResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use InteractsWithCart;

    public function __construct(private CartService $carts) {}

    public function show(Request $request): JsonResponse
    {
        $cart = $this->carts->current($this->cartOwner($request), $this->displayCurrency($request));

        return $this->respond($cart);
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $owner = $this->cartOwner($request, issueToken: true);

        $cart = $this->carts->addItem(
            $owner,
            $request->validated('variant_id'),
            (int) $request->validated('quantity'),
            $request->validated('delivery_values', []),
            $this->displayCurrency($request),
        );

        return $this->withCartCookie($this->respond($cart, 'Added to cart.', 201), $request, $owner);
    }

    public function update(UpdateCartItemRequest $request, int $cartItem): JsonResponse
    {
        return $this->respond($this->carts->updateQuantity($this->cartOwner($request), $cartItem, (int) $request->validated('quantity')), 'Cart updated.');
    }

    public function destroy(Request $request, int $cartItem): JsonResponse
    {
        return $this->respond($this->carts->removeItem($this->cartOwner($request), $cartItem), 'Item removed.');
    }

    public function applyCoupon(ApplyCouponRequest $request): JsonResponse
    {
        return $this->respond($this->carts->applyCoupon($this->cartOwner($request), $request->validated('code')), 'Coupon applied.');
    }

    public function removeCoupon(Request $request): JsonResponse
    {
        return $this->respond($this->carts->removeCoupon($this->cartOwner($request)), 'Coupon removed.');
    }

    private function respond(?Cart $cart, string $message = 'OK', int $status = 200): JsonResponse
    {
        return $this->success(
            ['cart' => $cart === null ? null : (new CartResource($this->carts->quote($cart)))->resolve()],
            $message,
            status: $status,
        );
    }
}
