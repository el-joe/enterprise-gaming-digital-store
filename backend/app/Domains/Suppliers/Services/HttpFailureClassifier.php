<?php

namespace App\Domains\Suppliers\Services;

use Illuminate\Http\Client\ConnectionException;

/**
 * Decides whether a transport failure happened before the request could reach the supplier
 * (architecture 7.2 (a): DNS failure, connection refused, TLS handshake failure, connect
 * timeout). Everything else, including a read timeout, a reset connection, a failed send and
 * any failure it does not recognise, may have been processed and is treated as ambiguous.
 */
final class HttpFailureClassifier
{
    /**
     * cURL errors raised before any request byte is sent: could not resolve host or proxy (5, 6),
     * could not connect (7), and TLS handshake or certificate failures (35, 51, 53, 54, 58, 59,
     * 60, 66, 77, 83, 90, 91).
     */
    private const array PRE_SEND_ERRORS = [5, 6, 7, 35, 51, 53, 54, 58, 59, 60, 66, 77, 83, 90, 91];

    private const int TIMEOUT = 28;

    /**
     * libcurl's wording for a timeout during name resolution or connect, as opposed to
     * "Operation timed out after ... bytes received", which is a read timeout.
     */
    private const string CONNECT_PHASE_TIMEOUT = '/(Connection timed out after|Connection timeout after|Resolving timed out after|Failed to connect to)/i';

    public static function wasNotSent(ConnectionException $exception): bool
    {
        $errno = self::errorNumber($exception);
        $message = $exception->getMessage();

        if ($errno === null) {
            return false;
        }

        if (in_array($errno, self::PRE_SEND_ERRORS, true)) {
            return true;
        }

        return $errno === self::TIMEOUT && preg_match(self::CONNECT_PHASE_TIMEOUT, $message) === 1;
    }

    /**
     * A short label for logs and timelines, without the URL.
     */
    public static function label(ConnectionException $exception): string
    {
        $errno = self::errorNumber($exception);

        return match (true) {
            $errno === self::TIMEOUT && self::wasNotSent($exception) => 'connect_timeout',
            $errno === self::TIMEOUT => 'read_timeout',
            $errno === 52 => 'empty_reply',
            $errno === 55 => 'send_failed',
            $errno === 56 => 'connection_reset',
            $errno !== null && in_array($errno, self::PRE_SEND_ERRORS, true) => 'not_connected',
            default => 'transport_error',
        };
    }

    private static function errorNumber(ConnectionException $exception): ?int
    {
        return preg_match('/cURL error (\d+)/', $exception->getMessage(), $match) === 1 ? (int) $match[1] : null;
    }
}
