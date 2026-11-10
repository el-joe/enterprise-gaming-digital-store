<?php

namespace App\Domains\Orders\Services;

use App\Domains\Orders\DTOs\IdempotencyClaim;
use App\Domains\Orders\Exceptions\IdempotencyConflict;
use App\Domains\Orders\Models\IdempotencyKey;
use Carbon\CarbonImmutable;
use LogicException;

/**
 * Database-backed Idempotency-Key handling. A claim is committed on its own, outside the work's
 * transaction, so a concurrent request sees it at once; the stored response is written inside
 * the work's transaction, so it exists exactly when the work committed. The claim's lock time is
 * a fencing token: a request whose lock was taken over after going stale can no longer complete.
 */
class IdempotencyStore
{
    /** A claim older than this with no response is treated as abandoned (crashed request). */
    public const int LOCK_SECONDS = 120;

    public const int RETENTION_HOURS = 24;

    private const int CLAIM_ATTEMPTS = 3;

    /**
     * @throws IdempotencyConflict
     */
    public function claim(string $scope, string $key, string $ownerKey, string $requestHash): IdempotencyClaim
    {
        $ownerHash = hash('sha256', $ownerKey);

        for ($attempt = 0; $attempt < self::CLAIM_ATTEMPTS; $attempt++) {
            $now = CarbonImmutable::now()->startOfSecond();

            $inserted = IdempotencyKey::query()->insertOrIgnore([
                'scope' => $scope,
                'key' => $key,
                'owner_hash' => $ownerHash,
                'request_hash' => $requestHash,
                'locked_at' => $now,
                'expires_at' => $now->addHours(self::RETENTION_HOURS),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $row = IdempotencyKey::query()->where('scope', $scope)->where('key', $key)->where('owner_hash', $ownerHash)->first();

            if ($row === null) {
                continue;
            }

            if ($inserted === 1) {
                return IdempotencyClaim::acquired($row->id, $now);
            }

            $claim = $this->resolveExisting($row, $requestHash, $now);

            if ($claim !== null) {
                return $claim;
            }
        }

        throw IdempotencyConflict::inProgress();
    }

    /**
     * Store the response. Must run inside the transaction that did the work.
     *
     * @param  array<string, mixed>  $body
     *
     * @throws IdempotencyConflict when the claim was taken over meanwhile
     */
    public function complete(IdempotencyClaim $claim, int $status, array $body): void
    {
        if ($claim->lockToken === null) {
            throw new LogicException('A replayed claim cannot be completed.');
        }

        $updated = IdempotencyKey::query()
            ->whereKey($claim->keyId)
            ->where('locked_at', $claim->lockToken)
            ->whereNull('response_status')
            ->update([
                'response_status' => $status,
                'response_body' => json_encode($body, JSON_THROW_ON_ERROR),
                'locked_at' => null,
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            throw IdempotencyConflict::inProgress();
        }
    }

    /**
     * Give the key back after a failure that changed nothing, so the client may retry with it.
     */
    public function release(IdempotencyClaim $claim): void
    {
        if ($claim->lockToken === null) {
            return;
        }

        IdempotencyKey::query()
            ->whereKey($claim->keyId)
            ->where('locked_at', $claim->lockToken)
            ->whereNull('response_status')
            ->delete();
    }

    public function prune(): int
    {
        return IdempotencyKey::query()->where('expires_at', '<=', now())->delete();
    }

    private function resolveExisting(IdempotencyKey $row, string $requestHash, CarbonImmutable $now): ?IdempotencyClaim
    {
        if ($row->expires_at->lessThanOrEqualTo($now)) {
            return $this->takeOver($row, $now, ['request_hash' => $requestHash, 'response_status' => null, 'response_body' => null, 'expires_at' => $now->addHours(self::RETENTION_HOURS)], requireNoResponse: false);
        }

        if (! hash_equals($row->request_hash, $requestHash)) {
            throw IdempotencyConflict::mismatch();
        }

        if ($row->response_status !== null) {
            return IdempotencyClaim::replay($row->id, $row->response_status, $row->response_body ?? []);
        }

        if ($row->locked_at !== null && $row->locked_at->greaterThan($now->subSeconds(self::LOCK_SECONDS))) {
            throw IdempotencyConflict::inProgress();
        }

        return $this->takeOver($row, $now, [], requireNoResponse: true);
    }

    /**
     * Compare-and-set on the previous lock time, so exactly one of several racing requests wins.
     *
     * @param  array<string, mixed>  $changes
     */
    private function takeOver(IdempotencyKey $row, CarbonImmutable $now, array $changes, bool $requireNoResponse): ?IdempotencyClaim
    {
        $updated = IdempotencyKey::query()
            ->whereKey($row->id)
            ->when($row->locked_at === null, static fn ($query) => $query->whereNull('locked_at'), static fn ($query) => $query->where('locked_at', $row->locked_at))
            ->when($requireNoResponse, static fn ($query) => $query->whereNull('response_status'))
            ->update([...$changes, 'locked_at' => $now, 'updated_at' => $now]);

        if ($updated !== 1) {
            throw IdempotencyConflict::inProgress();
        }

        return IdempotencyClaim::acquired($row->id, $now);
    }
}
