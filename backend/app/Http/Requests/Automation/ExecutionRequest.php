<?php

namespace App\Http\Requests\Automation;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /executions. The workflow and execution key come from the signed headers; the body says
 * whether the execution starts or finishes, and how it finished.
 */
class ExecutionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['start', 'finish'])],
            'status' => ['required_if:action,finish', 'prohibited_if:action,start', Rule::in(['success', 'failed'])],
            'summary' => ['sometimes', 'nullable', 'array', 'max:50'],
            'error' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
