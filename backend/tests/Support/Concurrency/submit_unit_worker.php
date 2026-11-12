<?php

/**
 * Runs the supplier submission gate for one unit in its own PHP process and database
 * connection, for SupplierConcurrencyTest. Argument 1 is a JSON payload file:
 *   unit_id, start_at (optional unix time with fraction),
 *   mode: `submit` (whole gate), `crash_after_claim` (SIGKILL after step 1),
 *   `crash_after_send` (SIGKILL after the supplier accepted, before step 3).
 * Reloadly is faked; the fake answers POST /orders after a short delay so racing workers overlap.
 */

use App\Domains\Fulfillment\Services\SupplierSubmissionGate;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Services\SupplierAdapterRegistry;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Http;
use Tests\Support\TestConfiguration;

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';
require $root.'/tests/Support/FulfilmentFixtures.php';

$payload = json_decode((string) file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);

$app = require $root.'/bootstrap/app.php';
TestConfiguration::register($app);
$app->make(Kernel::class)->bootstrap();

if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'gaming-store_testing') {
    fwrite(STDERR, "Refusing to run outside the test database.\n");
    exit(2);
}

Http::preventStrayRequests();
fakeReloadly(static function () {
    usleep(300_000);

    return Http::response(reloadlyTransaction('SUCCESSFUL', (string) random_int(10_000, 99_999_999)));
});

while (isset($payload['start_at']) && microtime(true) < $payload['start_at']) {
    usleep(500);
}

$gate = $app->make(SupplierSubmissionGate::class);
$mode = $payload['mode'] ?? 'submit';

if ($mode === 'submit') {
    $gate->submit((int) $payload['unit_id']);
    echo json_encode(['done' => true]);

    exit(0);
}

$claimed = $gate->claim((int) $payload['unit_id']);

if ($claimed !== null && $mode === 'crash_after_send') {
    [$transaction, $request] = $claimed;
    $app->make(SupplierAdapterRegistry::class)->for(Supplier::query()->findOrFail($transaction->supplier_id))->createOrder($request);
}

posix_kill(getmypid(), SIGKILL);
