<?php

namespace App\Domains\Shared\Exceptions;

use LogicException;

/**
 * Raised when an operation combines money in two currencies. Always a programming error.
 */
class CurrencyMismatch extends LogicException {}
