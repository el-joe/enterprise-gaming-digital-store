<?php

use App\Domains\Orders\Exceptions\IdempotencyConflict;
use App\Domains\Orders\Models\IdempotencyKey;
use App\Domains\Orders\Services\IdempotencyStore;

beforeEach(function (): void {
    $this->store = app(IdempotencyStore::class);
});

it('reports a key that is still being processed and one reused with another body', function (): void {
    $this->store->claim('scope', 'key-1', 'user:1', 'hash-a');

    expect(fn () => $this->store->claim('scope', 'key-1', 'user:1', 'hash-a'))->toThrow(IdempotencyConflict::class, 'still being processed')
        ->and(fn () => $this->store->claim('scope', 'key-1', 'user:1', 'hash-b'))->toThrow(IdempotencyConflict::class, 'different request');
});

it('replays a completed response', function (): void {
    $claim = $this->store->claim('scope', 'key-1', 'user:1', 'hash-a');
    $this->store->complete($claim, 201, ['data' => ['id' => 5]]);

    $replay = $this->store->claim('scope', 'key-1', 'user:1', 'hash-a');

    expect($replay->isReplay())->toBeTrue()
        ->and($replay->replayStatus)->toBe(201)
        ->and($replay->replayBody)->toBe(['data' => ['id' => 5]]);
});

it('takes over an abandoned claim and fences out the original holder', function (): void {
    $abandoned = $this->store->claim('scope', 'key-1', 'user:1', 'hash-a');
    $this->travel(IdempotencyStore::LOCK_SECONDS + 1)->seconds();

    $takeover = $this->store->claim('scope', 'key-1', 'user:1', 'hash-a');

    expect($takeover->isReplay())->toBeFalse()
        ->and(fn () => $this->store->complete($abandoned, 201, []))->toThrow(IdempotencyConflict::class);

    $this->store->complete($takeover, 201, ['ok' => true]);

    expect(IdempotencyKey::query()->sole()->response_status)->toBe(201);
});

it('releases a key so the same request can retry', function (): void {
    $this->store->release($this->store->claim('scope', 'key-1', 'user:1', 'hash-a'));

    expect(IdempotencyKey::query()->exists())->toBeFalse()
        ->and($this->store->claim('scope', 'key-1', 'user:1', 'hash-a')->isReplay())->toBeFalse();
});

it('keeps owners apart and forgets keys after retention', function (): void {
    $this->store->complete($this->store->claim('scope', 'key-1', 'user:1', 'hash-a'), 201, ['a' => 1]);

    expect($this->store->claim('scope', 'key-1', 'user:2', 'hash-z')->isReplay())->toBeFalse();

    $this->travel(IdempotencyStore::RETENTION_HOURS + 1)->hours();

    expect($this->store->claim('scope', 'key-1', 'user:1', 'hash-b')->isReplay())->toBeFalse();

    $this->travel(IdempotencyStore::RETENTION_HOURS + 1)->hours();
    $this->artisan('orders:prune-idempotency-keys')->expectsOutput('Pruned 2 keys.')->assertSuccessful();
});
