<?php

namespace App\Domains\Catalog\DTOs;

final readonly class SearchSuggestion
{
    public function __construct(
        public string $type,
        public string $title,
        public string $slug,
    ) {}
}
