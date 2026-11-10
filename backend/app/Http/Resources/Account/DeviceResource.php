<?php

namespace App\Http\Resources\Account;

use App\Domains\Identity\DTOs\AccountDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A signed-in device (M-A5). The IP address is masked: IPv4 keeps the first three octets
 * (`203.0.113.x`), IPv6 the first four groups (`2001:0db8:85a3:08d3:x`).
 *
 * @property-read AccountDevice $resource
 */
class DeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $device = $this->resource;

        return [
            'id' => $device->id,
            'type' => $device->type->value,
            'device_name' => $device->deviceName,
            'user_agent' => $device->userAgent,
            'ip' => self::maskIp($device->ip),
            'created_at' => $device->signedInAt?->toIso8601String(),
            'last_used_at' => $device->lastUsedAt?->toIso8601String(),
            'expires_at' => $device->expiresAt?->toIso8601String(),
            'current' => $device->current,
        ];
    }

    public static function maskIp(?string $ip): ?string
    {
        return match (true) {
            $ip === null || $ip === '' => null,
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false => implode('.', array_slice(explode('.', $ip), 0, 3)).'.x',
            filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false => implode(':', array_slice(str_split(bin2hex((string) inet_pton($ip)), 4), 0, 4)).':x',
            default => null,
        };
    }
}
