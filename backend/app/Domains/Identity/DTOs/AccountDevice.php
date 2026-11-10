<?php

namespace App\Domains\Identity\DTOs;

use App\Domains\Identity\Enums\AccountDeviceType;
use Carbon\CarbonImmutable;

/**
 * One signed-in device of the customer (M-A5). `id` is the refresh-token family id (mobile) or
 * the opaque `web_...` id of a browser session; `ip` is the full last known address and is masked
 * by the resource.
 */
final readonly class AccountDevice
{
    public function __construct(
        public string $id,
        public AccountDeviceType $type,
        public ?string $deviceName,
        public ?string $userAgent,
        public ?string $ip,
        public ?CarbonImmutable $signedInAt,
        public ?CarbonImmutable $lastUsedAt,
        public ?CarbonImmutable $expiresAt,
        public bool $current,
    ) {}
}
