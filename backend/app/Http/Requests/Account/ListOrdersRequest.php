<?php

namespace App\Http\Requests\Account;

use App\Domains\Orders\DTOs\CustomerOrderFilter;
use App\Domains\Orders\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Order history query (M-A5): `page`, `per_page` (max 50), `status` (an order status), `from` and
 * `to` (placement dates, `Y-m-d`, inclusive).
 */
class ListOrdersRequest extends PageRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'status' => ['sometimes', 'string', Rule::enum(OrderStatus::class)],
            'from' => ['sometimes', 'date_format:Y-m-d'],
            'to' => ['sometimes', 'date_format:Y-m-d', 'after_or_equal:from'],
        ];
    }

    public function filter(): CustomerOrderFilter
    {
        $from = $this->validated('from');
        $to = $this->validated('to');
        $status = $this->validated('status');

        return new CustomerOrderFilter(
            status: $status === null ? null : OrderStatus::from($status),
            from: $from === null ? null : CarbonImmutable::createFromFormat('Y-m-d', $from)->startOfDay(),
            to: $to === null ? null : CarbonImmutable::createFromFormat('Y-m-d', $to)->endOfDay(),
        );
    }
}
