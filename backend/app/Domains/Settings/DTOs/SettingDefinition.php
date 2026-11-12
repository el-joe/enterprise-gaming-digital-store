<?php

namespace App\Domains\Settings\DTOs;

use App\Domains\Settings\Enums\SettingType;

/**
 * One database setting that replaced a `.env` key or is applied over a config key ("Settings from
 * the database", `config/admin_settings.php`). `vault` marks a code-vault key (owner decision 11):
 * never shown or edited on a screen, written only by DigitalCodeKeyManager.
 */
final readonly class SettingDefinition
{
    public function __construct(
        public string $screen,
        public string $group,
        public string $key,
        public SettingType $type,
        public bool $encrypted,
        public string $permission,
        public ?string $configKey = null,
        public ?string $envKey = null,
        public bool $envFile = false,
        public ?string $format = null,
        public bool $vault = false,
    ) {}

    public function id(): string
    {
        return $this->group.'.'.$this->key;
    }
}
