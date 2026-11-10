<?php

/**
 * Fixtures for the account routes of M-A5, which run for a web session and a mobile bearer
 * token alike (routes/account.php is registered under /api/v1/account and
 * /api/v1/mobile/account).
 */

use App\Domains\Identity\Models\User;
use Illuminate\Testing\TestResponse;

/**
 * The two clients of every account route.
 *
 * @return array<string, array{0: string}>
 */
function accountClients(): array
{
    return ['web session' => ['web'], 'mobile bearer' => ['mobile']];
}

/**
 * The mobile access token of this user in the running test (signed in once through the mobile
 * login, kept in the test's application container), so a test can switch between users.
 */
function accountMobileToken(User $user, string $deviceName = 'Pixel 9'): string
{
    $tokens = app()->bound('tests.account_mobile_tokens') ? app('tests.account_mobile_tokens') : new ArrayObject;
    app()->instance('tests.account_mobile_tokens', $tokens);
    $key = $user->id.'|'.$deviceName;

    return $tokens[$key] ??= mobileSignIn($user, $deviceName)['access_token'];
}

/**
 * Call an account route as this user through the given client: a signed-in web session, or the
 * stateless mobile twin with a bearer token.
 *
 * @param  array<string, mixed>  $body
 */
function accountCall(string $client, User $user, string $method, string $path, array $body = [], array $headers = []): TestResponse
{
    $path = ltrim($path, '/');

    if ($client === 'web') {
        app('auth')->forgetGuards();

        return test()->actingAs($user)->json($method, '/api/v1/account/'.$path, $body, ['Accept' => 'application/json', ...$headers]);
    }

    return statelessCall($method, 'api/v1/mobile/account/'.$path, $body, $headers, accountMobileToken($user));
}
