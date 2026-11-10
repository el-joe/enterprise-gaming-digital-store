<?php

/**
 * SECURITY.md section 7: a canary code delivered through the full supplier path must not appear
 * in any sink other than the vault (encrypted) and the authorised reveal response.
 * Reloadly is mocked; awaiting sandbox verification.
 */

use App\Domains\Fulfillment\Jobs\FulfilOrder;
use App\Domains\Identity\Models\User;
use App\Domains\Orders\Models\OrderItemUnit;
use App\Http\Controllers\Account\ConfirmPasswordController;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

const CANARY_CODE = '73310000999988887777';

const CANARY_PIN = '55667788';

it('keeps the canary code out of logs, tables, job payloads, mail, resources and errors', function (): void {
    // M17: the outbox is off by default; turn it on so the automation_outbox sink holds real events.
    config(['automation.outbox.enabled' => true]);
    $logFile = storage_path('logs/laravel.log');
    $logOffset = is_file($logFile) ? filesize($logFile) : 0;
    $logged = [];
    $payloads = [];
    Event::listen(MessageLogged::class, static function (MessageLogged $event) use (&$logged): void {
        if ($event->message !== 'fulfilment.canary_probe') {
            $logged[] = $event->message.' '.json_encode($event->context);
        }
    });
    Event::listen(JobProcessing::class, static function (JobProcessing $event) use (&$payloads): void {
        $payloads[] = $event->job->getRawBody();
    });

    $customer = User::factory()->create(['email_verified_at' => now()]);
    $variant = sellableVariant('9');
    $supplier = reloadlySupplier();
    mapToSupplier($variant, $supplier);
    Http::fake([
        'auth.reloadly.com/*' => Http::response(['access_token' => 'tok', 'expires_in' => 3600]),
        RELOADLY_API.'/orders' => Http::response(reloadlyTransaction()),
        RELOADLY_API.'/orders/transactions/*/cards' => Http::sequence()
            ->push('{"cardNumber":'.CANARY_CODE.'}', 500)
            ->push('{"cardNumber":'.CANARY_CODE.',"pinCode":'.CANARY_PIN.',"redemptionUrl":"https://r.example.com/'.CANARY_CODE.'"}'),
    ]);

    $order = paidOrderFor($variant, orderAttributes: ['user_id' => $customer->id]);
    FulfilOrder::dispatch($order->id);
    $this->travel(11)->minutes();
    $this->artisan('suppliers:sweep-stale-submissions')->assertSuccessful();
    // Written on purpose: the file sink below proves the redaction processor removes it.
    Log::info('fulfilment.canary_probe', ['code' => CANARY_CODE, 'cardNumber' => CANARY_CODE]);

    $unit = OrderItemUnit::query()->sole();
    expect($unit->fresh()->delivered_at)->not->toBeNull()
        ->and(DB::table('automation_outbox')->where('event_type', 'order.delivered')->exists())->toBeTrue();

    actingAsStaffWith(['orders.view', 'suppliers.logs']);
    $timeline = $this->getJson("/admin/api/orders/{$order->public_id}/timeline")->assertOk()->getContent();
    $logs = $this->getJson("/admin/api/suppliers/{$supplier->id}/logs?per_page=100")->assertOk()->getContent();
    $denied = $this->actingAs($customer)->withSession([ConfirmPasswordController::SESSION_KEY => 0])
        ->postJson("/api/v1/account/orders/{$order->public_id}/units/{$unit->public_id}/reveal")->assertStatus(423)->getContent();

    $mail = collect(app('mailer')->getSymfonyTransport()->messages())->map(static fn ($message): string => $message->toString())->implode("\n");

    $sinks = [
        'log events raised by the application' => implode("\n", $logged),
        'log file' => is_file($logFile) ? (string) file_get_contents($logFile, offset: $logOffset) : '',
        'supplier_api_logs' => DB::table('supplier_api_logs')->get()->toJson(),
        'order_events' => DB::table('order_events')->get()->toJson(),
        'audit_logs' => DB::table('audit_logs')->get()->toJson(),
        'automation_outbox' => DB::table('automation_outbox')->get()->toJson(),
        'analytics_event_dispatches' => DB::table('analytics_event_dispatches')->get()->toJson(),
        'supplier_transactions' => DB::table('supplier_transactions')->get()->toJson(),
        'digital_codes (stored)' => DB::table('digital_codes')->get()->toJson(),
        'job payloads' => implode("\n", $payloads),
        'mail' => $mail,
        'admin timeline' => $timeline,
        'admin supplier logs' => $logs,
        'reveal error response' => $denied,
    ];

    expect($payloads)->not->toBeEmpty()->and($mail)->toContain($order->order_number);

    foreach ($sinks as $sink => $content) {
        expect(str_contains($content, CANARY_CODE))->toBeFalse("Canary code found in {$sink}")
            ->and(str_contains($content, CANARY_PIN))->toBeFalse("Canary PIN found in {$sink}");
    }

    $revealed = $this->actingAs($customer)->withSession([ConfirmPasswordController::SESSION_KEY => now()->getTimestamp()])
        ->postJson("/api/v1/account/orders/{$order->public_id}/units/{$unit->public_id}/reveal")->assertOk();

    expect($revealed->json('data.code'))->toBe(CANARY_CODE)->and($revealed->json('data.pin'))->toBe(CANARY_PIN);
});
