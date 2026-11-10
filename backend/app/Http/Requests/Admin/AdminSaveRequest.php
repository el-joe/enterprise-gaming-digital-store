<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Base for a request that both creates (POST, fields required) and updates (PATCH, fields optional).
 */
abstract class AdminSaveRequest extends FormRequest
{
    protected function presence(): string
    {
        return $this->isMethod('PATCH') ? 'sometimes' : 'required';
    }
}
