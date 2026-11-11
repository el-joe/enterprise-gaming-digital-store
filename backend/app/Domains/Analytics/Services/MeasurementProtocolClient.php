<?php

namespace App\Domains\Analytics\Services;

use App\Domains\Analytics\Exceptions\MeasurementProtocolException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * GA4 Measurement Protocol (INTEGRATIONS.md section 6): `POST {host}/mp/collect` with the
 * measurement id and API secret in the query string. GA answers 2xx even for malformed data;
 * the request body is built by AnalyticsEventDispatcher within the documented limits. Awaiting
 * credentials: tested with `Http::fake` only, never called live.
 */
class MeasurementProtocolClient
{
    public function __construct(private AnalyticsSettings $settings) {}

    /**
     * @param  array<string, mixed>  $body
     *
     * @throws MeasurementProtocolException
     */
    public function send(array $body): void
    {
        $url = $this->settings->measurementProtocolHost().'/mp/collect?'.http_build_query([
            'measurement_id' => (string) $this->settings->measurementId(),
            'api_secret' => (string) $this->settings->apiSecret(),
        ]);

        try {
            $response = Http::acceptJson()->asJson()->connectTimeout(5)->timeout(10)->post($url, $body);
        } catch (ConnectionException) {
            throw new MeasurementProtocolException('The GA4 Measurement Protocol could not be reached.');
        }

        if (! $response->successful()) {
            throw new MeasurementProtocolException('The GA4 Measurement Protocol answered HTTP '.$response->status().'.');
        }
    }
}
