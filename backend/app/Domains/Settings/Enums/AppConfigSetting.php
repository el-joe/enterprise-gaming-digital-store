<?php

namespace App\Domains\Settings\Enums;

use App\Domains\Shared\Rules\SafeUrl;
use App\Domains\Shared\ValueObjects\SemanticVersion;

/**
 * Public mobile app settings (M-A4, `GET /api/v1/app/config`): settings group `app`, defaults in
 * `config('mobile.app')`. Never encrypted, because every value is published.
 */
enum AppConfigSetting: string
{
    public const string GROUP = 'app';

    case AndroidMinVersion = 'android_min_version';
    case AndroidLatestVersion = 'android_latest_version';
    case IosMinVersion = 'ios_min_version';
    case IosLatestVersion = 'ios_latest_version';
    case Maintenance = 'maintenance';
    case MaintenanceMessage = 'maintenance_message';
    case CheckoutEnabled = 'checkout_enabled';
    case GuestCheckoutEnabled = 'guest_checkout_enabled';
    case WalletEnabled = 'wallet_enabled';
    case ReviewsEnabled = 'reviews_enabled';
    case SupportEnabled = 'support_enabled';
    case SupportUrl = 'support_url';
    case TermsUrl = 'terms_url';
    case PrivacyUrl = 'privacy_url';
    case RefundUrl = 'refund_url';

    public function type(): SettingType
    {
        return match ($this) {
            self::Maintenance, self::CheckoutEnabled, self::GuestCheckoutEnabled, self::WalletEnabled, self::ReviewsEnabled, self::SupportEnabled => SettingType::Bool,
            self::MaintenanceMessage => SettingType::Json,
            default => SettingType::String,
        };
    }

    public function default(): mixed
    {
        return config('mobile.app.'.$this->value);
    }

    public function isVersion(): bool
    {
        return in_array($this, [self::AndroidMinVersion, self::AndroidLatestVersion, self::IosMinVersion, self::IosLatestVersion], true);
    }

    public function isUrl(): bool
    {
        return in_array($this, [self::SupportUrl, self::TermsUrl, self::PrivacyUrl, self::RefundUrl], true);
    }

    /**
     * Parse a value typed by an operator, or return the reason it is refused.
     *
     * @return array{value: mixed, error: string|null}
     */
    public function parse(string $raw): array
    {
        if ($this->isVersion()) {
            return SemanticVersion::isValid($raw) ? ['value' => $raw, 'error' => null] : ['value' => null, 'error' => 'Give a semantic version such as 1.4.0.'];
        }

        if ($this->type() === SettingType::Bool) {
            $flag = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            return $flag === null ? ['value' => null, 'error' => 'Give true or false.'] : ['value' => $flag, 'error' => null];
        }

        if ($this === self::MaintenanceMessage) {
            $decoded = json_decode($raw, true);
            $valid = is_array($decoded) && $decoded !== [] && array_is_list($decoded) === false
                && array_diff(array_keys($decoded), (array) config('app.supported_locales')) === []
                && array_filter($decoded, static fn (mixed $text): bool => ! is_string($text) || mb_strlen($text) > 500) === [];

            return $valid ? ['value' => $decoded, 'error' => null] : ['value' => null, 'error' => 'Give a JSON object of locale to text, for example {"en":"Back soon","ar":"نعود قريبا"} (500 characters at most each).'];
        }

        $error = null;
        (new SafeUrl)->validate($this->value, $raw, static function (string $message) use (&$error): void {
            $error = $message;
        });

        return $error === null && mb_strlen($raw) <= 500 ? ['value' => $raw, 'error' => null] : ['value' => null, 'error' => 'Give an http(s) URL or a site path of 500 characters at most.'];
    }
}
