<?php

namespace App\Http\Requests\Mcp;

use App\Domains\Orders\Enums\OrderStatus;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;

class ListOrdersRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'status' => ['sometimes', 'nullable', Rule::enum(OrderStatus::class)],
            'from' => ['sometimes', 'nullable', 'date'],
            'to' => ['sometimes', 'nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return list<OrderStatus>
     */
    public function statuses(): array
    {
        $status = $this->validated('status');

        return $status === null ? [] : [OrderStatus::from($status)];
    }

    public function from(): ?CarbonImmutable
    {
        $from = $this->validated('from');

        return $from === null ? null : CarbonImmutable::parse($from)->startOfDay();
    }

    public function to(): ?CarbonImmutable
    {
        $to = $this->validated('to');

        return $to === null ? null : CarbonImmutable::parse($to)->endOfDay();
    }
}
