<?php

namespace App\Domains\Fulfillment\Exceptions;

use RuntimeException;

/**
 * A change to the code-vault keys was refused because it could make stored codes unreadable or
 * their duplicate check unreliable (DigitalCodeKeyManager). The message names the reason, never a key.
 */
class DigitalCodeKeyChangeRefused extends RuntimeException {}
