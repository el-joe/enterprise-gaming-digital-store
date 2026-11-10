<?php

namespace App\Http\Resources\Account;

use App\Domains\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The customer's own profile (M-A5).
 *
 * @mixin User
 */
class ProfileResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id' => $this->public_id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,
            'phone' => $this->phone,
            'locale' => $this->locale,
            'display_currency' => $this->display_currency,
            'country_code' => $this->country_code,
            'two_factor_enabled' => $this->hasTwoFactorEnabled(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
