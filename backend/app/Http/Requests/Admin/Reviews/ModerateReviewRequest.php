<?php

namespace App\Http\Requests\Admin\Reviews;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ModerateReviewRequest extends FormRequest
{
    /**
     * A reason is required to reject.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'reason' => [$this->routeIs('*.reject') ? 'required' : 'sometimes', 'nullable', 'string', 'min:3', 'max:1000'],
        ];
    }
}
