<?php

use App\Domains\Shared\Services\SensitiveDataRedactor;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

const CANARY = 'CANARY-7f3a9c-DIGITAL-CODE';

beforeEach(function (): void {
    $this->logPath = storage_path('logs/redaction-test-'.uniqid().'.log');
    config(['logging.channels.single.path' => $this->logPath]);
    Log::forgetChannel('single');
});

afterEach(function (): void {
    File::delete($this->logPath);
});

it('keeps canary values out of the log file', function (string $key): void {
    Log::channel('single')->info('supplier response', [$key => CANARY, 'order_id' => 42]);

    $contents = File::get($this->logPath);

    expect($contents)->not->toContain(CANARY)
        ->toContain('[REDACTED]')
        ->toContain('"order_id":42');
})->with(['password', 'token', 'access_token', 'secret', 'client_secret', 'api_key', 'apiKey', 'authorization', 'Authorization', 'code', 'card_number', 'cardNumber', 'pin', 'pin_code', 'pinCode', 'redemption_url', 'redemptionUrl', 'digital_code', 'code_ciphertext']);

it('redacts through the default stack channel', function (): void {
    config(['logging.default' => 'stack', 'logging.channels.stack.channels' => ['single']]);
    Log::forgetChannel('stack');

    Log::info('via stack', ['pin' => CANARY, 'order_id' => 42]);

    expect(File::get($this->logPath))->not->toContain(CANARY)->toContain('"order_id":42');
});

it('redacts nested context', function (): void {
    Log::channel('single')->error('nested', ['response' => ['data' => [['cardNumber' => CANARY, 'productId' => 7]]]]);

    expect(File::get($this->logPath))->not->toContain(CANARY)->toContain('"productId":7');
});

it('leaves ordinary diagnostic keys readable', function (string $key): void {
    expect(app(SensitiveDataRedactor::class)->isSensitiveKey($key))->toBeFalse();
})->with(['status_code', 'http_status', 'ping_ms', 'currency_code', 'country_code', 'order_id']);
