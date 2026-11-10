<?php

namespace App\Http\Requests\Automation;

use App\Http\Middleware\Automation\AuthenticateAutomationRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * POST /alerts from the error workflow (Z01) or a monitoring workflow.
 */
class AlertRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'severity' => ['required', Rule::in(['info', 'warning', 'critical'])],
            'title' => ['required', 'string', 'max:190'],
            'message' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'failed_workflow_id' => ['sometimes', 'nullable', 'string', 'regex:'.AuthenticateAutomationRequest::WORKFLOW_PATTERN],
            'failed_execution_key' => ['sometimes', 'nullable', 'string', 'regex:'.AuthenticateAutomationRequest::EXECUTION_KEY_PATTERN],
            'n8n_execution_id' => ['sometimes', 'nullable', 'string', 'max:64'],
            'node' => ['sometimes', 'nullable', 'string', 'max:190'],
            'context' => ['sometimes', 'nullable', 'array', 'max:30'],
        ];
    }
}
