<?php

/**
 * Fixtures for the mobile authentication tests (M-A2) and the stateless storefront, checkout
 * and account tests (M-A3).
 */

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Services\DigitalCodeVault;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderItemUnitStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use Illuminate\Testing\TestResponse;
use PragmaRX\Google2FA\Google2FA;

const MOBILE_TOTP_SECRET = 'JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP';

function mobileTotp(string $secret = MOBILE_TOTP_SECRET): string
{
    return app(Google2FA::class)->getCurrentOtp($secret);
}

/**
 * Send a mobile API request the way the app does: JSON and an optional bearer token.
 * Resolved guards are dropped first so that each request authenticates from scratch, as it does
 * in production.
 *
 * @param  array<string, mixed>  $body
 */
function mobileCall(string $method, string $path, array $body = [], ?string $token = null): TestResponse
{
    app('auth')->forgetGuards();

    $headers = ['Accept' => 'application/json'];

    if ($token !== null) {
        $headers['Authorization'] = 'Bearer '.$token;
    }

    return test()->json($method, '/api/v1/mobile/'.ltrim($path, '/'), $body, $headers);
}

/**
 * Sign a customer in through the mobile login and return the token pair.
 *
 * @return array{access_token: string, refresh_token: string}
 */
function mobileSignIn(User $user, string $deviceName = 'Pixel 9'): array
{
    $tokens = mobileCall('POST', 'auth/login', ['email' => $user->email, 'password' => 'password', 'device_name' => $deviceName])
        ->assertOk()
        ->json('data.tokens');

    return ['access_token' => $tokens['access_token'], 'refresh_token' => $tokens['refresh_token']];
}

/**
 * A stateless request to any path (M-A3): JSON, no cookies or session, an optional bearer token
 * and extra headers such as `X-Cart-Token`, `X-Currency`, `X-Guest-Order-Token` or
 * `Accept-Language`.
 *
 * @param  array<string, mixed>  $body
 * @param  array<string, string>  $headers
 */
function statelessCall(string $method, string $path, array $body = [], array $headers = [], ?string $token = null): TestResponse
{
    app('auth')->forgetGuards();

    $headers = ['Accept' => 'application/json', ...$headers];

    if ($token !== null) {
        $headers['Authorization'] = 'Bearer '.$token;
    }

    return test()->json($method, '/'.ltrim($path, '/'), $body, $headers);
}

/**
 * Add a variant to a guest cart through the mobile API and return the issued cart token.
 */
function mobileGuestCart(ProductVariant $variant, int $quantity = 1, ?string $cartToken = null): string
{
    $response = statelessCall('POST', 'api/v1/mobile/storefront/cart/items', ['variant_id' => $variant->public_id, 'quantity' => $quantity], $cartToken === null ? [] : ['X-Cart-Token' => $cartToken])
        ->assertCreated();

    return $cartToken ?? $response->headers->get('X-Cart-Token');
}

/**
 * A paid guest order with one delivered unit whose code is in the vault (M-A3 reveal tests).
 *
 * @return array{0: Order, 1: OrderItemUnit}
 */
function deliveredGuestUnit(string $code = 'GIFT-MOBI-LE00-1234'): array
{
    $order = paidOrderFor(sellableVariant('9'), orderAttributes: ['user_id' => null, 'guest_email' => 'guest@example.com', 'guest_email_verified_at' => now()]);

    return [$order, deliveredUnitOf($order, $code)];
}

/**
 * Mark the order's only unit delivered with a code in the vault.
 */
function deliveredUnitOf(Order $order, string $code = 'GIFT-MOBI-LE00-1234'): OrderItemUnit
{
    $unit = OrderItemUnit::query()->where('order_id', $order->id)->sole();
    $unit->forceFill(['status' => OrderItemUnitStatus::Delivered])->save();
    app(DigitalCodeVault::class)->store(new DeliverableItem($code, '9876', 'https://redeem.example.com/x'), DigitalCodeSource::Supplier, ['order_item_unit_id' => $unit->id]);

    return $unit;
}
