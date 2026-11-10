<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Inbox query (M-A5): `page`, `per_page` (max 50) and `unread` (boolean, only unread when true).
 */
class ListNotificationsRequest extends PageRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [...parent::rules(), 'unread' => ['sometimes', 'boolean']];
    }

    public function unreadOnly(): bool
    {
        return $this->boolean('unread');
    }
}
