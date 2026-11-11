<?php

/*
 * Fixtures for the M18 SEO and analytics tests.
 */

use App\Domains\Analytics\Services\AnalyticsSettings;
use App\Domains\Seo\Models\Redirect;
use App\Domains\Seo\Services\RedirectService;
use App\Domains\Settings\Enums\SettingType;
use App\Domains\Settings\Services\SettingsService;
use Illuminate\Testing\TestResponse;

const GA4_API_SECRET_CANARY = 'mp_CANARY_m18_api_secret_never_rendered_4d1e';

const SEARCH_CONSOLE_TOKEN_CANARY = '1//CANARY_m18_refresh_token_never_rendered_8b2f';

/**
 * Store an `analytics` setting the way the admin screen does.
 */
function analyticsSetting(string $key, mixed $value, SettingType $type = SettingType::String, bool $encrypted = false): void
{
    app(SettingsService::class)->set(AnalyticsSettings::GROUP, $key, $value, $type, encrypted: $encrypted);
}

/**
 * GA4 Measurement Protocol configured: measurement id and API secret (the canary).
 */
function configureMeasurementProtocol(string $measurementId = 'G-TEST1234'): void
{
    analyticsSetting('ga4_measurement_id', $measurementId);
    analyticsSetting('ga4_api_secret', GA4_API_SECRET_CANARY, encrypted: true);
}

/**
 * The JSON-LD graph of a page: exactly one `application/ld+json` script carrying the response's
 * CSP nonce, parsed.
 *
 * @return list<array<string, mixed>>
 */
function jsonLdGraph(TestResponse $response): array
{
    preg_match("/'nonce-([^']+)'/", (string) $response->headers->get('Content-Security-Policy'), $nonce);
    preg_match_all('~<script type="application/ld\+json" nonce="([^"]*)">(.*?)</script>~s', $response->getContent(), $scripts, PREG_SET_ORDER);

    expect($scripts)->toHaveCount(1);

    if (isset($nonce[1])) {
        expect($scripts[0][1])->toBe($nonce[1]);
    }

    $data = json_decode($scripts[0][2], true, flags: JSON_THROW_ON_ERROR);

    expect($data['@context'])->toBe('https://schema.org');

    return $data['@graph'];
}

/**
 * The first node of a type in a JSON-LD graph.
 *
 * @param  list<array<string, mixed>>  $graph
 * @return array<string, mixed>|null
 */
function jsonLdNode(array $graph, string $type): ?array
{
    foreach ($graph as $node) {
        if (($node['@type'] ?? null) === $type) {
            return $node;
        }
    }

    return null;
}

/**
 * The analytics events the page hands to the browser (`#analytics-events`), or null when the
 * block is absent.
 *
 * @return list<array{event: string, ecommerce: array<string, mixed>}>|null
 */
function analyticsEvents(TestResponse $response): ?array
{
    if (! preg_match('~<script type="application/json" id="analytics-events" nonce="[^"]*">(.*?)</script>~s', $response->getContent(), $match)) {
        return null;
    }

    return json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
}

function seoRedirect(string $source, ?string $target, int $status = 301, bool $active = true): Redirect
{
    return Redirect::factory()->create([
        'source_path' => RedirectService::normalize($source),
        'source_hash' => RedirectService::hash($source),
        'target_path' => $target,
        'status_code' => $status,
        'is_active' => $active,
    ]);
}
