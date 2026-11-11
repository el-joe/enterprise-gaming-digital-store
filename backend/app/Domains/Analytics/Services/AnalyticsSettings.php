<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Settings\Services\SettingsService;

/**
 * The `analytics` settings group (admin Settings > Analytics, M18). Public ids (GA4 measurement
 * id, GTM container id, Search Console verification token) may reach the browser; the GA4 API
 * secret, the Search Console client secret and refresh token never do.
 */
class AnalyticsSettings
{
    public const string GROUP = 'analytics';

    public const string SEARCH_CONSOLE_TOKEN = 'search_console_refresh_token';

    public function __construct(private SettingsService $settings) {}

    public function measurementId(): ?string
    {
        return $this->string('ga4_measurement_id');
    }

    public function gtmContainerId(): ?string
    {
        return $this->string('gtm_container_id');
    }

    /**
     * A browser tag is configured (GTM wins over a bare GA4 tag when both are set).
     */
    public function browserTagEnabled(): bool
    {
        return $this->gtmContainerId() !== null || $this->measurementId() !== null;
    }

    /**
     * Ecommerce events pushed to the dataLayer (never `purchase` or `refund`).
     */
    public function browserEventsEnabled(): bool
    {
        return $this->browserTagEnabled() && (bool) $this->settings->get(self::GROUP, 'browser_events', true);
    }

    /**
     * What the storefront page receives: public ids only.
     *
     * @return array{gtm: string|null, ga4: string|null, events: bool}|null
     */
    public function browserConfig(): ?array
    {
        if (! $this->browserTagEnabled()) {
            return null;
        }

        $gtm = $this->gtmContainerId();

        return ['gtm' => $gtm, 'ga4' => $gtm === null ? $this->measurementId() : null, 'events' => $this->browserEventsEnabled()];
    }

    /**
     * Sources the Content Security Policy adds while a tag is configured (Google's documented
     * hosts for GTM and GA4, including the regional collection hosts).
     *
     * @return array{script: list<string>, connect: list<string>, img: list<string>}
     */
    public function cspSources(): array
    {
        if (! $this->browserTagEnabled()) {
            return ['script' => [], 'connect' => [], 'img' => []];
        }

        return [
            'script' => ['https://www.googletagmanager.com'],
            'connect' => ['https://www.googletagmanager.com', 'https://*.google-analytics.com', 'https://*.analytics.google.com'],
            'img' => [],
        ];
    }

    public function serverPurchaseEnabled(): bool
    {
        return (bool) $this->settings->get(self::GROUP, 'server_purchase', true);
    }

    public function apiSecret(): ?string
    {
        return $this->string('ga4_api_secret');
    }

    /**
     * The Measurement Protocol can send: a measurement id and an API secret are stored.
     */
    public function measurementProtocolConfigured(): bool
    {
        return $this->measurementId() !== null && $this->apiSecret() !== null;
    }

    public function measurementProtocolHost(): string
    {
        return $this->settings->get(self::GROUP, 'mp_region', 'global') === 'eu'
            ? 'https://region1.google-analytics.com'
            : 'https://www.google-analytics.com';
    }

    public function faqSchemaEnabled(): bool
    {
        return (bool) $this->settings->get(self::GROUP, 'faq_schema', false);
    }

    public function searchConsoleVerification(): ?string
    {
        return $this->string('search_console_verification');
    }

    public function searchConsoleProperty(): ?string
    {
        return $this->string('search_console_property');
    }

    public function searchConsoleClientId(): ?string
    {
        return $this->string('search_console_client_id');
    }

    public function searchConsoleClientSecret(): ?string
    {
        return $this->string('search_console_client_secret');
    }

    public function searchConsoleRefreshToken(): ?string
    {
        return $this->string(self::SEARCH_CONSOLE_TOKEN);
    }

    public function searchConsoleConnected(): bool
    {
        return $this->searchConsoleRefreshToken() !== null;
    }

    public function searchConsoleReady(): bool
    {
        return $this->searchConsoleConnected() && $this->searchConsoleProperty() !== null
            && $this->searchConsoleClientId() !== null && $this->searchConsoleClientSecret() !== null;
    }

    private function string(string $key): ?string
    {
        $value = $this->settings->get(self::GROUP, $key);

        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }
}
