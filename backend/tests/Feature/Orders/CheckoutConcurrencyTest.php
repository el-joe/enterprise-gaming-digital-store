<?php

use App\Domains\Catalog\Models\ProductVariant;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\CartStatus;
use App\Domains\Orders\Models\Cart;
use App\Domains\Orders\Models\CartItem;
use App\Domains\Orders\Models\IdempotencyKey;
use App\Domains\Orders\Models\Order;
use App\Domains\Payments\Models\PaymentGateway;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

/**
 * Real concurrency: each request runs in a separate PHP process with its own MySQL connection
 * against committed data, so row locks, unique indexes and the idempotency claim are exercised
 * for real rather than simulated in one connection.
 */
const WORKER_TABLES = [
    'order_item_delivery_values', 'order_item_units', 'order_events', 'order_items', 'orders', 'idempotency_keys',
    'cart_items', 'carts', 'product_variants', 'category_product', 'products', 'gaming_platforms', 'categories',
    'payment_gateways', 'users', 'currencies', 'countries',
];

beforeEach(function (): void {
    commitTestTransaction();
    config(['database.connections.mysql_second' => config('database.connections.mysql')]);

    $this->gateway = PaymentGateway::factory()->create(['key' => 'fakepay']);
    $this->variant = sellableVariant('10');
    $this->payloadDir = sys_get_temp_dir().'/m7-workers-'.bin2hex(random_bytes(4));
    mkdir($this->payloadDir);
});

afterEach(function (): void {
    DB::purge('mysql_second');

    foreach (WORKER_TABLES as $table) {
        DB::table($table)->delete();
    }

    array_map('unlink', glob($this->payloadDir.'/*') ?: []);
    rmdir($this->payloadDir);

    reopenTestTransaction();
});

/**
 * @param  array<string, mixed>  $payload
 */
function startWorker(array $payload): Process
{
    $file = test()->payloadDir.'/'.bin2hex(random_bytes(6)).'.json';
    file_put_contents($file, json_encode(['gateway_id' => test()->gateway->id, ...$payload], JSON_THROW_ON_ERROR));

    $process = new Process([PHP_BINARY, base_path('tests/Support/Concurrency/place_order_worker.php'), $file], base_path(), [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => 'gaming-store_testing',
        'DB_URL' => '',
        'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'MAIL_MAILER' => 'array',
        'BCRYPT_ROUNDS' => '4',
    ], null, 60);
    $process->start();

    return $process;
}

/**
 * @return array{status: int, replayed: bool, body: array<string, mixed>}|null null when the process was killed
 */
function workerResult(Process $process): ?array
{
    try {
        $process->wait();
    } catch (ProcessSignaledException) {
        return null;
    }

    $output = trim($process->getOutput());

    if ($output === '') {
        return null;
    }

    return json_decode($output, true, flags: JSON_THROW_ON_ERROR);
}

function committedCustomerCart(ProductVariant $variant): User
{
    $user = User::factory()->create();
    $cart = (new Cart)->forceFill(['user_id' => $user->id, 'display_currency' => 'USD', 'status' => CartStatus::Active, 'last_activity_at' => now()]);
    $cart->save();
    (new CartItem)->forceFill(['cart_id' => $cart->id, 'product_variant_id' => $variant->id, 'quantity' => 1])->save();

    return $user;
}

function waitForClaim(string $key): void
{
    $deadline = microtime(true) + 20;

    while (! IdempotencyKey::query()->where('key', $key)->exists()) {
        if (microtime(true) > $deadline) {
            throw new RuntimeException('The worker never claimed its key.');
        }

        usleep(20000);
    }
}

it('handles all three Idempotency-Key cases across processes while the first request holds the cart', function (): void {
    $user = committedCustomerCart($this->variant);
    $cartId = Cart::query()->sole()->id;
    $body = placeOrderBody('10.00');

    $second = DB::connection('mysql_second');
    $second->beginTransaction();
    $second->select('select id from carts where id = ? for update', [$cartId]);

    $first = startWorker(['key' => 'shared-key-01', 'user_id' => $user->id, 'body' => $body]);
    waitForClaim('shared-key-01');

    $inFlight = workerResult(startWorker(['key' => 'shared-key-01', 'user_id' => $user->id, 'body' => $body]));
    $mismatch = workerResult(startWorker(['key' => 'shared-key-01', 'user_id' => $user->id, 'body' => [...$body, 'consents' => ['terms' => true, 'marketing' => true]]]));

    $second->rollBack();
    $created = workerResult($first);
    $replay = workerResult(startWorker(['key' => 'shared-key-01', 'user_id' => $user->id, 'body' => $body]));

    expect($inFlight['status'])->toBe(409)->and($inFlight['body']['code'])->toBe('idempotency_in_progress')
        ->and($mismatch['status'])->toBe(409)->and($mismatch['body']['code'])->toBe('idempotency_mismatch')
        ->and($created['status'])->toBe(201)
        ->and($replay['status'])->toBe(201)->and($replay['replayed'])->toBeTrue()
        ->and($replay['body'])->toEqual($created['body'])
        ->and(Order::query()->count())->toBe(1);
});

it('creates one order when two processes submit the same cart at the same moment with different keys', function (): void {
    foreach (range(1, 3) as $round) {
        $user = committedCustomerCart($this->variant);
        $startAt = microtime(true) + 1.5;

        $workers = [
            startWorker(['key' => "race-a-{$round}-0001", 'user_id' => $user->id, 'body' => placeOrderBody('10.00'), 'start_at' => $startAt]),
            startWorker(['key' => "race-b-{$round}-0001", 'user_id' => $user->id, 'body' => placeOrderBody('10.00'), 'start_at' => $startAt]),
        ];

        $statuses = array_map(static fn (Process $worker): int => workerResult($worker)['status'], $workers);
        sort($statuses);

        expect(Order::query()->where('user_id', $user->id)->count())->toBe(1, "round {$round}")
            ->and($statuses[1])->toBe(201)
            ->and($statuses[0])->toBeIn([200, 422]);
    }
});

it('creates one order when two processes race with the same key', function (): void {
    $user = committedCustomerCart($this->variant);
    $startAt = microtime(true) + 1.5;

    $results = array_map('workerResult', [
        startWorker(['key' => 'same-key-0001', 'user_id' => $user->id, 'body' => placeOrderBody('10.00'), 'start_at' => $startAt]),
        startWorker(['key' => 'same-key-0001', 'user_id' => $user->id, 'body' => placeOrderBody('10.00'), 'start_at' => $startAt]),
    ]);

    $created = array_values(array_filter($results, static fn (array $result): bool => $result['status'] === 201 && ! $result['replayed']));
    $other = array_values(array_filter($results, static fn (array $result): bool => ! ($result['status'] === 201 && ! $result['replayed'])));

    expect($created)->toHaveCount(1)
        ->and($other[0]['status'] === 409 && $other[0]['body']['code'] === 'idempotency_in_progress' || $other[0]['replayed'])->toBeTrue()
        ->and(Order::query()->count())->toBe(1);
});

it('lets the unique placement index stop two guest carts with the same email and contents', function (): void {
    $tokens = [str_repeat('a', 64), str_repeat('b', 64)];

    foreach ($tokens as $token) {
        $cart = (new Cart)->forceFill(['session_token' => hash('sha256', $token), 'display_currency' => 'USD', 'status' => CartStatus::Active, 'last_activity_at' => now()]);
        $cart->save();
        (new CartItem)->forceFill(['cart_id' => $cart->id, 'product_variant_id' => $this->variant->id, 'quantity' => 1])->save();
    }

    $startAt = microtime(true) + 1.5;
    $body = placeOrderBody('10.00', ['email' => 'same@example.com']);

    $statuses = array_map(static fn (Process $worker): int => workerResult($worker)['status'], [
        startWorker(['key' => 'guest-a-0001', 'guest_token' => $tokens[0], 'body' => $body, 'start_at' => $startAt]),
        startWorker(['key' => 'guest-b-0001', 'guest_token' => $tokens[1], 'body' => $body, 'start_at' => $startAt]),
    ]);
    sort($statuses);

    expect($statuses)->toBe([200, 201])
        ->and(Order::query()->count())->toBe(1);
});

it('leaves no partial order when a process dies mid-transaction, and the key can be retried once stale', function (): void {
    $user = committedCustomerCart($this->variant);

    $crashed = workerResult(startWorker(['key' => 'crash-key-001', 'user_id' => $user->id, 'body' => placeOrderBody('10.00'), 'crash_after_units' => true]));

    expect($crashed)->toBeNull();

    foreach (['orders', 'order_items', 'order_item_units', 'order_events'] as $table) {
        expect(DB::table($table)->count())->toBe(0, $table);
    }

    expect(IdempotencyKey::query()->sole())->response_status->toBeNull()->locked_at->not->toBeNull()
        ->and(Cart::query()->sole()->status)->toBe(CartStatus::Active);

    $retry = workerResult(startWorker(['key' => 'crash-key-001', 'user_id' => $user->id, 'body' => placeOrderBody('10.00')]));
    expect($retry['status'])->toBe(409)->and($retry['body']['code'])->toBe('idempotency_in_progress');

    IdempotencyKey::query()->update(['locked_at' => now()->subMinutes(5)]);

    $afterStale = workerResult(startWorker(['key' => 'crash-key-001', 'user_id' => $user->id, 'body' => placeOrderBody('10.00')]));

    expect($afterStale['status'])->toBe(201)
        ->and(Order::query()->count())->toBe(1);
});
