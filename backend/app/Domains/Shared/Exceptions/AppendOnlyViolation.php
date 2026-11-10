<?php

namespace App\Domains\Shared\Exceptions;

use LogicException;

/**
 * Raised when application code tries to change a row in an append-only table.
 */
class AppendOnlyViolation extends LogicException {}
