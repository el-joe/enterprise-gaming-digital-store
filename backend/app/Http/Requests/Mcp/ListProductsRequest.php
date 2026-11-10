<?php

namespace App\Http\Requests\Mcp;

use App\Domains\Catalog\Enums\ProductStatus;
use App\Domains\Shared\Rules\Slug;
use Illuminate\Validation\Rule;

class ListProductsRequest extends McpPageRequest
{
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'status' => ['sometimes', 'nullable', Rule::enum(ProductStatus::class)],
            'platform' => ['sometimes', 'nullable', 'string', 'max:140', new Slug],
            'category' => ['sometimes', 'nullable', 'string', 'max:140', new Slug],
        ];
    }

    /**
     * @return array{status: ?string, platform: ?string, category: ?string}
     */
    public function filters(): array
    {
        return [
            'status' => $this->validated('status'),
            'platform' => $this->validated('platform'),
            'category' => $this->validated('category'),
        ];
    }
}
