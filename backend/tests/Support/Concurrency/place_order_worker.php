<?php

/**
 * Runs one POST /api/v1/checkout/place-order in its own PHP process, with its own database
 * connection, for the concurrency tests. Argument 1 is a JSON payload file:
 *   key, body, gateway_id, user_id | guest_token, start_at (optional unix time with fraction),
 *   crash_after_units (optional: SIGKILL the process mid-transaction), guest_checkout (optional:
 *   the storefront.guest_checkout override).
 * Prints {"status": int, "replayed": bool, "body": object}.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Payments\Contracts\PaymentCurrencyResolver;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\Support\FakePaymentCurrencyResolver;
use Tests\Support\TestConfiguration;

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';

$payload = json_decode((string) file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);

$app = require $root.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
TestConfiguration::register($app);
$kernel->bootstrap();

if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'gaming-store_testing') {
    fwrite(STDERR, "Refusing to run outside the test database.\n");
    exit(2);
}

// Guest checkout is the admin setting checkout.guest_checkout (applied to this config key).
if ($payload['guest_checkout'] ?? false) {
    config(['storefront.guest_checkout' => true]);
}

Http::preventStrayRequests();
$app->instance(PaymentCurrencyResolver::class, new FakePaymentCurrencyResolver((int) $payload['gateway_id']));

if ($payload['crash_after_units'] ?? false) {
    OrderItemUnit::created(static function (): void {
        posix_kill(getmypid(), SIGKILL);
    });
}

$cookies = [];

if (isset($payload['guest_token'])) {
    $encrypter = $app['encrypter'];
    $cookies['cart_token'] = $encrypter->encrypt(CookieValuePrefix::create('cart_token', $encrypter->getKey()).$payload['guest_token'], false);
}

$request = Request::create('/api/v1/checkout/place-order', 'POST', [], $cookies, [], [
    'HTTP_ACCEPT' => 'application/json',
    'CONTENT_TYPE' => 'application/json',
    'HTTP_IDEMPOTENCY_KEY' => $payload['key'],
], json_encode($payload['body'], JSON_THROW_ON_ERROR));

$app->instance('request', $request);

if (isset($payload['user_id'])) {
    $app['auth']->guard('web')->setUser(User::query()->findOrFail($payload['user_id']));
}

while (isset($payload['start_at']) && microtime(true) < $payload['start_at']) {
    usleep(500);
}

$response = $kernel->handle($request);

echo json_encode([
    'status' => $response->getStatusCode(),
    'replayed' => $response->headers->get('Idempotency-Replayed') === 'true',
    'body' => json_decode((string) $response->getContent(), true),
], JSON_THROW_ON_ERROR);

$kernel->terminate($request, $response);
