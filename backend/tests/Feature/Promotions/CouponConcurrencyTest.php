<?php

/**
 * Coupon limits under real concurrency: each reservation runs in a separate PHP process with its
 * own MySQL connection and transaction (tests/Support/Concurrency/m11_worker.php), against
 * committed data, exactly as the placement transaction calls the contract.
 */

use App\Domains\Identity\Models\User;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\Order;
use App\Domains\Promotions\Enums\CouponRedemptionStatus;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Promotions\Models\Coupon;
use App\Domains\Promotions\Models\CouponRedemption;
use Database\Factories\Currency\CurrencyFactory;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

const M11_COUPON_TABLES = ['coupon_redemptions', 'coupons', 'orders', 'users', 'currencies', 'countries'];

beforeEach(function (): void {
    commitTestTransaction();
    $this->payloadDir = sys_get_temp_dir().'/m11-coupon-'.bin2hex(random_bytes(4));
    mkdir($this->payloadDir);
    CurrencyFactory::ensure('USD');
});

afterEach(function (): void {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    foreach (M11_COUPON_TABLES as $table) {
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
function startCouponWorker(array $payload, string $dir): Process
{
    $file = $dir.'/'.bin2hex(random_bytes(6)).'.json';
    file_put_contents($file, json_encode($payload, JSON_THROW_ON_ERROR));

    $process = new Process([PHP_BINARY, base_path('tests/Support/Concurrency/m11_worker.php'), $file], base_path(), [
        'APP_ENV' => 'testing', 'DB_CONNECTION' => 'mysql', 'DB_DATABASE' => 'gaming-store_testing', 'DB_URL' => '',
        'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array', 'MAIL_MAILER' => 'array',
    ], null, 60);
    $process->start();

    return $process;
}

/**
 * @return list<array{ok: bool, code?: string}>
 */
function finishCouponWorkers(Process ...$workers): array
{
    return array_map(static function (Process $worker): array {
        $worker->wait();
        $output = $worker->getOutput();

        if (! $worker->isSuccessful()) {
            throw new RuntimeException('Worker failed: '.$worker->getErrorOutput().$output);
        }

        return json_decode($output, true) ?? throw new RuntimeException('Worker output: '.substr($output, 0, 2000));
    }, $workers);
}

it('lets only one of two simultaneous orders take the last use of a coupon', function (int $round): void {
    $coupon = Coupon::factory()->create(['code' => "LAST{$round}", 'type' => CouponType::Fixed, 'value' => '2', 'usage_limit' => 1, 'usage_limit_per_user' => null]);
    $orders = Order::factory()->count(2)->create(['status' => OrderStatus::PendingPayment]);
    $startAt = microtime(true) + 1.5;

    $results = finishCouponWorkers(...$orders->map(fn (Order $order): Process => startCouponWorker([
        'mode' => 'coupon_reserve', 'coupon_id' => $coupon->id, 'code' => $coupon->code, 'order_id' => $order->id,
        'email' => "buyer{$order->id}@example.test", 'hold_ms' => 300, 'start_at' => $startAt,
    ], $this->payloadDir))->all());

    expect(collect($results)->where('ok', true)->count())->toBe(1)
        ->and(collect($results)->firstWhere('ok', false)['code'])->toBe('coupon_invalid')
        ->and($coupon->fresh()->used_count)->toBe(1)
        ->and(CouponRedemption::query()->where('coupon_id', $coupon->id)->where('status', CouponRedemptionStatus::Reserved->value)->count())->toBe(1);
})->with([1, 2, 3]);

it('lets one customer take a once-per-customer coupon only once across simultaneous orders', function (int $round): void {
    $user = User::factory()->create();
    $coupon = Coupon::factory()->create(['code' => "ONCE{$round}", 'type' => CouponType::Fixed, 'value' => '2', 'usage_limit' => null, 'usage_limit_per_user' => 1]);
    $orders = Order::factory()->count(2)->create(['status' => OrderStatus::PendingPayment, 'user_id' => $user->id]);
    $startAt = microtime(true) + 1.5;

    $results = finishCouponWorkers(...$orders->map(fn (Order $order): Process => startCouponWorker([
        'mode' => 'coupon_reserve', 'coupon_id' => $coupon->id, 'code' => $coupon->code, 'order_id' => $order->id,
        'user_id' => $user->id, 'email' => $user->email, 'hold_ms' => 300, 'start_at' => $startAt,
    ], $this->payloadDir))->all());

    expect(collect($results)->where('ok', true)->count())->toBe(1)
        ->and($coupon->fresh()->used_count)->toBe(1)
        ->and(CouponRedemption::query()->where('coupon_id', $coupon->id)->count())->toBe(1);
})->with([1, 2, 3]);

it('grants exactly two uses of a limit of two when three buyers race', function (): void {
    $coupon = Coupon::factory()->create(['code' => 'TWO', 'type' => CouponType::Fixed, 'value' => '2', 'usage_limit' => 2, 'usage_limit_per_user' => null]);
    $orders = Order::factory()->count(3)->create(['status' => OrderStatus::PendingPayment]);
    $startAt = microtime(true) + 1.5;

    $results = finishCouponWorkers(...$orders->map(fn (Order $order): Process => startCouponWorker([
        'mode' => 'coupon_reserve', 'coupon_id' => $coupon->id, 'code' => 'TWO', 'order_id' => $order->id,
        'email' => "buyer{$order->id}@example.test", 'hold_ms' => 200, 'start_at' => $startAt,
    ], $this->payloadDir))->all());

    expect(collect($results)->where('ok', true)->count())->toBe(2)
        ->and($coupon->fresh()->used_count)->toBe(2);
});
