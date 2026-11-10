<?php

namespace App\Http\Requests\Admin\Catalog;

use App\Domains\Identity\Enums\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $needed = $this->input('action') === 'delete' ? PermissionName::CatalogManage : PermissionName::CatalogPublish;

        return (bool) $this->user()?->checkPermissionTo($needed->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete'])],
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['string', 'size:26', 'distinct'],
        ];
    }
}
