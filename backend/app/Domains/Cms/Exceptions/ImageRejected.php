<?php

namespace App\Domains\Cms\Exceptions;

use RuntimeException;

/**
 * An image the optimizer refuses: `empty`, `too_large`, `svg`, `not_image` or `dimensions`.
 */
class ImageRejected extends RuntimeException
{
    public function __construct(public readonly string $reason)
    {
        parent::__construct('Image rejected: '.$reason);
    }
}
