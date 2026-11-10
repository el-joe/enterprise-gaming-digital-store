<?php

namespace App\Domains\Notifications\DTOs;

use App\Domains\Notifications\Enums\NotificationPreferenceCategory;
use App\Domains\Notifications\Enums\NotificationPreferenceChannel;

/**
 * One cell of the customer's notification preferences (M-A5): the effective value, and whether
 * the customer may change it.
 */
final readonly class PreferenceSetting
{
    public function __construct(
        public NotificationPreferenceCategory $category,
        public NotificationPreferenceChannel $channel,
        public bool $enabled,
        public bool $mandatory,
    ) {}

    /**
     * @return array{category: string, channel: string, enabled: bool, mandatory: bool}
     */
    public function toArray(): array
    {
        return ['category' => $this->category->value, 'channel' => $this->channel->value, 'enabled' => $this->enabled, 'mandatory' => $this->mandatory];
    }
}
