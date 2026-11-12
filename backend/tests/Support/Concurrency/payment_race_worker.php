<?php

/**
 * Runs one payment path in its own PHP process and MySQL connection, for PaymentConcurrencyTest.
 * Argument 1 is a JSON payload file: mode (`webhook`: process a stored webhook; `verify`: the
 * return page's server-side status check; `intake`: receive a signed webhook), the ids it needs,
 * and `start_at` (unix time with fraction) so racing workers start together. Stripe is faked and
 * answers the re-read after a short delay, so both workers hold their reads at the same time.
 * Queued jobs are discarded (fulfilment is not under test here).
 */

use App\Domains\Orders\DTOs\OrderActor;
use App\Domains\Payments\DTOs\WebhookRequestData;
use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Services\PaymentVerifier;
use App\Domains\Payments\Services\WebhookIntake;
use App\Domains\Payments\Services\WebhookProcessor;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use Tests\Support\TestConfiguration;

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';
require $root.'/tests/Support/PaymentFixtures.php';

$payload = json_decode((string) file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);

$app = require $root.'/bootstrap/app.php';
TestConfiguration::register($app);
$app->make(Kernel::class)->bootstrap();

if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'gaming-store_testing') {
    fwrite(STDERR, "Refusing to run outside the test database.\n");
    exit(2);
}

config(['queue.connections.discard' => ['driver' => 'null'], 'queue.default' => 'discard']);

Http::preventStrayRequests();
fakeStripe(static function () {
    usleep(300_000);

    return Http::response(stripeIntent());
});

while (isset($payload['start_at']) && microtime(true) < $payload['start_at']) {
    usleep(500);
}

match ($payload['mode']) {
    'webhook' => $app->make(WebhookProcessor::class)->process((int) $payload['webhook_id']),
    'verify' => $app->make(PaymentVerifier::class)->verify(Payment::query()->findOrFail((int) $payload['payment_id']), OrderActor::system()),
    'intake' => $app->make(WebhookIntake::class)->receive('stripe', new WebhookRequestData($payload['body'], ['stripe-signature' => $payload['signature']]), '127.0.0.1'),
};

echo json_encode(['done' => true]);
