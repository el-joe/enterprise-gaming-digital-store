<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\CartOwner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Cart ownership from the request: the signed-in customer, or a guest cart token issued on the
 * first write. The browser holds it in the encrypted, HTTP-only `cart_token` cookie; a stateless
 * client (M-A3, ADR-036) sends it in the `X-Cart-Token` header and receives it in the same header
 * and in `meta.cart_token` when issued. Only its SHA-256 hash is stored. A signed-in customer who
 * presents a guest token adopts that cart when they have none (merge on sign-in).
 */
trait InteractsWithCart
{
    use ResolvesDisplayCurrency;

    private static string $cartCookie = 'cart_token';

    private static string $cartHeader = 'X-Cart-Token';

    private static int $cartCookieMinutes = 43200;

    protected function cartOwner(Request $request, bool $issueToken = false): CartOwner
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null && $user->status !== UserStatus::Active) {
            throw new AccessDeniedHttpException;
        }

        $token = $this->presentedCartToken($request);

        if ($user !== null) {
            return CartOwner::user($user, $token);
        }

        return CartOwner::guest($token ?? ($issueToken ? Str::random(64) : null));
    }

    /**
     * Hand a newly issued guest token to the client: the cookie for the browser, the header and
     * `meta.cart_token` for a stateless client.
     */
    protected function withCartCookie(JsonResponse $response, Request $request, CartOwner $owner): JsonResponse
    {
        if (! $owner->isGuest() || $owner->guestToken === null || $this->presentedCartToken($request) === $owner->guestToken) {
            return $response;
        }

        if ($request->hasSession()) {
            return $response->withCookie(cookie(self::$cartCookie, $owner->guestToken, self::$cartCookieMinutes, secure: $request->isSecure(), httpOnly: true, sameSite: 'lax'));
        }

        // Decoded as objects so empty JSON objects (such as an item's `delivery_values`) stay `{}`.
        $body = $response->getData();
        $body->meta = (object) [...(array) ($body->meta ?? []), 'cart_token' => $owner->guestToken];

        return $response->setData($body)
            ->header(self::$cartHeader, $owner->guestToken)
            ->header('Cache-Control', 'no-store, private');
    }

    private function presentedCartToken(Request $request): ?string
    {
        $token = $request->hasSession() ? $request->cookie(self::$cartCookie) : $request->headers->get(self::$cartHeader);

        return is_string($token) && preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1 ? $token : null;
    }
}
