<?php

namespace App\Domains\Pricing\Repositories;

use App\Domains\Pricing\Models\PricingRule;
use App\Domains\Shared\DTOs\ListQuery;
use App\Domains\Shared\Services\ListQueryApplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PricingRuleRepository
{
    public function __construct(private ListQueryApplier $lists) {}

    public function adminPage(ListQuery $list): LengthAwarePaginator
    {
        return $this->lists->paginate(
            PricingRule::query(),
            $list,
            ['scope' => 'exact', 'kind' => 'exact', 'is_active' => 'bool', 'currency' => 'exact', 'country_code' => 'exact'],
            ['id', 'name', 'priority', 'created_at'],
            ['name'],
        );
    }

    /**
     * Rules in force now. Rule counts are small, so a single scan per pricing batch is cheaper
     * than resolving precedence with a query per variant.
     *
     * @return Collection<int, PricingRule>
     */
    public function activeNow(): Collection
    {
        return PricingRule::query()
            ->where('is_active', true)
            ->where(static fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(static fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->get();
    }
}
