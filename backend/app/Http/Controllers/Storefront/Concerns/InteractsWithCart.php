<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Domains\Currency\Services\CurrencyConverter;
use App\Domains\Identity\Enums\UserStatus;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\DTOs\CartOwner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Cart ownership from the request: the signed-in customer, or the guest `cart_token` cookie
 * (encrypted and HTTP-only), issued on the first write.
 */
trait InteractsWithCart
{
    private static string $cartCookie = 'cart_token';

    private static int $cartCookieMinutes = 43200;

    protected function cartOwner(Request $request, bool $issueToken = false): CartOwner
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null && $user->status !== UserStatus::Active) {
            throw new AccessDeniedHttpException;
        }

        $token = $request->cookie(self::$cartCookie);
        $token = is_string($token) && preg_match('/^[A-Za-z0-9]{64}$/', $token) === 1 ? $token : null;

        if ($user !== null) {
            return CartOwner::user($user, $token);
        }

        return CartOwner::guest($token ?? ($issueToken ? Str::random(64) : null));
    }

    protected function withCartCookie(JsonResponse $response, Request $request, CartOwner $owner): JsonResponse
    {
        if ($owner->isGuest() && $owner->guestToken !== null && $request->cookie(self::$cartCookie) !== $owner->guestToken) {
            $response->withCookie(cookie(self::$cartCookie, $owner->guestToken, self::$cartCookieMinutes, secure: $request->isSecure(), httpOnly: true, sameSite: 'lax'));
        }

        return $response;
    }

    protected function displayCurrency(Request $request): string
    {
        $cookie = $request->cookie('currency');

        return app(CurrencyConverter::class)->resolveDisplayCurrency(is_string($cookie) ? $cookie : null);
    }
}
