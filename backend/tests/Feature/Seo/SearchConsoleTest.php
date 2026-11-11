<?php

/**
 * M18 Search Console (INTEGRATIONS.md section 7): admin OAuth connect with state and PKCE, the
 * refresh token stored encrypted and write-only, disconnect with revocation, the reporting
 * fetch into `search_console_metrics` and the admin report. Google is `Http::fake` only
 * (awaiting credentials).
 */

use App\Domains\Analytics\Models\SearchConsoleMetric;
use App\Domains\Analytics\Services\AnalyticsSettings;
use App\Domains\Audit\Models\AuditLog;
use App\Domains\Seo\SearchConsole\SearchConsoleClient;
use App\Domains\Settings\Models\Setting;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;

const SEARCH_CONSOLE_CLIENT_SECRET_CANARY = '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c';

function configureSearchConsoleClient(): void
{
    analyticsSetting('search_console_property', 'sc-domain:boxcards.shop');
    analyticsSetting('search_console_client_id', '123-abc.apps.googleusercontent.com');
    analyticsSetting('search_console_client_secret', SEARCH_CONSOLE_CLIENT_SECRET_CANARY, encrypted: true);
}

function connectSearchConsole(): void
{
    configureSearchConsoleClient();
    analyticsSetting(AnalyticsSettings::SEARCH_CONSOLE_TOKEN, SEARCH_CONSOLE_TOKEN_CANARY, encrypted: true);
}

beforeEach(function (): void {
    Http::preventStrayRequests();
});

describe('OAuth connection', function (): void {
    it('sends the admin to Google with the read-only scope, offline access, state and PKCE', function (): void {
        configureSearchConsoleClient();
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical']);

        $location = $this->get('/admin/seo/search-console/connect')->assertRedirect()->headers->get('Location');
        parse_str((string) parse_url($location, PHP_URL_QUERY), $query);
        $pending = session('admin.search_console.oauth');

        expect($location)->toStartWith(SearchConsoleClient::AUTHORIZE_URL.'?')
            ->and($query)->toMatchArray([
                'client_id' => '123-abc.apps.googleusercontent.com',
                'redirect_uri' => route('admin.content.search-console.callback'),
                'response_type' => 'code',
                'scope' => 'https://www.googleapis.com/auth/webmasters.readonly',
                'access_type' => 'offline',
                'prompt' => 'consent',
                'state' => $pending['state'],
                'code_challenge_method' => 'S256',
                'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $pending['verifier'], true)), '+/', '-_'), '='),
            ])
            ->and($location)->not->toContain(SEARCH_CONSOLE_CLIENT_SECRET_CANARY);
    });

    it('asks for the client id and secret before connecting', function (): void {
        adminWith(['settings.view', 'settings.manage_critical']);

        $this->get('/admin/seo/search-console/connect')->assertRedirect(route('admin.settings.show', 'analytics'))
            ->assertSessionHas('search_console_error');
    });

    it('stores the refresh token encrypted and never shows it', function (): void {
        configureSearchConsoleClient();
        adminWith(['settings.view', 'settings.manage', 'settings.manage_critical']);
        Http::fake([SearchConsoleClient::TOKEN_URL => Http::response(['access_token' => 'ya29.access', 'expires_in' => 3599, 'refresh_token' => SEARCH_CONSOLE_TOKEN_CANARY, 'scope' => SearchConsoleClient::SCOPE, 'token_type' => 'Bearer'])]);

        $this->get('/admin/seo/search-console/connect');
        $pending = session('admin.search_console.oauth');

        $this->get('/admin/seo/search-console/callback?state='.$pending['state'].'&code=4/abc')
            ->assertRedirect(route('admin.settings.show', 'analytics'))
            ->assertSessionHas('search_console_status');

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === SearchConsoleClient::TOKEN_URL
            && $request['grant_type'] === 'authorization_code' && $request['code'] === '4/abc'
            && $request['code_verifier'] === $pending['verifier'] && $request['client_secret'] === SEARCH_CONSOLE_CLIENT_SECRET_CANARY);

        $stored = Setting::query()->where('group', 'analytics')->where('key', AnalyticsSettings::SEARCH_CONSOLE_TOKEN)->sole();
        expect($stored->is_encrypted)->toBeTrue()
            ->and($stored->value)->not->toContain(SEARCH_CONSOLE_TOKEN_CANARY)
            ->and(app(AnalyticsSettings::class)->searchConsoleRefreshToken())->toBe(SEARCH_CONSOLE_TOKEN_CANARY)
            ->and(AuditLog::query()->where('action', 'seo.search_console.connected')->exists())->toBeTrue()
            ->and(AuditLog::query()->get()->toJson())->not->toContain(SEARCH_CONSOLE_TOKEN_CANARY);

        $page = $this->get('/admin/settings/analytics')->assertOk()->assertSee('Connected')->assertSee(route('admin.content.search-console.callback'));
        expect($page->getContent())->not->toContain(SEARCH_CONSOLE_TOKEN_CANARY)->not->toContain(SEARCH_CONSOLE_CLIENT_SECRET_CANARY);
    });

    it('refuses a callback with a wrong, missing or reused state, or a denied consent', function (): void {
        configureSearchConsoleClient();
        adminWith(['settings.view', 'settings.manage_critical']);
        Http::fake();

        $this->get('/admin/seo/search-console/callback?state=forged&code=4/abc')->assertSessionHas('search_console_error');

        $this->get('/admin/seo/search-console/connect');
        $state = session('admin.search_console.oauth')['state'];
        $this->get('/admin/seo/search-console/callback?state=forged&code=4/abc')->assertSessionHas('search_console_error');
        $this->get('/admin/seo/search-console/callback?state='.$state.'&code=4/abc')->assertSessionHas('search_console_error');

        $this->get('/admin/seo/search-console/connect');
        $state = session('admin.search_console.oauth')['state'];
        $this->get('/admin/seo/search-console/callback?state='.$state.'&error=access_denied')->assertSessionHas('search_console_error');

        Http::assertNothingSent();
        expect(app(AnalyticsSettings::class)->searchConsoleConnected())->toBeFalse();
    });

    it('needs settings.manage_critical to connect or disconnect, and seo.manage for the report', function (): void {
        adminWith(['settings.view', 'settings.manage']);
        $this->get('/admin/seo/search-console/connect')->assertForbidden();
        $this->get('/admin/seo/search-console/callback?state=x&code=y')->assertForbidden();
        $this->deleteJson('/admin/api/seo/search-console/connection')->assertForbidden();
        $this->get('/admin/seo/search-console')->assertForbidden();
        $this->postJson('/admin/api/seo/search-console/sync')->assertForbidden();
    });

    it('revokes and deletes the token on disconnect', function (): void {
        connectSearchConsole();
        adminWith(['settings.view', 'settings.manage_critical']);
        Http::fake([SearchConsoleClient::REVOKE_URL => Http::response('', 200)]);

        $this->deleteJson('/admin/api/seo/search-console/connection')->assertOk()->assertJsonPath('data.connected', false);

        Http::assertSent(fn (HttpRequest $request): bool => $request->url() === SearchConsoleClient::REVOKE_URL && $request['token'] === SEARCH_CONSOLE_TOKEN_CANARY);
        expect(app(AnalyticsSettings::class)->searchConsoleConnected())->toBeFalse()
            ->and(AuditLog::query()->where('action', 'seo.search_console.disconnected')->exists())->toBeTrue();
    });
});

describe('reporting', function (): void {
    it('fetches totals, queries and pages into search_console_metrics, idempotently', function (): void {
        connectSearchConsole();
        $day = now()->subDays(3)->toDateString();
        Http::fake([
            SearchConsoleClient::TOKEN_URL => Http::response(['access_token' => 'ya29.fresh', 'expires_in' => 3599]),
            'www.googleapis.com/webmasters/v3/sites/*' => function (HttpRequest $request) use ($day) {
                return Http::response(['rows' => match ($request['dimensions']) {
                    ['date'] => [['keys' => [$day], 'clicks' => 40, 'impressions' => 1000, 'ctr' => 0.04, 'position' => 7.5]],
                    ['date', 'query'] => [['keys' => [$day, 'steam gift card'], 'clicks' => 30, 'impressions' => 400, 'ctr' => 0.075, 'position' => 3.2]],
                    ['date', 'page'] => [['keys' => [$day, 'https://boxcards.shop/gaming/steam'], 'clicks' => 25, 'impressions' => 300, 'ctr' => 0.0833, 'position' => 4.1]],
                }]);
            },
        ]);

        $this->artisan('seo:fetch-search-console --days=3')->expectsOutputToContain('Stored 3 Search Console rows.')->assertSuccessful();
        $this->artisan('seo:fetch-search-console --days=3')->assertSuccessful();

        expect(SearchConsoleMetric::query()->count())->toBe(3)
            ->and(SearchConsoleMetric::query()->where('dimension', 'query')->sole())->toMatchArray(['key_value' => 'steam gift card', 'clicks' => 30, 'impressions' => 400]);

        Http::assertSent(fn (HttpRequest $request): bool => str_starts_with($request->url(), 'https://www.googleapis.com/webmasters/v3/sites/sc-domain%3Aboxcards.shop/searchAnalytics/query')
            && $request->hasHeader('Authorization', 'Bearer ya29.fresh')
            && $request['dataState'] === 'final' && $request['type'] === 'web'
            && $request['endDate'] === now()->subDays(3)->toDateString());
        // One token refresh serves every query (cached until shortly before expiry).
        expect(Http::recorded(fn (HttpRequest $request): bool => $request->url() === SearchConsoleClient::TOKEN_URL))->toHaveCount(1);
    });

    it('does nothing while awaiting credentials', function (): void {
        $this->artisan('seo:fetch-search-console')->expectsOutputToContain('awaiting credentials')->assertSuccessful();
        Http::assertNothingSent();
    });

    it('reports an expired consent as an error without a token in the message', function (): void {
        connectSearchConsole();
        adminWith(['seo.manage']);
        Http::fake([SearchConsoleClient::TOKEN_URL => Http::response(['error' => 'invalid_grant'], 400)]);

        $response = $this->postJson('/admin/api/seo/search-console/sync')->assertStatus(502)->assertJsonPath('code', 'search_console_unavailable');
        expect($response->getContent())->not->toContain(SEARCH_CONSOLE_TOKEN_CANARY)->not->toContain(SEARCH_CONSOLE_CLIENT_SECRET_CANARY);
    });

    it('shows clicks, impressions, CTR, position, top queries and pages, in English and Arabic', function (): void {
        adminWith(['seo.manage']);
        $day = now()->subDays(3)->toDateString();
        SearchConsoleMetric::factory()->create(['date' => $day, 'dimension' => 'total', 'key_hash' => hash('sha256', 'total'), 'key_value' => 'total', 'clicks' => 40, 'impressions' => 1000, 'ctr' => '0.040000', 'position' => '7.500']);
        SearchConsoleMetric::factory()->create(['date' => $day, 'dimension' => 'query', 'key_hash' => hash('sha256', 'steam gift card'), 'key_value' => 'steam gift card', 'clicks' => 30, 'impressions' => 400, 'ctr' => '0.075000', 'position' => '3.200']);
        SearchConsoleMetric::factory()->create(['date' => $day, 'dimension' => 'page', 'key_hash' => hash('sha256', 'p'), 'key_value' => 'https://boxcards.shop/gaming/steam', 'clicks' => 25, 'impressions' => 300, 'ctr' => '0.083300', 'position' => '4.100']);

        $page = $this->get('/admin/seo/search-console?days=28')->assertOk()
            ->assertSee('Top queries')->assertSee('steam gift card')->assertSee('https://boxcards.shop/gaming/steam')
            ->assertSee('4.00%')->assertSee('7.5')->assertSee('1,000')
            ->assertSee('does not guarantee all data');
        assertCspSafeHtml($page);

        $this->withSession(['admin.locale' => 'ar'])->get('/admin/seo/search-console')->assertOk()->assertSee('أهم عبارات البحث');
        $this->get('/admin/seo/search-console?days=5')->assertRedirect();
    });

    it('says the report awaits credentials when nothing was fetched', function (): void {
        adminWith(['seo.manage']);

        $this->get('/admin/seo/search-console')->assertOk()->assertSee('Awaiting credentials');
    });
});
