<?php

namespace App\Domains\Settings\Rules;

use App\Domains\Settings\Services\AppLinkAssociationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The value formats of database settings that replaced `.env` keys (`format` in
 * `config/admin_settings.php`). URLs must be https; http is accepted only while `app.env` is
 * `local`. `$placeholders` must all appear in the value (for example `{order}`).
 *
 * - `url`: an absolute https URL without user info.
 * - `path`: a site path (`/...`, never `//` or a backslash).
 * - `path_or_url`: either of the above.
 * - `app_url`: an app's custom scheme URL (`boxcards://...`) or an https URL.
 * - `header`: an HTTP header name.
 * - `android_packages`, `sha256_fingerprints`, `apple_app_ids`: comma-separated lists, each item
 *   in the format published in the app link association files (AppLinkAssociationService).
 * - `fcm_project_id`: a Firebase (Google Cloud) project id.
 * - `fcm_service_account`: a Google service-account JSON key: `type` service_account,
 *   `project_id`, `client_email`, a usable `private_key` and an https `token_uri` when present.
 * - `host`: a host name or an IP address, without scheme, port or path (SMTP host, EHLO domain).
 * - `cookie_domain`: a cookie domain (a host name, optionally with a leading dot).
 * - `disk`: a configured filesystem disk whose driver is installed (the S3 driver needs
 *   league/flysystem-aws-s3-v3, which is not installed).
 * - `locale_code`: a locale such as en_US or ar_SA (Faker).
 */
class SettingValueFormat implements ValidationRule
{
    private const array FORBIDDEN_SCHEMES = ['javascript', 'data', 'file', 'vbscript', 'ftp'];

    private const string PROJECT_ID = '/^[a-z][a-z0-9-]{4,28}[a-z0-9]$/';

    private const string HOST_NAME = '/^(?=.{1,253}$)([A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?)(\.[A-Za-z0-9]([A-Za-z0-9-]{0,61}[A-Za-z0-9])?)*$/';

    /** The adapter class each filesystem driver needs, when it is not part of the framework. */
    private const array DRIVER_ADAPTERS = ['s3' => 'League\Flysystem\AwsS3V3\AwsS3V3Adapter'];

    /**
     * @param  list<string>  $placeholders
     */
    public function __construct(private string $format, private array $placeholders = []) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! is_string($value) || ! $this->matches(trim($value))) {
            $fail(__('admin.settings.invalid.'.$this->format));

            return;
        }

        foreach ($this->placeholders as $placeholder) {
            if (! str_contains($value, $placeholder)) {
                $fail(__('admin.settings.invalid.placeholder', ['placeholder' => $placeholder]));

                return;
            }
        }
    }

    private function matches(string $value): bool
    {
        return match ($this->format) {
            'url' => self::isUrl($value),
            'path' => self::isPath($value),
            'path_or_url' => self::isPath($value) || self::isUrl($value),
            'app_url' => self::isAppUrl($value),
            'header' => preg_match('/^[A-Za-z0-9][A-Za-z0-9-]{0,63}$/', $value) === 1,
            'android_packages' => self::isList($value, AppLinkAssociationService::PACKAGE),
            'sha256_fingerprints' => self::isList(strtoupper($value), AppLinkAssociationService::FINGERPRINT),
            'apple_app_ids' => self::isList($value, AppLinkAssociationService::APPLE_APP_ID),
            'fcm_project_id' => preg_match(self::PROJECT_ID, $value) === 1,
            'fcm_service_account' => self::isServiceAccount($value),
            'host' => preg_match(self::HOST_NAME, $value) === 1 || filter_var($value, FILTER_VALIDATE_IP) !== false,
            'cookie_domain' => preg_match(self::HOST_NAME, ltrim($value, '.')) === 1,
            'disk' => self::isUsableDisk($value),
            'locale_code' => preg_match('/^[a-z]{2,3}(_[A-Z]{2})?$/', $value) === 1,
            default => false,
        };
    }

    public static function isUrl(string $value): bool
    {
        if (preg_match('~[\s\\\\]~', $value) === 1) {
            return false;
        }

        $parts = parse_url($value);

        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host']) || isset($parts['user']) || isset($parts['pass'])) {
            return false;
        }

        $scheme = strtolower($parts['scheme']);

        return $scheme === 'https' || ($scheme === 'http' && config('app.env') === 'local');
    }

    public static function isPath(string $value): bool
    {
        return preg_match('~^/(?![/\\\\])[^\s\\\\]*$~', $value) === 1;
    }

    private static function isAppUrl(string $value): bool
    {
        if (preg_match('~^([a-z][a-z0-9+.-]*)://[^\s\\\\]+$~i', $value, $match) !== 1) {
            return false;
        }

        $scheme = strtolower($match[1]);

        if (in_array($scheme, ['http', 'https'], true)) {
            return self::isUrl($value);
        }

        return ! in_array($scheme, self::FORBIDDEN_SCHEMES, true);
    }

    private static function isList(string $value, string $pattern): bool
    {
        $items = array_map('trim', explode(',', $value));

        foreach ($items as $item) {
            if (preg_match($pattern, $item) !== 1) {
                return false;
            }
        }

        return $items !== [];
    }

    private static function isUsableDisk(string $value): bool
    {
        $driver = config("filesystems.disks.{$value}.driver");

        if (! is_string($driver)) {
            return false;
        }

        return ! isset(self::DRIVER_ADAPTERS[$driver]) || class_exists(self::DRIVER_ADAPTERS[$driver]);
    }

    private static function isServiceAccount(string $value): bool
    {
        $json = json_decode($value, true, 16);

        if (! is_array($json) || ($json['type'] ?? null) !== 'service_account') {
            return false;
        }

        if (! is_string($json['project_id'] ?? null) || preg_match(self::PROJECT_ID, $json['project_id']) !== 1) {
            return false;
        }

        if (! is_string($json['client_email'] ?? null) || filter_var($json['client_email'], FILTER_VALIDATE_EMAIL) === false) {
            return false;
        }

        if (isset($json['token_uri']) && (! is_string($json['token_uri']) || ! str_starts_with($json['token_uri'], 'https://'))) {
            return false;
        }

        return is_string($json['private_key'] ?? null)
            && str_contains($json['private_key'], 'PRIVATE KEY')
            && openssl_pkey_get_private($json['private_key']) !== false;
    }
}
