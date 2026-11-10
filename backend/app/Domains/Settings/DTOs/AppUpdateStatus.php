<?php

namespace App\Domains\Settings\DTOs;

/**
 * How an installed app version compares with the configured versions: `required` below the
 * minimum supported version, `available` below the latest version.
 */
final readonly class AppUpdateStatus
{
    public function __construct(
        public string $platform,
        public string $version,
        public bool $required,
        public bool $available,
    ) {}
}
