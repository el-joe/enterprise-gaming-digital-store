<?php

namespace App\Domains\Settings\Services;

/**
 * The app link association files (M-A6, ADR-039), built from `mobile.app_links` only (packages,
 * fingerprints and app ids are the `app_links` admin settings, applied there by SettingsOverlay).
 *
 * Formats (official docs, read 2026-11-10):
 * - Android Digital Asset Links: a JSON array of statements with relation
 *   `delegate_permission/common.handle_all_urls` and target `{namespace: android_app,
 *   package_name, sha256_cert_fingerprints}` (developer.android.com/training/app-links/configure-assetlinks).
 * - Apple: `{"applinks": {"details": [{"appIDs": [...], "components": [{"/": "/app/*"}, ...]}]}}`
 *   (iOS 13 and later format), one component per path in `mobile.app_links.paths`.
 *
 * Malformed configured values are left out rather than published, so a typo can never declare
 * a wrong app. Nothing is ever invented: unconfigured means `[]` and an empty `details` list.
 */
class AppLinkAssociationService
{
    public const string FINGERPRINT = '/^([0-9A-F]{2}:){31}[0-9A-F]{2}$/';

    public const string PACKAGE = '/^[A-Za-z][A-Za-z0-9_]*(\.[A-Za-z][A-Za-z0-9_]*)+$/';

    public const string APPLE_APP_ID = '/^[A-Z0-9]{10}\.[A-Za-z0-9\-]+(\.[A-Za-z0-9\-]+)*$/';

    /**
     * @return list<array{relation: list<string>, target: array{namespace: string, package_name: string, sha256_cert_fingerprints: list<string>}}>
     */
    public function androidStatements(): array
    {
        $fingerprints = $this->listOf('android_sha256_fingerprints', self::FINGERPRINT, uppercase: true);

        if ($fingerprints === []) {
            return [];
        }

        return array_map(static fn (string $package): array => [
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target' => ['namespace' => 'android_app', 'package_name' => $package, 'sha256_cert_fingerprints' => $fingerprints],
        ], $this->listOf('android_packages', self::PACKAGE));
    }

    /**
     * @return array{applinks: array{details: list<array{appIDs: list<string>, components: list<array{'/': string}>}>}}
     */
    public function appleAssociation(): array
    {
        $appIds = $this->listOf('ios_app_ids', self::APPLE_APP_ID);
        $components = array_map(static fn (string $path): array => ['/' => $path], array_values((array) config('mobile.app_links.paths')));

        return ['applinks' => ['details' => $appIds === [] ? [] : [['appIDs' => $appIds, 'components' => $components]]]];
    }

    public function cacheSeconds(): int
    {
        return max(0, (int) config('mobile.app_links.cache_seconds'));
    }

    /**
     * @return list<string>
     */
    private function listOf(string $key, string $pattern, bool $uppercase = false): array
    {
        $values = array_map(static fn (string $value): string => $uppercase ? strtoupper(trim($value)) : trim($value), explode(',', (string) config('mobile.app_links.'.$key)));

        return array_values(array_unique(array_filter($values, static fn (string $value): bool => preg_match($pattern, $value) === 1)));
    }
}
