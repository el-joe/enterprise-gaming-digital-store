<?php

namespace App\Http\Requests\Web\Account;

use App\Domains\Orders\DTOs\CustomerOrderFilter;
use App\Domains\Orders\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Filters of the account orders page (M13): one status and a placement period in days, mapped to
 * the order history filter of the JSON route (CustomerOrderFilter).
 */
class OrderListPageRequest extends AccountPageRequest
{
    /** @var list<string> */
    public const array PERIODS = ['30', '90', '365', 'all'];

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'status' => ['sometimes', 'nullable', Rule::enum(OrderStatus::class)],
            'period' => ['sometimes', 'nullable', Rule::in(self::PERIODS)],
        ];
    }

    public function filter(): CustomerOrderFilter
    {
        $period = $this->period();

        return new CustomerOrderFilter(
            status: OrderStatus::tryFrom((string) $this->validated('status')),
            from: $period === 'all' ? null : CarbonImmutable::now()->subDays((int) $period),
        );
    }

    public function period(): string
    {
        return (string) ($this->validated('period') ?? 'all');
    }

    public function status(): ?string
    {
        return $this->validated('status');
    }
}
