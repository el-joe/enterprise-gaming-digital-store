<?php

namespace App\Domains\Seo\SearchConsole;

use RuntimeException;

/**
 * Google OAuth or the Search Console API refused or failed. Messages never contain a token,
 * code or secret.
 */
class SearchConsoleException extends RuntimeException {}
