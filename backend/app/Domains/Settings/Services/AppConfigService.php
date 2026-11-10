<?php

namespace App\Domains\Settings\Services;

use App\Domains\Catalog\Services\StorefrontCatalogService;
use App\Domains\Cms\Services\MediaUrls;
use App\Domains\Currency\Services\ExchangeRateRegistry;
use App\Domains\Payments\Services\PaymentPublicCredentials;
use App\Domains\Settings\DTOs\AppConfig;
use App\Domains\Settings\DTOs\AppUpdateStatus;
use App\Domains\Settings\Enums\AppConfigSetting;
use App\Domains\Shared\Exceptions\ApplicationException;
use App\Domains\Shared\ValueObjects\SemanticVersion;

/**
 * Builds `GET /api/v1/app/config` (M-A4, gap G9) from the settings table (group `app`, editable
 * without a deploy) with `config('mobile.app')` as the default, plus active currencies and the
 * publishable provider identifiers. Secrets are never read here except through
 * PaymentPublicCredentials, which returns only the allow-listed public field.
 */
class AppConfigService
{
    public const array PLATFORMS = ['android', 'ios'];

    public function __construct(
        private SettingsService $settings,
        private StorefrontCatalogService $catalog,
        private ExchangeRateRegistry $registry,
        private MediaUrls $media,
        private PaymentPublicCredentials $payments,
    ) {}

    public function build(?string $platform = null, ?string $installedVersion = null): AppConfig
    {
        $versions = [];

        foreach (self::PLATFORMS as $name) {
            $versions[$name] = [
                'min_supported_version' => $this->version(AppConfigSetting::from("{$name}_min_version")),
                'latest_version' => $this->version(AppConfigSetting::from("{$name}_latest_version")),
            ];
        }

        $maintenance = (bool) $this->value(AppConfigSetting::Maintenance);
        $features = [];

        foreach ([AppConfigSetting::CheckoutEnabled, AppConfigSetting::GuestCheckoutEnabled, AppConfigSetting::WalletEnabled, AppConfigSetting::ReviewsEnabled, AppConfigSetting::SupportEnabled] as $flag) {
            $features[$flag->value] = (bool) $this->value($flag);
        }

        $links = [];

        foreach (['support' => AppConfigSetting::SupportUrl, 'terms' => AppConfigSetting::TermsUrl, 'privacy' => AppConfigSetting::PrivacyUrl, 'refund' => AppConfigSetting::RefundUrl] as $name => $setting) {
            $links[$name] = $this->url($this->value($setting));
        }

        return new AppConfig(
            versions: $versions,
            update: $platform === null || $installedVersion === null ? null : $this->updateStatus($versions[$platform], $platform, $installedVersion),
            maintenance: $maintenance,
            maintenanceMessage: $maintenance ? $this->maintenanceMessage() : null,
            features: $features,
            locales: array_values((array) config('app.supported_locales')),
            defaultLocale: (string) config('app.fallback_locale'),
            baseCurrency: $this->registry->baseCurrency(),
            currencies: $this->catalog->currencies(),
            mediaBaseUrl: $this->media->baseUrl(),
            links: $links,
            paymentGateways: $this->payments->forActiveGateways(),
        );
    }

    /**
     * Save one app setting (operator command). Values are validated per key.
     *
     * @throws ApplicationException
     */
    public function set(AppConfigSetting $setting, string $raw): void
    {
        $parsed = $setting->parse($raw);

        if ($parsed['error'] !== null) {
            throw new ApplicationException('The value is not valid.', 'validation_failed', 422, ['value' => [$parsed['error']]]);
        }

        $this->settings->set(AppConfigSetting::GROUP, $setting->value, $parsed['value'], $setting->type());
    }

    public function value(AppConfigSetting $setting): mixed
    {
        return $this->settings->get(AppConfigSetting::GROUP, $setting->value, $setting->default());
    }

    /**
     * @param  array{min_supported_version: string, latest_version: string}  $versions
     */
    private function updateStatus(array $versions, string $platform, string $installedVersion): AppUpdateStatus
    {
        $installed = SemanticVersion::of($installedVersion);

        return new AppUpdateStatus(
            platform: $platform,
            version: $installedVersion,
            required: $installed->lessThan(SemanticVersion::of($versions['min_supported_version'])),
            available: $installed->lessThan(SemanticVersion::of($versions['latest_version'])),
        );
    }

    /**
     * A stored version that is not SemVer (edited outside the command) falls back to the default.
     */
    private function version(AppConfigSetting $setting): string
    {
        $value = $this->value($setting);

        return is_string($value) && SemanticVersion::isValid($value) ? $value : (string) $setting->default();
    }

    private function maintenanceMessage(): string
    {
        $messages = $this->value(AppConfigSetting::MaintenanceMessage);
        $locale = app()->getLocale();

        if (is_array($messages)) {
            foreach ([$locale, (string) config('app.fallback_locale')] as $candidate) {
                if (is_string($messages[$candidate] ?? null) && trim($messages[$candidate]) !== '') {
                    return $messages[$candidate];
                }
            }
        }

        return __('We are making improvements. Please try again shortly.');
    }

    private function url(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return preg_match('~^https?://~i', $value) === 1 ? $value : rtrim((string) config('app.url'), '/').'/'.ltrim($value, '/');
    }
}
