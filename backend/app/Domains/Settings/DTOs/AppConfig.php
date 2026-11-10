<?php

namespace App\Domains\Settings\DTOs;

/**
 * The public mobile app configuration (M-A4, gap G9). Every field is public by construction:
 * no secret is read into this object.
 */
final readonly class AppConfig
{
    /**
     * @param  array<string, array{min_supported_version: string, latest_version: string}>  $versions
     * @param  array<string, bool>  $features
     * @param  list<string>  $locales
     * @param  list<array{code: string, name: string, symbol: string, exponent: int}>  $currencies
     * @param  array<string, string|null>  $links
     * @param  array<string, array<string, string|null>>  $paymentGateways
     */
    public function __construct(
        public array $versions,
        public ?AppUpdateStatus $update,
        public bool $maintenance,
        public ?string $maintenanceMessage,
        public array $features,
        public array $locales,
        public string $defaultLocale,
        public string $baseCurrency,
        public array $currencies,
        public string $mediaBaseUrl,
        public array $links,
        public array $paymentGateways,
    ) {}
}
