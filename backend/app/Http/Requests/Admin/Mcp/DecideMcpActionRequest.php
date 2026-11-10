<?php

namespace App\Http\Requests\Admin\Mcp;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * A note is optional on approval and required on rejection.
 */
class DecideMcpActionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $required = str_ends_with((string) $this->route()?->getName(), '.reject');

        return ['note' => [$required ? 'required' : 'nullable', 'string', 'min:5', 'max:1000']];
    }
}
