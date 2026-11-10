<?php

namespace App\Domains\Cms\DTOs;

use App\Domains\Cms\Enums\LinkTargetType;

/**
 * A structured link (M-A4): `value` is a slug (product, category, platform), the search text
 * (search) or an absolute URL (url). `url` is always the absolute web URL of the same target.
 */
final readonly class LinkTarget
{
    public function __construct(
        public LinkTargetType $type,
        public string $value,
        public string $url,
    ) {}

    /**
     * @return array{type: string, value: string, url: string}
     */
    public function toArray(): array
    {
        return ['type' => $this->type->value, 'value' => $this->value, 'url' => $this->url];
    }
}
