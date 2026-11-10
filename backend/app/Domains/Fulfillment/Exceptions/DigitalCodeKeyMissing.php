<?php

namespace App\Domains\Fulfillment\Exceptions;

use RuntimeException;

/**
 * The code keyring is absent or invalid. Codes are never stored or read without it.
 */
class DigitalCodeKeyMissing extends RuntimeException {}
