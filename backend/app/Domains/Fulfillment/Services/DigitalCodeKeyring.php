<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyMissing;
use Illuminate\Encryption\Encrypter;

/**
 * AES-256-GCM encrypters by key id, from DIGITAL_CODE_KEYS, independent of APP_KEY (ADR-010).
 */
class DigitalCodeKeyring
{
    private const string CIPHER = 'aes-256-gcm';

    /**
     * @var array<int, Encrypter>|null
     */
    private ?array $encrypters = null;

    public function activeKid(): int
    {
        $kid = config('fulfillment.active_kid');

        if (! is_numeric($kid) || ! isset($this->encrypters()[(int) $kid])) {
            throw new DigitalCodeKeyMissing('The active digital code key is not configured.');
        }

        return (int) $kid;
    }

    public function encrypter(int $kid): Encrypter
    {
        return $this->encrypters()[$kid] ?? throw new DigitalCodeKeyMissing("Digital code key {$kid} is not configured.");
    }

    public function blindIndex(string $normalizedCode): string
    {
        $key = config('fulfillment.blind_index_key');

        if (! is_string($key) || strlen($key) < 32) {
            throw new DigitalCodeKeyMissing('The digital code blind index key is not configured.');
        }

        return hash_hmac('sha256', $normalizedCode, $key);
    }

    /**
     * @return list<int>
     */
    public function kids(): array
    {
        return array_keys($this->encrypters());
    }

    /**
     * @return array<int, Encrypter>
     */
    private function encrypters(): array
    {
        if ($this->encrypters !== null) {
            return $this->encrypters;
        }

        $encrypters = [];

        foreach (array_filter(explode(',', (string) config('fulfillment.code_keys'))) as $pair) {
            [$kid, $key] = array_pad(explode(':', trim($pair), 2), 2, '');
            $raw = base64_decode(str_starts_with($key, 'base64:') ? substr($key, 7) : $key, true);

            if (! ctype_digit($kid) || $raw === false || ! Encrypter::supported($raw, self::CIPHER)) {
                throw new DigitalCodeKeyMissing('DIGITAL_CODE_KEYS contains an invalid entry.');
            }

            $encrypters[(int) $kid] = new Encrypter($raw, self::CIPHER);
        }

        return $this->encrypters = $encrypters;
    }
}
