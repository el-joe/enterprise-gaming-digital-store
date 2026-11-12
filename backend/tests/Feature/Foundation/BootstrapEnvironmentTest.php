<?php

use App\Domains\Settings\Services\SettingDefinitions;

/*
| Owner decision 11: "only the Super Admin and technical bootstrap keys stay in .env". Every other
| runtime value is a database setting (config/admin_settings.php, SettingsOverlay). These tests fail
| as soon as a config file reads, or `.env.example` lists, a key outside the stay list
| (DEPLOYMENT.md 3.1 gives the reason for each).
*/

/**
 * The keys allowed in `.env`: exact names or prefixes ending in `_`.
 *
 * @return list<string>
 */
function bootstrapEnvironmentKeys(): array
{
    return [
        'SUPER_ADMIN_EMAIL', 'SUPER_ADMIN_PASSWORD',
        'APP_KEY', 'APP_PREVIOUS_KEYS', 'APP_ENV', 'APP_DEBUG', 'APP_MAINTENANCE_',
        'DB_', 'MYSQL_ATTR_SSL_CA',
        'CACHE_', 'DYNAMODB_', 'MEMCACHED_', 'REDIS_',
        'SESSION_DRIVER', 'SESSION_CONNECTION', 'SESSION_STORE', 'SESSION_TABLE',
        'QUEUE_', 'BEANSTALKD_', 'SQS_', 'BROADCAST_CONNECTION',
        'LOG_', 'PAPERTRAIL_',
        'BCRYPT_ROUNDS', 'VITE_',
    ];
}

function isBootstrapEnvironmentKey(string $key): bool
{
    foreach (bootstrapEnvironmentKeys() as $allowed) {
        if ($key === $allowed || (str_ends_with($allowed, '_') && str_starts_with($key, $allowed))) {
            return true;
        }
    }

    return false;
}

it('reads only bootstrap keys with env() in the config files', function (): void {
    $outside = [];

    foreach (glob(config_path('*.php')) as $file) {
        $source = (string) file_get_contents($file);

        expect(preg_match('/\benv\(\s*[^\'"\s]/', $source))->toBe(0, basename($file).' calls env() with a computed key');

        preg_match_all('/\benv\(\s*[\'"]([^\'"]+)[\'"]/', $source, $matches);

        foreach ($matches[1] as $key) {
            if (! isBootstrapEnvironmentKey($key)) {
                $outside[] = basename($file).': '.$key;
            }
        }
    }

    expect($outside)->toBe([]);
});

it('never calls env() in application code', function (): void {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS));

    foreach ($files as $file) {
        if ($file->getExtension() === 'php') {
            expect(preg_match('/(?<![:>\w])env\(/', (string) file_get_contents($file->getPathname())))->toBe(0, $file->getPathname());
        }
    }
});

it('lists only bootstrap keys in .env.example', function (): void {
    preg_match_all('/^\s*#?\s*([A-Z][A-Z0-9_]*)=/m', (string) file_get_contents(base_path('.env.example')), $matches);

    expect($matches[1])->not->toBeEmpty()
        ->and(array_values(array_filter($matches[1], static fn (string $key): bool => ! isBootstrapEnvironmentKey($key))))->toBe([]);
});

it('keeps every former project key importable', function (): void {
    $imported = array_filter(array_map(static fn ($definition): ?string => $definition->envKey, app(SettingDefinitions::class)->all()));

    expect($imported)->toContain('DIGITAL_CODE_KEYS', 'DIGITAL_CODE_ACTIVE_KID', 'DIGITAL_CODE_BLIND_INDEX_KEY', 'APP_NAME', 'APP_URL', 'APP_LOCALE', 'APP_FALLBACK_LOCALE', 'APP_FAKER_LOCALE')
        ->toContain('SESSION_LIFETIME', 'SESSION_ENCRYPT', 'SESSION_PATH', 'SESSION_DOMAIN', 'SESSION_SECURE_COOKIE')
        ->toContain('MAIL_MAILER', 'MAIL_SCHEME', 'MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD', 'MAIL_FROM_ADDRESS', 'MAIL_FROM_NAME')
        ->toContain('FILESYSTEM_DISK', 'AWS_ACCESS_KEY_ID', 'AWS_SECRET_ACCESS_KEY', 'AWS_DEFAULT_REGION', 'AWS_BUCKET', 'AWS_USE_PATH_STYLE_ENDPOINT', 'SEO_SITEMAP_DISK')
        ->toContain('MOBILE_ACCESS_TOKEN_TTL_MINUTES', 'MOBILE_REFRESH_TOKEN_TTL_DAYS', 'MOBILE_2FA_CHALLENGE_TTL_SECONDS', 'GUEST_ORDER_TOKEN_TTL_HOURS', 'AFFILIATE_ATTRIBUTION_DAYS', 'BRAND_NAME', 'MEDIA_BASE_URL')
        ->toContain('PAYMENTS_CONNECT_TIMEOUT', 'PAYMENTS_TIMEOUT', 'OPEN_EXCHANGE_RATES_BASE_URL', 'OPEN_EXCHANGE_RATES_CONNECT_TIMEOUT', 'OPEN_EXCHANGE_RATES_TIMEOUT', 'AUTH_PASSWORD_TIMEOUT', 'SANCTUM_TOKEN_PREFIX')
        ->and(array_filter($imported, isBootstrapEnvironmentKey(...)))->toBe([]);
});
