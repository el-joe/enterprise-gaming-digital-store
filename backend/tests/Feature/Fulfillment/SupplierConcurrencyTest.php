<?php

/**
 * Real concurrency for the exactly-once gate: each worker is a separate PHP process with its own
 * MySQL connection, against committed data (mocked Reloadly; awaiting sandbox verification).
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Fulfillment\Jobs\SubmitUnitToSupplier;
use App\Domains\Fulfillment\Services\StaleSubmissionSweeper;
use App\Domains\Fulfillment\Services\SupplierVerificationService;
use App\Domains\Orders\Enums\OrderItemUnitStatus as Unit;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Domains\Suppliers\Enums\SupplierTransactionStatus as Status;
use App\Domains\Suppliers\Models\SupplierApiLog;
use App\Domains\Suppliers\Models\SupplierTransaction;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Process;

const GATE_WORKER_TABLES = [
    'digital_code_accesses', 'digital_codes', 'supplier_api_logs', 'supplier_transactions', 'manual_fulfillment_tasks', 'order_events',
    'order_item_units', 'order_items', 'risk_assessments', 'payments', 'orders', 'supplier_product_prices', 'supplier_product_mappings',
    'supplier_products', 'supplier_credentials', 'suppliers', 'product_variants', 'category_product', 'products', 'gaming_platforms',
    'categories', 'payment_gateways', 'audit_logs', 'users', 'currencies', 'countries',
];

beforeEach(function (): void {
    commitTestTransaction();
    config(['database.connections.mysql_second' => config('database.connections.mysql')]);

    $this->payloadDir = sys_get_temp_dir().'/m8-workers-'.bin2hex(random_bytes(4));
    mkdir($this->payloadDir);

    $variant = sellableVariant('9');
    $this->supplier = reloadlySupplier();
    mapToSupplier($variant, $this->supplier);

    Queue::fake([SubmitUnitToSupplier::class]);
    $this->order = paidOrderFor($variant);
    FulfilOrder::dispatch($this->order->id);
    $this->unit = OrderItemUnit::query()->sole();
});

afterEach(function (): void {
    DB::purge('mysql_second');
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    foreach (GATE_WORKER_TABLES as $table) {
        DB::table($table)->delete();
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1');
    array_map('unlink', glob($this->payloadDir.'/*') ?: []);
    rmdir($this->payloadDir);

    reopenTestTransaction();
});

/**
 * @param  array<string, mixed>  $payload
 */
function startGateWorker(array $payload): Process
{
    $file = test()->payloadDir.'/'.bin2hex(random_bytes(6)).'.json';
    file_put_contents($file, json_encode($payload, JSON_THROW_ON_ERROR));

    $process = new Process([PHP_BINARY, base_path('tests/Support/Concurrency/submit_unit_worker.php'), $file], base_path(), [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => 'gaming-store_testing',
        'DB_URL' => '',
        'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array',
        'QUEUE_CONNECTION' => 'sync',
        'MAIL_MAILER' => 'array',
        'DIGITAL_CODE_KEYS' => (string) config('fulfillment.code_keys'),
        'DIGITAL_CODE_ACTIVE_KID' => (string) config('fulfillment.active_kid'),
        'DIGITAL_CODE_BLIND_INDEX_KEY' => (string) config('fulfillment.blind_index_key'),
    ], null, 60);
    $process->start();

    return $process;
}

function waitForWorker(Process $process): bool
{
    try {
        $process->wait();
    } catch (ProcessSignaledException) {
        return false;
    }

    if (! $process->isSuccessful()) {
        throw new RuntimeException('Worker failed: '.$process->getErrorOutput().$process->getOutput());
    }

    return true;
}

it('has the unit queued and committed before the race', function (): void {
    expect($this->unit->status)->toBe(Unit::Queued)->and($this->order->fresh()->status)->toBe(OrderStatus::Processing);
});

it('lets exactly one of two simultaneous workers submit the unit', function (int $round): void {
    $startAt = microtime(true) + 1.5;
    $workers = [startGateWorker(['unit_id' => $this->unit->id, 'start_at' => $startAt]), startGateWorker(['unit_id' => $this->unit->id, 'start_at' => $startAt])];

    foreach ($workers as $worker) {
        waitForWorker($worker);
    }

    expect(SupplierTransaction::query()->count())->toBe(1)
        ->and(SupplierApiLog::query()->where('operation', 'orders.create')->count())->toBe(1)
        ->and(SupplierTransaction::query()->sole()->status)->toBe(Status::Succeeded)
        ->and($this->unit->fresh()->status)->toBe(Unit::Delivered)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Completed);
})->with([1, 2, 3]);

it('refuses a second active attempt from another connection while the first is active', function (): void {
    $first = (new SupplierTransaction)->forceFill([
        'order_item_unit_id' => $this->unit->id, 'supplier_id' => $this->supplier->id, 'attempt_no' => 1,
        'client_reference' => 'u'.$this->unit->public_id.'s'.$this->supplier->id, 'status' => Status::Submitting,
        'requested_amount' => '10', 'requested_currency' => 'USD', 'submitted_at' => now(),
    ]);
    $first->save();

    expect(fn () => DB::connection('mysql_second')->table('supplier_transactions')->insert([
        'public_id' => (string) Str::ulid(), 'order_item_unit_id' => $this->unit->id, 'supplier_id' => $this->supplier->id, 'attempt_no' => 2,
        'client_reference' => 'u'.$this->unit->public_id.'s'.$this->supplier->id, 'status' => 'accepted', 'requested_amount' => '10',
        'requested_currency' => 'USD', 'code_retrieval_status' => 'not_applicable', 'created_at' => now(), 'updated_at' => now(),
    ]))->toThrow(UniqueConstraintViolationException::class);
});

it('recovers a worker killed after step 1: no supplier call, pending verification, never resubmitted', function (): void {
    expect(waitForWorker(startGateWorker(['unit_id' => $this->unit->id, 'mode' => 'crash_after_claim'])))->toBeFalse();

    $transaction = SupplierTransaction::query()->sole();
    expect($transaction->status)->toBe(Status::Submitting)
        ->and(SupplierApiLog::query()->where('operation', 'orders.create')->count())->toBe(0);

    $this->travel(2)->minutes();
    fakeReloadly();
    app(StaleSubmissionSweeper::class)->sweep();

    expect($transaction->fresh()->status)->toBe(Status::PendingVerification)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::PendingVerification);

    app(SupplierVerificationService::class)->verify($transaction->id, force: true);

    expect($transaction->fresh()->status)->toBe(Status::PendingVerification)
        ->and(reloadlyOrderPosts())->toBeEmpty();
});

it('recovers a worker killed after the supplier accepted: the lookup settles it, one order only', function (): void {
    expect(waitForWorker(startGateWorker(['unit_id' => $this->unit->id, 'mode' => 'crash_after_send'])))->toBeFalse();

    $transaction = SupplierTransaction::query()->sole();
    expect($transaction->status)->toBe(Status::Submitting)
        ->and(SupplierApiLog::query()->where('operation', 'orders.create')->count())->toBe(1);

    $this->travel(2)->minutes();
    fakeReloadly(overrides: [RELOADLY_API.'/reports/transactions*' => Http::response(['content' => [reloadlyTransaction('SUCCESSFUL', '31337', $transaction->client_reference)]])]);
    app(StaleSubmissionSweeper::class)->sweep();
    app(SupplierVerificationService::class)->verify($transaction->id);

    expect($transaction->fresh()->status)->toBe(Status::Succeeded)
        ->and($this->order->fresh()->status)->toBe(OrderStatus::Completed)
        ->and(reloadlyOrderPosts())->toBeEmpty()
        ->and(SupplierApiLog::query()->where('operation', 'orders.create')->count())->toBe(1);
});

it('makes a worker wait on the unit row lock held by another connection', function (): void {
    $second = DB::connection('mysql_second');
    $second->beginTransaction();
    $second->table('order_item_units')->where('id', $this->unit->id)->lockForUpdate()->first();

    $worker = startGateWorker(['unit_id' => $this->unit->id]);
    usleep(1_500_000);

    expect($worker->isRunning())->toBeTrue()
        ->and(SupplierTransaction::query()->count())->toBe(0);

    $second->rollBack();
    waitForWorker($worker);

    expect(SupplierTransaction::query()->count())->toBe(1);
});
