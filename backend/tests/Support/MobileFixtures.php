<?php

/**
 * Fixtures for the mobile authentication tests (M-A2).
 */

use App\Domains\Identity\Models\User;
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
