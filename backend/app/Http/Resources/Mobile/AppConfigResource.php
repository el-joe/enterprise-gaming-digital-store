<?php

namespace App\Http\Resources\Mobile;

use App\Domains\Settings\DTOs\AppConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * `GET /api/v1/app/config` (M-A4). Public data only (SECURITY.md section 5): versions, flags,
 * locales, currencies, URLs and publishable provider identifiers.
 *
 * @property-read AppConfig $resource
 */
class AppConfigResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $config = $this->resource;

        return [
            'versions' => $config->versions,
            'update' => $config->update === null ? null : [
                'platform' => $config->update->platform,
                'version' => $config->update->version,
                'required' => $config->update->required,
                'available' => $config->update->available,
            ],
            'maintenance' => ['enabled' => $config->maintenance, 'message' => $config->maintenanceMessage],
            'features' => $config->features,
            'locales' => ['supported' => $config->locales, 'default' => $config->defaultLocale],
            'currencies' => ['base' => $config->baseCurrency, 'supported' => $config->currencies],
            'media' => ['base_url' => $config->mediaBaseUrl],
            'links' => $config->links,
            'payment_gateways' => (object) array_map(static fn (array $values): object => (object) $values, $config->paymentGateways),
        ];
    }
}
