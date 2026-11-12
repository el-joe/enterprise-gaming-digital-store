<?php

namespace App\Domains\Suppliers\Services;

use App\Domains\Cms\Exceptions\ImageRejected;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Downloads one supplier image (owner decision 12) under the SSRF rules of SECURITY.md section 2:
 * HTTPS only, the host exactly on `suppliers.image_hosts`, no credentials or port in the URL,
 * redirects never followed, `suppliers.image_timeout_seconds`, at most `suppliers.image_max_kilobytes`
 * read from the stream, and an `image/*` content type other than SVG. The bytes still go through
 * ImageOptimizer, which decodes them with GD.
 */
class SupplierImageDownloader
{
    /**
     * @throws ImageRejected for a URL or response that is refused for good
     * @throws ConnectionException for a network failure worth retrying
     */
    public function download(string $url): string
    {
        $this->assertAllowed($url);
        $limit = (int) config('suppliers.image_max_kilobytes') * 1024;

        $response = Http::withOptions(['allow_redirects' => false, 'stream' => true])
            ->timeout((int) config('suppliers.image_timeout_seconds'))
            ->connectTimeout((int) config('suppliers.image_timeout_seconds'))
            ->accept('image/avif,image/webp,image/png,image/jpeg,image/gif')
            ->get($url);

        if ($response->status() >= 500) {
            throw new ConnectionException('Supplier image host answered '.$response->status());
        }

        if ($response->status() !== 200) {
            throw new ImageRejected($response->status() >= 300 && $response->status() < 400 ? 'redirect' : 'http_'.$response->status());
        }

        $type = strtolower(trim(explode(';', (string) $response->header('Content-Type'))[0]));

        if (! str_starts_with($type, 'image/')) {
            throw new ImageRejected('not_image');
        }

        if (str_contains($type, 'svg')) {
            throw new ImageRejected('svg');
        }

        if ((int) $response->header('Content-Length') > $limit) {
            throw new ImageRejected('too_large');
        }

        $body = $response->toPsrResponse()->getBody();
        $bytes = '';

        if ($body->isSeekable()) {
            $body->rewind();
        }

        while (! $body->eof() && strlen($bytes) <= $limit) {
            $bytes .= $body->read(65536);
        }

        if (strlen($bytes) > $limit) {
            throw new ImageRejected('too_large');
        }

        return $bytes;
    }

    /**
     * @throws ImageRejected
     */
    public function assertAllowed(string $url): void
    {
        $parts = parse_url($url);

        if ($parts === false || strtolower($parts['scheme'] ?? '') !== 'https') {
            throw new ImageRejected('not_https');
        }

        $host = strtolower((string) ($parts['host'] ?? ''));
        $allowed = array_map('strtolower', (array) config('suppliers.image_hosts'));

        if ($host === '' || isset($parts['user']) || isset($parts['pass']) || isset($parts['port']) || ! in_array($host, $allowed, true)) {
            throw new ImageRejected('host');
        }
    }
}
