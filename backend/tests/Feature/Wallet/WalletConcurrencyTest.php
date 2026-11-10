<?php

/**
 * Real concurrency on the wallet ledger: each worker is a separate PHP process with its own MySQL
 * connection (tests/Support/Concurrency/m11_worker.php), against committed data.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Models\Payment;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Models\WalletTransaction;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

const M11_WALLET_TABLES = [
    'analytics_event_dispatches', 'payment_transactions', 'order_events', 'order_item_units', 'order_items', 'risk_assessments',
    'wallet_transactions', 'customer_wallets', 'payments', 'orders', 'payment_eligibility_rules', 'payment_gateway_currencies',
    'payment_gateway_credentials', 'payment_gateways', 'product_variants', 'category_product', 'products', 'gaming_platforms',
    'categories', 'audit_logs', 'users', 'currencies', 'countries',
];

beforeEach(function (): void {
    commitTestTransaction();
    $this->payloadDir = sys_get_temp_dir().'/m11-wallet-'.bin2hex(random_bytes(4));
    mkdir($this->payloadDir);
    CurrencyFactory::ensure('USD');
    $this->user = User::factory()->create();
});

afterEach(function (): void {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    foreach (M11_WALLET_TABLES as $table) {
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
function startM11Worker(array $payload, string $dir): Process
{
    $file = $dir.'/'.bin2hex(random_bytes(6)).'.json';
    file_put_contents($file, json_encode($payload, JSON_THROW_ON_ERROR));

    $process = new Process([PHP_BINARY, base_path('tests/Support/Concurrency/m11_worker.php'), $file], base_path(), [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'mysql',
        'DB_DATABASE' => 'gaming-store_testing',
        'DB_URL' => '',
        'CACHE_STORE' => 'array',
        'SESSION_DRIVER' => 'array',
        'MAIL_MAILER' => 'array',
    ], null, 60);
    $process->start();

    return $process;
}

/**
 * @return list<array{ok: bool, code?: string}>
 */
function finishM11Workers(Process ...$workers): array
{
    return array_map(static function (Process $worker): array {
        $worker->wait();

        if (! $worker->isSuccessful()) {
            throw new RuntimeException('Worker failed: '.$worker->getErrorOutput().$worker->getOutput());
        }

        $output = $worker->getOutput();

        return json_decode($output, true) ?? throw new RuntimeException('Worker output: '.substr($output, 0, 2000));
    }, $workers);
}

it('never overdraws when two debits race for a balance that covers one', function (int $round): void {
    fundWallet($this->user, '10');
    $startAt = microtime(true) + 1.5;

    $results = finishM11Workers(
        startM11Worker(['mode' => 'wallet_debit', 'user_id' => $this->user->id, 'amount' => '7', 'key' => "race-a-{$round}", 'start_at' => $startAt], $this->payloadDir),
        startM11Worker(['mode' => 'wallet_debit', 'user_id' => $this->user->id, 'amount' => '7', 'key' => "race-b-{$round}", 'start_at' => $startAt], $this->payloadDir),
    );

    expect(collect($results)->where('ok', true)->count())->toBe(1)
        ->and(collect($results)->firstWhere('ok', false)['code'])->toBe('wallet_insufficient_balance')
        ->and(walletTotals($this->user))->toBe(['balance' => '3.000000', 'sum' => '3.000000'])
        ->and(WalletTransaction::query()->where('type', WalletTransactionType::DebitAdmin)->count())->toBe(1);
})->with([1, 2, 3]);

it('posts one entry when the same credit is sent twice at the same moment', function (): void {
    $startAt = microtime(true) + 1.5;

    $results = finishM11Workers(
        startM11Worker(['mode' => 'wallet_credit', 'user_id' => $this->user->id, 'amount' => '12.5', 'key' => 'refund:same', 'start_at' => $startAt], $this->payloadDir),
        startM11Worker(['mode' => 'wallet_credit', 'user_id' => $this->user->id, 'amount' => '12.5', 'key' => 'refund:same', 'start_at' => $startAt], $this->payloadDir),
    );

    expect(collect($results)->every(fn (array $result): bool => $result['ok']))->toBeTrue()
        ->and(WalletTransaction::query()->count())->toBe(1)
        ->and(walletTotals($this->user))->toBe(['balance' => '12.500000', 'sum' => '12.500000']);
});

it('pays exactly one of two orders racing for a balance that covers one', function (int $round): void {
    fundWallet($this->user, '15');
    $gateway = walletGateway();
    [$first, $firstPayment] = orderAwaitingPayment($gateway, '10.00', providerPaymentId: null, orderAttributes: ['user_id' => $this->user->id]);
    [$second, $secondPayment] = orderAwaitingPayment($gateway, '10.00', providerPaymentId: null, orderAttributes: ['user_id' => $this->user->id]);
    Payment::query()->whereKey([$firstPayment->id, $secondPayment->id])->update(['status' => PaymentStatus::Created->value]);
    $startAt = microtime(true) + 1.5;

    $results = finishM11Workers(
        startM11Worker(['mode' => 'wallet_pay', 'payment_id' => $firstPayment->id, 'start_at' => $startAt], $this->payloadDir),
        startM11Worker(['mode' => 'wallet_pay', 'payment_id' => $secondPayment->id, 'start_at' => $startAt], $this->payloadDir),
    );

    $statuses = [$first->fresh()->status, $second->fresh()->status];

    expect(collect($results)->where('ok', true)->count())->toBe(1)
        ->and(collect($statuses)->filter(fn (OrderStatus $status): bool => $status === OrderStatus::Paid)->count())->toBe(1)
        ->and(collect($statuses)->filter(fn (OrderStatus $status): bool => $status === OrderStatus::PendingPayment)->count())->toBe(1)
        ->and(Payment::query()->where('status', PaymentStatus::Failed->value)->count())->toBe(1)
        ->and(walletTotals($this->user))->toBe(['balance' => '5.000000', 'sum' => '5.000000']);
})->with([1, 2, 3]);
