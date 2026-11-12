<?php

namespace App\Domains\Notifications\Services;

use App\Domains\Notifications\DTOs\PushDeliveryResult;
use App\Domains\Notifications\DTOs\PushMessage;
use App\Domains\Notifications\Enums\PushDeliveryOutcome;
use App\Domains\Notifications\Exceptions\PushNotConfigured;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Firebase Cloud Messaging HTTP v1 sender (M-A18, owner decision 9, ADR-042), on the Laravel HTTP
 * client with no SDK (backend/CLAUDE.md: no new dependency).
 *
 * Authentication is the OAuth2 service-account flow: a JWT (RS256, signed with the service
 * account's private key through openssl) is exchanged at the key's `token_uri` for an access token
 * with the `firebase.messaging` scope. The access token is cached encrypted until five minutes
 * before it expires, and forgotten when FCM answers 401.
 *
 * FCM answers are mapped to {@see PushDeliveryOutcome}: `UNREGISTERED` (404 with that code), an invalid
 * registration token (400 `INVALID_ARGUMENT` about the token) and `SENDER_ID_MISMATCH` (403) mean
 * the token must be deleted; 429, 5xx, 401 and network failures are retried; anything else fails.
 * Tokens, keys and access tokens are never logged.
 *
 * The service-account JSON key is the encrypted admin setting `push.fcm_service_account`
 * (Settings > Push, write-only), decrypted only here; the project id is `push.fcm.project_id`
 * (admin setting push.fcm_project_id), else the key's own `project_id`.
 */
class FcmClient
{
    private const int TOKEN_LIFETIME_SECONDS = 3600;

    private const int TOKEN_EXPIRY_MARGIN_SECONDS = 300;

    private const string DEFAULT_TOKEN_URI = 'https://oauth2.googleapis.com/token';

    public const string SETTINGS_GROUP = 'push';

    public const string SERVICE_ACCOUNT_KEY = 'fcm_service_account';

    public function __construct(private SettingsService $settings) {}

    public function send(string $deviceToken, PushMessage $message): PushDeliveryResult
    {
        $credentials = $this->credentials();

        try {
            $response = $this->http()
                ->withToken($this->accessToken($credentials))
                ->post($this->sendUrl($credentials), $this->payload($deviceToken, $message));
        } catch (ConnectionException) {
            Log::warning('push.fcm.unreachable', ['type' => $message->type]);

            return new PushDeliveryResult(PushDeliveryOutcome::Retry, error: 'connection');
        }

        return $this->result($response, $credentials);
    }

    /**
     * The FCM v1 request body. `notification` is shown by the system when the app is in the
     * background; `data` carries the routing ids (strings only).
     *
     * @return array{message: array<string, mixed>}
     */
    public function payload(string $deviceToken, PushMessage $message): array
    {
        $tag = ($message->data['link_type'] ?? 'inbox').'-'.($message->data['link_value'] ?? $message->type);

        return ['message' => [
            'token' => $deviceToken,
            'notification' => ['title' => $message->title, 'body' => $message->body],
            'data' => $message->data,
            'android' => [
                'priority' => 'high',
                'notification' => ['channel_id' => (string) config('push.android_channel_id'), 'tag' => $tag],
            ],
            'apns' => [
                'headers' => ['apns-priority' => '10'],
                'payload' => ['aps' => ['sound' => 'default', 'thread-id' => $tag]],
            ],
        ]];
    }

    /**
     * The signed JWT assertion of the service-account flow (RFC 7523): RS256 over
     * `base64url(header).base64url(claims)`.
     *
     * @param  array{client_email: string, private_key: string, token_uri?: string, project_id?: string}  $credentials
     *
     * @throws PushNotConfigured
     */
    public function assertion(array $credentials, int $issuedAt): string
    {
        $header = self::base64Url((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
        $claims = self::base64Url((string) json_encode([
            'iss' => $credentials['client_email'],
            'scope' => (string) config('push.fcm.scope'),
            'aud' => $credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI,
            'iat' => $issuedAt,
            'exp' => $issuedAt + self::TOKEN_LIFETIME_SECONDS,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        $key = openssl_pkey_get_private($credentials['private_key']);

        if ($key === false || ! openssl_sign($header.'.'.$claims, $signature, $key, OPENSSL_ALGO_SHA256)) {
            throw new PushNotConfigured('The FCM service-account private key cannot sign.');
        }

        return $header.'.'.$claims.'.'.self::base64Url($signature);
    }

    /**
     * The service-account key stored encrypted in the settings (`push.fcm_service_account`).
     *
     * @return array{client_email: string, private_key: string, token_uri?: string, project_id?: string}
     *
     * @throws PushNotConfigured
     */
    public function credentials(): array
    {
        try {
            $stored = $this->settings->get(self::SETTINGS_GROUP, self::SERVICE_ACCOUNT_KEY);
        } catch (Throwable) {
            throw new PushNotConfigured('The FCM service-account key cannot be decrypted.');
        }

        if (! is_string($stored) || $stored === '') {
            throw new PushNotConfigured('FCM is enabled but no service-account key is stored (Settings > Push).');
        }

        try {
            $json = json_decode($stored, true, 16, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            throw new PushNotConfigured('The FCM service-account key is not valid JSON.');
        }

        if (! is_array($json) || ! is_string($json['client_email'] ?? null) || ! is_string($json['private_key'] ?? null)) {
            throw new PushNotConfigured('The FCM service-account key has no client_email or private_key.');
        }

        if ($this->projectId($json) === '') {
            throw new PushNotConfigured('FCM is enabled but no Firebase project id is set (Settings > Push).');
        }

        $tokenUri = $json['token_uri'] ?? self::DEFAULT_TOKEN_URI;

        if (! is_string($tokenUri) || ! str_starts_with($tokenUri, 'https://')) {
            throw new PushNotConfigured('The FCM service-account token_uri must be https.');
        }

        return ['client_email' => $json['client_email'], 'private_key' => $json['private_key'], 'token_uri' => $tokenUri, 'project_id' => $this->projectId($json)];
    }

    /**
     * @param  array{client_email: string, private_key: string, token_uri?: string, project_id?: string}  $credentials
     *
     * @throws PushNotConfigured
     */
    private function accessToken(array $credentials): string
    {
        $cacheKey = self::cacheKey($credentials);
        $cached = Cache::get($cacheKey);

        if (is_string($cached)) {
            try {
                return Crypt::decryptString($cached);
            } catch (Throwable) {
                Cache::forget($cacheKey);
            }
        }

        $response = $this->http()->asForm()->post($credentials['token_uri'] ?? self::DEFAULT_TOKEN_URI, [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $this->assertion($credentials, now()->getTimestamp()),
        ]);

        $token = $response->json('access_token');

        if (! $response->successful() || ! is_string($token) || $token === '') {
            Log::error('push.fcm.oauth_failed', ['status' => $response->status(), 'error' => $response->json('error')]);

            throw new PushNotConfigured('FCM OAuth token request failed with HTTP '.$response->status().'.');
        }

        $ttl = max(60, (int) $response->json('expires_in', self::TOKEN_LIFETIME_SECONDS) - self::TOKEN_EXPIRY_MARGIN_SECONDS);
        Cache::put($cacheKey, Crypt::encryptString($token), $ttl);

        return $token;
    }

    /**
     * @param  array{client_email: string, private_key: string, token_uri?: string, project_id?: string}  $credentials
     */
    private function result(Response $response, array $credentials): PushDeliveryResult
    {
        if ($response->successful()) {
            return new PushDeliveryResult(PushDeliveryOutcome::Sent);
        }

        $status = $response->status();
        $errorCode = $this->fcmErrorCode($response);
        $message = (string) $response->json('error.message', '');
        $retryAfter = is_numeric($response->header('Retry-After')) ? (int) $response->header('Retry-After') : null;

        $outcome = match (true) {
            // A bare 404 (a wrong project id, for example) must not delete every token.
            $errorCode === 'UNREGISTERED' => PushDeliveryOutcome::TokenInvalid,
            $errorCode === 'SENDER_ID_MISMATCH' => PushDeliveryOutcome::TokenInvalid,
            $errorCode === 'INVALID_ARGUMENT' && stripos($message, 'registration token') !== false => PushDeliveryOutcome::TokenInvalid,
            $status === 401 => PushDeliveryOutcome::Retry,
            $status === 429, $status >= 500 => PushDeliveryOutcome::Retry,
            default => PushDeliveryOutcome::Failed,
        };

        if ($status === 401) {
            Cache::forget(self::cacheKey($credentials));
        }

        if ($outcome === PushDeliveryOutcome::Failed) {
            Log::error('push.fcm.refused', ['status' => $status, 'error' => $errorCode]);
        }

        return new PushDeliveryResult($outcome, $retryAfter, $errorCode ?? (string) $status);
    }

    /**
     * The FCM error code from `error.details[].errorCode` (type `google.firebase.fcm.v1.FcmError`),
     * else the Google status (`error.status`).
     */
    private function fcmErrorCode(Response $response): ?string
    {
        foreach ((array) $response->json('error.details', []) as $detail) {
            if (is_array($detail) && is_string($detail['errorCode'] ?? null)) {
                return $detail['errorCode'];
            }
        }

        $status = $response->json('error.status');

        return is_string($status) ? $status : null;
    }

    /**
     * @param  array{project_id?: string}  $credentials
     */
    private function sendUrl(array $credentials): string
    {
        return rtrim((string) config('push.fcm.base_url'), '/').'/projects/'.rawurlencode((string) ($credentials['project_id'] ?? config('push.fcm.project_id'))).'/messages:send';
    }

    /**
     * @param  array<string, mixed>  $json
     */
    private function projectId(array $json): string
    {
        $configured = config('push.fcm.project_id');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return is_string($json['project_id'] ?? null) ? $json['project_id'] : '';
    }

    private function http(): PendingRequest
    {
        return Http::acceptJson()
            ->connectTimeout((int) config('push.fcm.connect_timeout'))
            ->timeout((int) config('push.fcm.timeout'));
    }

    /**
     * @param  array{client_email: string, private_key: string, token_uri?: string, project_id?: string}  $credentials
     */
    private static function cacheKey(array $credentials): string
    {
        return 'push:fcm:access-token:'.hash('sha256', $credentials['client_email'].'|'.($credentials['project_id'] ?? config('push.fcm.project_id')));
    }

    private static function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
