<?php

namespace App\Domains\Notifications\Exceptions;

use RuntimeException;

/**
 * FCM is enabled but the project id or the service-account key is missing or unreadable
 * (M-A18). The message never names the key's contents.
 */
class PushNotConfigured extends RuntimeException {}
