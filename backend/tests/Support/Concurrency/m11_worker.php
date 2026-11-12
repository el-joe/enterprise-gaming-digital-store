<?php

/**
 * Runs one M11 operation in its own PHP process and MySQL connection, for the wallet and coupon
 * concurrency tests. Argument 1 is a JSON payload file: `mode`, the ids it needs, and `start_at`
 * (unix time with fraction) so racing workers start together. Prints a JSON result: `ok` and,
 * on a refusal, the error `code`. Queued jobs are discarded.
 *
 * Modes: `wallet_debit` (a debit of `amount` from `user_id` with key `key`), `wallet_credit`
 * (a credit), `wallet_pay` (settle payment `payment_id` through the wallet gateway) and
 * `coupon_reserve` (reserve coupon `coupon_id` for order `order_id` inside a transaction that
 * holds the lock for `hold_ms`, as a placement would).
 */

use App\Domains\Payments\Models\Payment;
use App\Domains\Payments\Services\PaymentGatewayRegistry;
use App\Domains\Pricing\DTOs\CouponTerms;
use App\Domains\Promotions\Contracts\CheckoutCoupons;
use App\Domains\Promotions\DTOs\CouponLookup;
use App\Domains\Promotions\DTOs\ResolvedCoupon;
use App\Domains\Promotions\Enums\CouponType;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\ValueObjects\Money;
use App\Domains\Wallet\DTOs\WalletEntry;
use App\Domains\Wallet\Enums\WalletTransactionType;
use App\Domains\Wallet\Services\WalletLedger;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Tests\Support\TestConfiguration;

$root = dirname(__DIR__, 3);

require $root.'/vendor/autoload.php';

$payload = json_decode((string) file_get_contents($argv[1]), true, flags: JSON_THROW_ON_ERROR);

$app = require $root.'/bootstrap/app.php';
TestConfiguration::register($app);
$app->make(Kernel::class)->bootstrap();

if (config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'gaming-store_testing') {
    fwrite(STDERR, "Refusing to run outside the test database.\n");
    exit(2);
}

config(['queue.connections.discard' => ['driver' => 'null'], 'queue.default' => 'discard']);

while (isset($payload['start_at']) && microtime(true) < $payload['start_at']) {
    usleep(500);
}

try {
    match ($payload['mode']) {
        'wallet_debit', 'wallet_credit' => $app->make(WalletLedger::class)->post(new WalletEntry(
            userId: (int) $payload['user_id'],
            type: $payload['mode'] === 'wallet_debit' ? WalletTransactionType::DebitAdmin : WalletTransactionType::CreditRefund,
            amount: Money::of($payload['amount'], 'USD'),
            idempotencyKey: $payload['key'],
        )),
        'wallet_pay' => (static function () use ($app, $payload): void {
            $payment = Payment::query()->findOrFail((int) $payload['payment_id']);
            $app->make(PaymentGatewayRegistry::class)->forPayment($payment)->settle($payment);
        })(),
        'coupon_reserve' => DB::transaction(static function () use ($app, $payload): void {
            $lookup = new CouponLookup($payload['code'], $payload['user_id'] ?? null, $payload['email'] ?? null, Money::of('20', 'USD'), [], []);
            $coupon = new ResolvedCoupon((int) $payload['coupon_id'], $payload['code'], new CouponTerms(CouponType::Percent, '10'));
            $app->make(CheckoutCoupons::class)->reserve($coupon, $lookup, (int) $payload['order_id'], Money::of('2', 'USD'));
            usleep((int) ($payload['hold_ms'] ?? 0) * 1000);
        }),
    };

    echo json_encode(['ok' => true]);
} catch (ApplicationException $exception) {
    echo json_encode(['ok' => false, 'code' => $exception->errorCode]);
}
