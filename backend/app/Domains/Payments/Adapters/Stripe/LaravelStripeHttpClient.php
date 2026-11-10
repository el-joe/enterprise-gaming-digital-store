<?php

namespace App\Domains\Payments\Adapters\Stripe;

use App\Domains\Suppliers\Services\HttpFailureClassifier;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Stripe\Exception\ApiConnectionException;
use Stripe\HttpClient\ClientInterface;
use Stripe\Util\CaseInsensitiveArray;
use Stripe\Util\Util;

/**
 * Transport for the official Stripe SDK through Laravel's HTTP client, so Stripe calls obey the
 * same timeouts, `Http::preventStrayRequests()` and `Http::fake()` as every other provider. The
 * SDK still builds every request (form encoding, Idempotency-Key, Stripe-Version) and parses
 * every response. The SDK's own network retries are disabled: a lost response is re-read.
 *
 * A transport failure is raised as ApiConnectionException whose message starts with
 * `not_sent:` only when the request provably never left.
 */
class LaravelStripeHttpClient implements ClientInterface
{
    public const string NOT_SENT_PREFIX = 'not_sent:';

    public function __construct(private int $connectTimeout, private int $timeout) {}

    /**
     * @param  array<int, string>  $headers
     * @param  array<string, mixed>  $params
     * @return array{0: string, 1: int, 2: CaseInsensitiveArray}
     */
    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
    {
        $method = strtolower($method);
        $params = Util::objectsToIds($params, false);
        $headerMap = [];

        foreach ($headers as $line) {
            [$name, $value] = array_map('trim', explode(':', $line, 2)) + [1 => ''];
            $headerMap[$name] = $value;
        }

        $contentType = $headerMap['Content-Type'] ?? 'application/x-www-form-urlencoded';
        unset($headerMap['Content-Type'], $headerMap['Expect']);

        $request = Http::connectTimeout($this->connectTimeout)->timeout($this->timeout)->withHeaders($headerMap);

        if ($method === 'post') {
            $request = $request->withBody(Util::encodeParameters($params), $contentType);
        } elseif ($params !== []) {
            $absUrl .= '?'.Util::encodeParameters($params, $apiMode);
        }

        try {
            $response = $request->send(strtoupper($method), $absUrl);
        } catch (ConnectionException $exception) {
            $label = HttpFailureClassifier::label($exception);

            throw ApiConnectionException::factory((HttpFailureClassifier::wasNotSent($exception) ? self::NOT_SENT_PREFIX : 'sent:').$label);
        }

        $responseHeaders = new CaseInsensitiveArray;

        foreach ($response->headers() as $name => $values) {
            $responseHeaders[$name] = is_array($values) ? (string) ($values[0] ?? '') : (string) $values;
        }

        return [$response->body(), $response->status(), $responseHeaders];
    }
}
