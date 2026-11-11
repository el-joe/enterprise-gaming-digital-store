<?php

namespace App\Domains\Analytics\Exceptions;

use RuntimeException;

/**
 * The GA4 Measurement Protocol did not accept a request (network error or non-2xx). The
 * message never contains the API secret.
 */
class MeasurementProtocolException extends RuntimeException {}
