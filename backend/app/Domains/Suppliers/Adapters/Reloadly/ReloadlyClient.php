<?php

namespace App\Domains\Suppliers\Adapters\Reloadly;

use App\Domains\Shared\Enums\EnvironmentMode;
use App\Domains\Suppliers\Exceptions\SupplierNotConfigured;
use App\Domains\Suppliers\Models\Supplier;
use App\Domains\Suppliers\Models\SupplierCredential;
use App\Domains\Suppliers\Services\ExactJson;
use App\Domains\Suppliers\Services\SupplierApiLogger;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * HTTP transport for the Reloadly Gift Cards API (INTEGRATIONS.md 1.1): OAuth client-credentials
 * token per product audience, cached encrypted until shortly before `expires_in`, refreshed once
 * on a 401 (a 401 means the call did not run, so repeating it is safe), and every call logged
 * redacted to supplier_api_logs. Status: mocked tests only, awaiting sandbox verification.
 */
class ReloadlyClient
{
    private const int TOKEN_EXPIRY_MARGIN_SECONDS = 300;

    /**
     * @param  list<string>  $sensitiveKeys
     */
    public function __construct(
        private Supplier $supplier,
        private SupplierApiLogger $logger,
        private array $sensitiveKeys,
    ) {}

    public function baseUrl(): string
    {
        $override = $this->supplier->base_url_override;

        if (is_string($override) && $override !== '' && $this->isAllowedOverride($override)) {
            return rtrim($override, '/');
        }

        $mode = $this->supplier->mode === EnvironmentMode::Production ? 'production' : 'sandbox';

        return (string) config("suppliers.reloadly.base_urls.{$mode}");
    }

    public function isAllowedOverride(string $url): bool
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? null) === 'https'
            && in_array(strtolower((string) ($parts['host'] ?? '')), config('suppliers.override_hosts.reloadly_giftcards'), true)
            && ! isset($parts['user'], $parts['pass']);
    }

    /**
     * Send one API call. Connection failures are logged and rethrown for the caller to classify;
     * a 401 triggers exactly one token refresh and one repeat of the call.
     *
     * @param  array<string, scalar>  $query
     * @param  array<array-key, mixed>|null  $loggedBody  the body as it should appear in the log
     *
     * @throws ConnectionException
     * @throws SupplierNotConfigured when the token cannot be obtained; nothing was sent
     */
    public function send(
        string $operation,
        string $method,
        string $path,
        array $query = [],
        ?string $jsonBody = null,
        ?array $loggedBody = null,
        ?int $supplierTransactionId = null,
        bool $logResponse = true,
        ?string $accept = null,
    ): Response {
        $response = $this->attempt($operation, $method, $path, $query, $jsonBody, $loggedBody, $supplierTransactionId, $logResponse, $accept, $this->token());

        if ($response->status() === 401) {
            $response = $this->attempt($operation.'.after_token_refresh', $method, $path, $query, $jsonBody, $loggedBody, $supplierTransactionId, $logResponse, $accept, $this->token(fresh: true));
        }

        return $response;
    }

    /**
     * @return array<array-key, mixed>|null
     */
    public static function decode(Response $response): ?array
    {
        return ExactJson::decode($response->body());
    }

    /**
     * @param  array<string, scalar>  $query
     * @param  array<array-key, mixed>|null  $loggedBody
     */
    private function attempt(
        string $operation,
        string $method,
        string $path,
        array $query,
        ?string $jsonBody,
        ?array $loggedBody,
        ?int $supplierTransactionId,
        bool $logResponse,
        ?string $accept,
        string $token,
    ): Response {
        $url = $this->baseUrl().'/'.ltrim($path, '/');
        $started = hrtime(true);

        $request = $this->http()
            ->withToken($token)
            ->withHeaders(['Accept' => $accept ?? config('suppliers.reloadly.accept')]);

        if ($jsonBody !== null) {
            $request = $request->withBody($jsonBody, 'application/json');
        }

        try {
            $response = $request->send($method, $query === [] ? $url : $url.'?'.http_build_query($query));
        } catch (ConnectionException $exception) {
            $this->log($operation, $method, $url, null, $started, false, class_basename($exception), $loggedBody, null, $supplierTransactionId);

            throw $exception;
        }

        $this->log(
            $operation,
            $method,
            $url,
            $response->status(),
            $started,
            $response->successful(),
            null,
            $loggedBody,
            $logResponse ? (self::decode($response) ?? ['unparsed_body_bytes' => strlen($response->body())]) : null,
            $supplierTransactionId,
        );

        return $response;
    }

    /**
     * @throws SupplierNotConfigured
     */
    private function token(bool $fresh = false): string
    {
        $credentials = $this->credentials();
        $cacheKey = sprintf('suppliers:%d:token:%s:%s', $this->supplier->id, $this->supplier->mode->value, hash('sha256', $credentials['client_id']));

        if (! $fresh) {
            $cached = Cache::get($cacheKey);

            if (is_string($cached)) {
                try {
                    return Crypt::decryptString($cached);
                } catch (Throwable) {
                    Cache::forget($cacheKey);
                }
            }
        }

        $url = rtrim((string) config('suppliers.reloadly.auth_url'), '/').'/oauth/token';
        $started = hrtime(true);
        $body = [
            'client_id' => $credentials['client_id'],
            'client_secret' => $credentials['client_secret'],
            'grant_type' => 'client_credentials',
            'audience' => $this->baseUrl(),
        ];

        try {
            $response = $this->http()->acceptJson()->asJson()->post($url, $body);
        } catch (ConnectionException $exception) {
            $this->log('auth.token', 'POST', $url, null, $started, false, class_basename($exception), $body, null, null);

            throw new SupplierNotConfigured('The Reloadly token endpoint could not be reached.');
        }

        $payload = self::decode($response);
        $this->log('auth.token', 'POST', $url, $response->status(), $started, $response->successful(), null, $body, $payload, null);

        $token = $payload['access_token'] ?? null;
        $expiresIn = (int) ($payload['expires_in'] ?? 0);

        if (! $response->successful() || ! is_string($token) || $token === '') {
            throw new SupplierNotConfigured("Reloadly refused the token request (HTTP {$response->status()}).");
        }

        $ttl = max(60, $expiresIn - self::TOKEN_EXPIRY_MARGIN_SECONDS);
        Cache::put($cacheKey, Crypt::encryptString($token), $ttl);

        return $token;
    }

    /**
     * @return array{client_id: string, client_secret: string}
     *
     * @throws SupplierNotConfigured
     */
    private function credentials(): array
    {
        $row = SupplierCredential::query()
            ->where('supplier_id', $this->supplier->id)
            ->where('mode', $this->supplier->mode)
            ->first();

        $values = $row?->credentials;

        if (! is_array($values) || ! is_string($values['client_id'] ?? null) || ! is_string($values['client_secret'] ?? null)
            || $values['client_id'] === '' || $values['client_secret'] === '') {
            throw new SupplierNotConfigured("Supplier {$this->supplier->name} has no credentials for {$this->supplier->mode->value} mode.");
        }

        return ['client_id' => $values['client_id'], 'client_secret' => $values['client_secret']];
    }

    private function http(): PendingRequest
    {
        return Http::connectTimeout(max(1, intdiv($this->supplier->connect_timeout_ms, 1000)))
            ->timeout(max(1, min(60, intdiv($this->supplier->request_timeout_ms, 1000))));
    }

    /**
     * @param  array<array-key, mixed>|null  $request
     * @param  array<array-key, mixed>|null  $response
     */
    private function log(string $operation, string $method, string $url, ?int $status, int $started, bool $succeeded, ?string $errorClass, ?array $request, ?array $response, ?int $supplierTransactionId): void
    {
        $this->logger->record(
            $this->supplier,
            $operation,
            $method,
            $url,
            $status,
            (int) ((hrtime(true) - $started) / 1_000_000),
            $succeeded,
            $errorClass,
            $request,
            $response,
            $supplierTransactionId,
            $this->sensitiveKeys,
        );
    }
}
