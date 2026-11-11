<?php

namespace App\Http\Requests\Admin\Users;

use App\Domains\Identity\Enums\PermissionName;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * A full list of permissions for a role or a staff member; only names from SECURITY.md section 4.
 */
class SyncPermissionsRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'permissions' => ['present', 'array', 'max:100'],
            'permissions.*' => ['string', 'distinct', Rule::enum(PermissionName::class)],
        ];
    }
}
