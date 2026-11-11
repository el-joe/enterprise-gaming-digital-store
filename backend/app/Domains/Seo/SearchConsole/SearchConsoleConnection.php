<?php

namespace App\Domains\Seo\SearchConsole;

use App\Domains\Analytics\Services\AnalyticsSettings;
use App\Domains\Audit\DTOs\AuditEntry;
use App\Domains\Audit\Services\AuditLogger;
use App\Domains\Identity\Models\User;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Str;

/**
 * The admin's one-time OAuth consent for Search Console (INTEGRATIONS.md section 7, M18). The
 * state and PKCE verifier live in the staff session for ten minutes; the callback must return the
 * same state. The refresh token is stored encrypted in the `analytics` settings group, is never
 * shown, and can only be replaced (by connecting again) or removed (disconnect, which also asks
 * Google to revoke it). Connecting and disconnecting are audited without the token.
 */
class SearchConsoleConnection
{
    private const string SESSION_KEY = 'admin.search_console.oauth';

    private const int STATE_TTL_SECONDS = 600;

    public function __construct(
        private SearchConsoleClient $client,
        private AnalyticsSettings $analytics,
        private SettingsService $settings,
        private AuditLogger $audit,
    ) {}

    /**
     * @throws SearchConsoleException
     */
    public function start(Session $session, string $redirectUri): string
    {
        if ($this->analytics->searchConsoleClientId() === null || $this->analytics->searchConsoleClientSecret() === null) {
            throw new SearchConsoleException(__('admin.search_console.needs_client'));
        }

        $state = Str::random(40);
        $verifier = Str::random(64);
        $session->put(self::SESSION_KEY, ['state' => $state, 'verifier' => $verifier, 'expires' => now()->addSeconds(self::STATE_TTL_SECONDS)->getTimestamp()]);

        return $this->client->authorizationUrl($redirectUri, $state, $verifier);
    }

    /**
     * @throws SearchConsoleException
     */
    public function complete(Session $session, User $actor, ?string $state, ?string $code, ?string $error, string $redirectUri): void
    {
        $pending = $session->pull(self::SESSION_KEY);

        if (! is_array($pending) || ! is_string($state) || ! hash_equals((string) $pending['state'], $state) || (int) $pending['expires'] < now()->getTimestamp()) {
            throw new SearchConsoleException(__('admin.search_console.bad_state'));
        }

        if ($error !== null || $code === null || $code === '') {
            throw new SearchConsoleException(__('admin.search_console.denied'));
        }

        $refreshToken = $this->client->exchangeCode($code, $redirectUri, (string) $pending['verifier']);
        $this->settings->set(AnalyticsSettings::GROUP, AnalyticsSettings::SEARCH_CONSOLE_TOKEN, $refreshToken, SettingType::String, encrypted: true, actor: $actor);

        $this->audit->record(new AuditEntry(action: 'seo.search_console.connected', after: ['property' => $this->analytics->searchConsoleProperty()]));
    }

    public function disconnect(User $actor): void
    {
        $token = $this->analytics->searchConsoleRefreshToken();

        if ($token !== null) {
            $this->client->revoke($token);
        }

        SearchConsoleClient::forgetAccessToken();
        $this->settings->set(AnalyticsSettings::GROUP, AnalyticsSettings::SEARCH_CONSOLE_TOKEN, null, SettingType::String, encrypted: true, actor: $actor);
        $this->audit->record(new AuditEntry(action: 'seo.search_console.disconnected'));
    }
}
