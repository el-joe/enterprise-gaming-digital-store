<?php

namespace App\Domains\Affiliates\Services;

use App\Domains\Affiliates\Models\Affiliate;
use App\Domains\Affiliates\Repositories\AffiliateRepository;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Shared\Services\CrudService;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Admin management of affiliates. `user_id` is set on create only, `status` by staff; payout
 * details are encrypted, write-only and never returned or audited.
 *
 * @extends CrudService<Affiliate>
 */
class AffiliateService extends CrudService
{
    public function __construct(ListQueryApplier $lists, AuditLogger $audit, private AffiliateRepository $affiliates)
    {
        parent::__construct($lists, $audit);
    }

    public function commissions(Affiliate $affiliate, ?string $status, int $page, int $perPage): LengthAwarePaginator
    {
        return $this->affiliates->commissions($affiliate->id, $status, $page, $perPage);
    }

    protected function model(): string
    {
        return Affiliate::class;
    }

    protected function auditPrefix(): string
    {
        return 'affiliates.affiliate';
    }

    protected function filters(): array
    {
        return ['status' => 'exact'];
    }

    protected function searchColumns(): array
    {
        return ['code'];
    }

    protected function relations(): array
    {
        return ['user:id,name,email'];
    }

    protected function auditIgnored(): array
    {
        return ['payout_details'];
    }

    protected function prepare(Model $model, array $attributes): array
    {
        if (isset($attributes['code'])) {
            $attributes['code'] = strtoupper(trim((string) $attributes['code']));
        }

        return $attributes;
    }

    protected function applyGuarded(Model $model, array $data): void
    {
        if (! $model->exists && isset($data['user_id'])) {
            $model->forceFill(['user_id' => (int) $data['user_id']]);
        }

        if (isset($data['status'])) {
            $model->forceFill(['status' => $data['status']]);
        }
    }
}
