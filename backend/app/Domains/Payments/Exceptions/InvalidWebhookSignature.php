<?php

namespace App\Domains\Payments\Exceptions;

use RuntimeException;

/**
 * The webhook could not be authenticated. The message is a reason code only; it never
 * contains the body, the signature or the secret.
 */
class InvalidWebhookSignature extends RuntimeException {}
