<?php

namespace App\Domains\Seo\SearchConsole;

use App\Domains\Analytics\Services\AnalyticsSettings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Google Search Console API with OAuth 2.0 (INTEGRATIONS.md section 7, M18) on the Laravel HTTP
 * client (no Google SDK): the authorization URL (offline access, PKCE S256, read-only scope),
 * the code exchange, access tokens from the stored refresh token (cached encrypted until shortly
 * before they expire), token revocation and `searchAnalytics.query`. Awaiting credentials:
 * tested with `Http::fake` only, never called live.
 */
class SearchConsoleClient
{
    public const string AUTHORIZE_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    public const string TOKEN_URL = 'https://oauth2.googleapis.com/token';

    public const string REVOKE_URL = 'https://oauth2.googleapis.com/revoke';

    public const string API_URL = 'https://www.googleapis.com/webmasters/v3/sites/';

    public const string SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private const string ACCESS_TOKEN_CACHE = 'seo:search-console:access-token';

    public function __construct(private AnalyticsSettings $settings) {}

    public function authorizationUrl(string $redirectUri, string $state, string $codeVerifier): string
    {
        return self::AUTHORIZE_URL.'?'.http_build_query([
            'client_id' => (string) $this->settings->searchConsoleClientId(),
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $codeVerifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ]);
    }

    /**
     * Exchange an authorization code for the refresh token.
     *
     * @throws SearchConsoleException
     */
    public function exchangeCode(string $code, string $redirectUri, string $codeVerifier): string
    {
        $body = $this->token([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'code_verifier' => $codeVerifier,
        ]);

        $refreshToken = $body['refresh_token'] ?? null;

        if (! is_string($refreshToken) || $refreshToken === '') {
            throw new SearchConsoleException('Google returned no refresh token; connect again and grant offline access.');
        }

        if (! str_contains((string) ($body['scope'] ?? self::SCOPE), self::SCOPE)) {
            throw new SearchConsoleException('Google did not grant the Search Console read-only scope.');
        }

        $this->rememberAccessToken($body);

        return $refreshToken;
    }

    /**
     * @throws SearchConsoleException
     */
    public function accessToken(): string
    {
        $cached = Cache::get(self::ACCESS_TOKEN_CACHE);

        if (is_string($cached)) {
            return Crypt::decryptString($cached);
        }

        $refreshToken = $this->settings->searchConsoleRefreshToken() ?? throw new SearchConsoleException('Search Console is not connected.');
        $body = $this->token(['grant_type' => 'refresh_token', 'refresh_token' => $refreshToken]);

        return $this->rememberAccessToken($body);
    }

    public function revoke(string $refreshToken): void
    {
        try {
            Http::asForm()->connectTimeout(5)->timeout(10)->post(self::REVOKE_URL, ['token' => $refreshToken]);
        } catch (ConnectionException) {
            // Best effort: the stored token is deleted whatever Google answers.
        }

        self::forgetAccessToken();
    }

    public static function forgetAccessToken(): void
    {
        Cache::forget(self::ACCESS_TOKEN_CACHE);
    }

    /**
     * `searchAnalytics.query` for the configured property.
     *
     * @param  list<string>  $dimensions
     * @return list<array{keys: list<string>, clicks: int|float, impressions: int|float, ctr: int|float, position: int|float}>
     *
     * @throws SearchConsoleException
     */
    public function query(string $startDate, string $endDate, array $dimensions, int $rowLimit = 25000): array
    {
        $property = $this->settings->searchConsoleProperty() ?? throw new SearchConsoleException('No Search Console property is set.');

        $response = $this->call(fn (): Response => Http::withToken($this->accessToken())->acceptJson()->asJson()->connectTimeout(5)->timeout(30)
            ->post(self::API_URL.rawurlencode($property).'/searchAnalytics/query', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'dimensions' => $dimensions,
                'type' => 'web',
                'rowLimit' => min(25000, max(1, $rowLimit)),
                'startRow' => 0,
                'dataState' => 'final',
            ]));

        return array_values(array_filter((array) $response->json('rows', []), 'is_array'));
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     *
     * @throws SearchConsoleException
     */
    private function token(array $fields): array
    {
        $clientId = $this->settings->searchConsoleClientId();
        $clientSecret = $this->settings->searchConsoleClientSecret();

        if ($clientId === null || $clientSecret === null) {
            throw new SearchConsoleException('The Search Console OAuth client id and secret are not set.');
        }

        $response = $this->call(static fn (): Response => Http::asForm()->acceptJson()->connectTimeout(5)->timeout(15)
            ->post(self::TOKEN_URL, [...$fields, 'client_id' => $clientId, 'client_secret' => $clientSecret]));

        return (array) $response->json();
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function rememberAccessToken(array $body): string
    {
        $token = $body['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new SearchConsoleException('Google returned no access token.');
        }

        $ttl = max(60, (int) ($body['expires_in'] ?? 3600) - 120);
        Cache::put(self::ACCESS_TOKEN_CACHE, Crypt::encryptString($token), $ttl);

        return $token;
    }

    /**
     * @param  callable(): Response  $send
     *
     * @throws SearchConsoleException
     */
    private function call(callable $send): Response
    {
        try {
            $response = $send();
        } catch (ConnectionException) {
            throw new SearchConsoleException('Google could not be reached.');
        }

        if (! $response->successful()) {
            if ($response->status() === 401) {
                self::forgetAccessToken();
            }

            $error = $response->json('error');
            $code = is_array($error) ? (string) ($error['status'] ?? $error['code'] ?? '') : (string) ($error ?? '');

            throw new SearchConsoleException(trim('Google answered HTTP '.$response->status().' '.preg_replace('/[^A-Za-z0-9_ ]/', '', $code)));
        }

        return $response;
    }
}
