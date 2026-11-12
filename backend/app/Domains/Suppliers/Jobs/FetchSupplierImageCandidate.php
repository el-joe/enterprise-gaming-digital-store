<?php

namespace App\Domains\Suppliers\Jobs;

use App\Domains\Suppliers\Enums\SupplierImageKind;
use App\Domains\Suppliers\Models\SupplierImageCandidate;
use App\Domains\Suppliers\Services\SupplierImageCandidates;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;

/**
 * Downloads one supplier image for a platform (brand logo) or category on the `sync` queue and
 * keeps it as that supplier's candidate (SupplierImageCandidates). Unique per subject, supplier
 * and kind while queued, so a brand shared by many supplier products is fetched once. A network
 * failure is stored on the candidate and retried with backoff.
 */
class FetchSupplierImageCandidate implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public int $uniqueFor = 900;

    public function __construct(
        public readonly string $subjectType,
        public readonly int $subjectId,
        public readonly int $supplierId,
        public readonly string $kind,
        public readonly string $url,
        public readonly bool $force = false,
    ) {
        $this->onQueue('sync');
    }

    public function uniqueId(): string
    {
        return implode(':', [$this->subjectType, $this->subjectId, $this->supplierId, $this->kind]);
    }

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(SupplierImageCandidates $candidates): void
    {
        $class = Relation::getMorphedModel($this->subjectType);
        $subject = $class === null ? null : $class::query()->find($this->subjectId);
        $kind = SupplierImageKind::tryFrom($this->kind);

        if ($subject === null || $kind === null) {
            return;
        }

        try {
            $candidates->fetch($subject, $this->supplierId, $kind, $this->url, $this->force);
        } catch (ConnectionException) {
            SupplierImageCandidate::query()->updateOrCreate(
                ['subject_type' => $this->subjectType, 'subject_id' => $this->subjectId, 'supplier_id' => $this->supplierId, 'kind' => $this->kind],
                ['source_url' => $this->url, 'error' => 'network', 'fetched_at' => now()],
            );

            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
            }
        }
    }
}
