<?php

namespace App\Http\Controllers\Storefront\Concerns;

use App\Domains\Orders\Services\GuestOrderTokenService;
use App\Domains\Orders\Services\OrderStatusReader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Guest order access of a request (ADR-036): the browser session's order list, or for a
 * stateless client the `X-Guest-Order-Token` header, which opens only the order it was issued
 * for.
 */
trait InteractsWithGuestOrderToken
{
    protected static string $guestOrderTokenHeader = 'X-Guest-Order-Token';

    /**
     * The guest orders this request may see, in the shape `OrderStatusReader` takes.
     *
     * @return list<string>
     */
    protected function guestOrderIds(Request $request, string $orderPublicId): array
    {
        if ($request->hasSession()) {
            return $request->session()->get(OrderStatusReader::SESSION_KEY, []);
        }

        return app(GuestOrderTokenService::class)->opensOrder($orderPublicId, $this->presentedGuestOrderToken($request)) ? [$orderPublicId] : [];
    }

    protected function presentedGuestOrderToken(Request $request): ?string
    {
        $token = $request->headers->get(self::$guestOrderTokenHeader);

        return is_string($token) && $token !== '' ? $token : null;
    }

    /**
     * A response carrying a token is never cached.
     */
    protected function withoutCaching(JsonResponse $response): JsonResponse
    {
        return $response->header('Cache-Control', 'no-store, private');
    }
}
