<?php

/**
 * Real concurrency on payment state: each worker is a separate PHP process with its own MySQL
 * connection, against committed data. Stripe mocked; awaiting sandbox verification.
 */

use App\Domains\Analytics\Models\AnalyticsEventDispatch;
use App\Domains\Orders\Enums\OrderStatus;
use App\Domains\Orders\Models\OrderEvent;
use App\Domains\Payments\Enums\PaymentStatus;
use App\Domains\Payments\Enums\WebhookStatus;
use App\Domains\Payments\Models\PaymentTransaction;
use App\Domains\Payments\Models\PaymentWebhook;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

const PAYMENT_RACE_TABLES = [
    'order_item_financials', 'analytics_event_dispatches', 'payment_transactions', 'payment_webhooks', 'refund_units', 'refunds', 'security_events', 'order_events',
    'order_item_units', 'order_items', 'risk_assessments', 'payments', 'orders', 'payment_eligibility_rules', 'payment_methods',
    'payment_gateway_currencies', 'payment_gateway_credentials', 'payment_gateways', 'product_variants', 'category_product', 'products',
    'gaming_platforms', 'categories', 'audit_logs', 'users', 'currencies', 'countries',
];

beforeEach(function (): void {
    commitTestTransaction();

    $this->payloadDir = sys_get_temp_dir().'/m10-workers-'.bin2hex(random_bytes(4));
    mkdir($this->payloadDir);

    $this->gateway = gatewayFor('stripe');
    [$this->order, $this->payment] = orderAwaitingPayment($this->gateway);
});

afterEach(function (): void {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');

    foreach (PAYMENT_RACE_TABLES as $table) {
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
function startPaymentWorker(array $payload): Process
{
    $file = test()->payloadDir.'/'.bin2hex(random_bytes(6)).'.json';
    file_put_contents($file, json_encode($payload, JSON_THROW_ON_ERROR));

    $process = new Process([PHP_BINARY, base_path('tests/Support/Concurrency/payment_race_worker.php'), $file], base_path(), [
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

function finishPaymentWorkers(Process ...$workers): void
{
    foreach ($workers as $worker) {
        $worker->wait();

        if (! $worker->isSuccessful()) {
            throw new RuntimeException('Worker failed: '.$worker->getErrorOutput().$worker->getOutput());
        }
    }
}

it('applies success once when a webhook and the return-page status check race on the same payment', function (int $round): void {
    $body = stripeEvent('payment_intent.succeeded', stripeIntent());
    $webhook = (new PaymentWebhook)->forceFill([
        'payment_gateway_id' => $this->gateway->id, 'provider_event_id' => 'evt_race_'.$round, 'event_type' => 'payment_intent.succeeded',
        'signature_valid' => true, 'payload' => $body, 'status' => WebhookStatus::Received,
    ]);
    $webhook->save();

    $startAt = microtime(true) + 1.5;
    finishPaymentWorkers(
        startPaymentWorker(['mode' => 'webhook', 'webhook_id' => $webhook->id, 'start_at' => $startAt]),
        startPaymentWorker(['mode' => 'verify', 'payment_id' => $this->payment->id, 'start_at' => $startAt]),
    );

    expect($this->order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($this->order->fresh()->primary_payment_id)->toBe($this->payment->id)
        ->and($this->payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and(OrderEvent::query()->where('order_id', $this->order->id)->where('to_status', OrderStatus::Paid)->count())->toBe(1)
        ->and(PaymentTransaction::query()->where('payment_id', $this->payment->id)->count())->toBe(1)
        ->and(AnalyticsEventDispatch::query()->where('order_id', $this->order->id)->count())->toBe(1)
        ->and($webhook->fresh()->status)->toBe(WebhookStatus::Processed);
})->with([1, 2, 3]);

it('stores one row when the same signed event is delivered twice at the same moment', function (): void {
    $body = stripeEvent('payment_intent.succeeded', stripeIntent(), 'evt_double_delivery');
    $signature = stripeSignature($body);
    $startAt = microtime(true) + 1.5;

    finishPaymentWorkers(
        startPaymentWorker(['mode' => 'intake', 'body' => $body, 'signature' => $signature, 'start_at' => $startAt]),
        startPaymentWorker(['mode' => 'intake', 'body' => $body, 'signature' => $signature, 'start_at' => $startAt]),
    );

    expect(PaymentWebhook::query()->where('provider_event_id', 'evt_double_delivery')->count())->toBe(1);
});
