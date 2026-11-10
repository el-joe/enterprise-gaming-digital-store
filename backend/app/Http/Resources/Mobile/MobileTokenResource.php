<?php

namespace App\Http\Resources\Mobile;

use App\Domains\Identity\DTOs\IssuedTokens;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The token pair handed to the app once. Shown only in the response body, never logged.
 *
 * @property IssuedTokens $resource
 */
class MobileTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'token_type' => 'Bearer',
            'access_token' => $this->resource->accessToken,
            'access_token_expires_at' => $this->resource->accessTokenExpiresAt->toIso8601String(),
            'refresh_token' => $this->resource->refreshToken,
            'refresh_token_expires_at' => $this->resource->refreshTokenExpiresAt->toIso8601String(),
        ];
    }
}
