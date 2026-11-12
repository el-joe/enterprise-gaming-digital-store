<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyMissing;
use Illuminate\Encryption\Encrypter;

/**
 * AES-256-GCM encrypters by key id, independent of APP_KEY (ADR-010). The keys come from
 * `fulfillment.code_keys`, `fulfillment.active_kid` and `fulfillment.blind_index_key`, which
 * SettingsOverlay fills from the encrypted digital_codes settings (owner decision 11). The parsed
 * keyring follows the config value, so a rotation applied in a long-running worker is picked up.
 */
class DigitalCodeKeyring
{
    public const string CIPHER = 'aes-256-gcm';

    public const int MIN_BLIND_INDEX_LENGTH = 32;

    /**
     * @var array<int, Encrypter>|null
     */
    private ?array $encrypters = null;

    private ?string $parsedFrom = null;

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

        if (! is_string($key) || strlen($key) < self::MIN_BLIND_INDEX_LENGTH) {
            throw new DigitalCodeKeyMissing('The digital code blind index key is not configured.');
        }

        return self::blindIndexWith($normalizedCode, $key);
    }

    public static function blindIndexWith(string $normalizedCode, string $key): string
    {
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
     * The raw keys of a keyring string (`kid:base64key` pairs, comma-separated), by key id.
     *
     * @return array<int, string>
     *
     * @throws DigitalCodeKeyMissing
     */
    public static function parse(string $keyring): array
    {
        $keys = [];

        foreach (array_filter(explode(',', $keyring)) as $pair) {
            [$kid, $key] = array_pad(explode(':', trim($pair), 2), 2, '');
            $raw = base64_decode(str_starts_with($key, 'base64:') ? substr($key, 7) : $key, true);

            if (! ctype_digit($kid) || $raw === false || ! Encrypter::supported($raw, self::CIPHER)) {
                throw new DigitalCodeKeyMissing('The digital code keyring contains an invalid entry.');
            }

            $keys[(int) $kid] = $raw;
        }

        return $keys;
    }

    /**
     * @return array<int, Encrypter>
     */
    private function encrypters(): array
    {
        $keyring = (string) config('fulfillment.code_keys');

        if ($this->encrypters !== null && $this->parsedFrom === $keyring) {
            return $this->encrypters;
        }

        $encrypters = [];

        foreach (self::parse($keyring) as $kid => $raw) {
            $encrypters[$kid] = new Encrypter($raw, self::CIPHER);
        }

        $this->parsedFrom = $keyring;

        return $this->encrypters = $encrypters;
    }
}
