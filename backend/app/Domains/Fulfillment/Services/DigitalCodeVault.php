<?php

namespace App\Domains\Fulfillment\Services;

use App\Domains\Fulfillment\DTOs\RevealedCode;
use App\Domains\Fulfillment\Enums\DigitalCodeSource;
use App\Domains\Fulfillment\Exceptions\DuplicateDigitalCode;
use App\Domains\Fulfillment\Models\DigitalCode;
use App\Domains\Suppliers\DTOs\DeliverableItem;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

/**
 * The only place code material is encrypted or decrypted (architecture section 10). Code, PIN and
 * redemption URL are encrypted separately with the active key; duplicates are found through an
 * HMAC blind index; only the last four characters are kept in clear as the mask.
 */
class DigitalCodeVault
{
    public function __construct(private DigitalCodeKeyring $keys) {}

    /**
     * @param  array<string, mixed>  $attributes  non-secret columns (unit, transaction, variant, stock status, batch)
     *
     * @throws DuplicateDigitalCode
     */
    public function store(DeliverableItem $secret, DigitalCodeSource $source, array $attributes = []): DigitalCode
    {
        $kid = $this->keys->activeKid();
        $encrypter = $this->keys->encrypter($kid);
        $code = trim($secret->code);

        $row = (new DigitalCode)->forceFill([
            ...$attributes,
            'source' => $source,
            'code_ciphertext' => $encrypter->encryptString($code),
            'pin_ciphertext' => $secret->pin === null ? null : $encrypter->encryptString($secret->pin),
            'redemption_url_ciphertext' => $secret->redemptionUrl === null ? null : $encrypter->encryptString($secret->redemptionUrl),
            'key_version' => $kid,
            'code_hash' => $this->hashFor($code),
            'masked_code' => self::mask($code),
        ]);

        try {
            DB::transaction(static fn () => $row->save());
        } catch (UniqueConstraintViolationException $exception) {
            if (str_contains($exception->getMessage(), 'uq_dc_hash')) {
                throw new DuplicateDigitalCode;
            }

            throw $exception;
        }

        return $row;
    }

    /**
     * Decrypt for an already authorised and audited reveal. Callers: CodeRevealService only.
     */
    public function decrypt(DigitalCode $code): RevealedCode
    {
        $encrypter = $this->keys->encrypter($code->key_version);
        $row = DigitalCode::query()->whereKey($code->id)->firstOrFail(['code_ciphertext', 'pin_ciphertext', 'redemption_url_ciphertext']);

        return new RevealedCode(
            $encrypter->decryptString($row->code_ciphertext),
            $row->pin_ciphertext === null ? null : $encrypter->decryptString($row->pin_ciphertext),
            $row->redemption_url_ciphertext === null ? null : $encrypter->decryptString($row->redemption_url_ciphertext),
        );
    }

    /**
     * Re-encrypt one row with the active key. Returns false when it already uses it.
     */
    public function reencrypt(DigitalCode $code): bool
    {
        $active = $this->keys->activeKid();

        if ($code->key_version === $active) {
            return false;
        }

        $plain = $this->decrypt($code);
        $encrypter = $this->keys->encrypter($active);

        DigitalCode::query()->whereKey($code->id)->where('key_version', $code->key_version)->update([
            'code_ciphertext' => $encrypter->encryptString($plain->code),
            'pin_ciphertext' => $plain->pin === null ? null : $encrypter->encryptString($plain->pin),
            'redemption_url_ciphertext' => $plain->redemptionUrl === null ? null : $encrypter->encryptString($plain->redemptionUrl),
            'key_version' => $active,
        ]);

        return true;
    }

    public function hashFor(string $code): string
    {
        return $this->keys->blindIndex(self::normalize($code));
    }

    /**
     * Codes compare equal regardless of case, spaces and hyphens.
     */
    public static function normalize(string $code): string
    {
        return strtoupper((string) preg_replace('/[\s-]+/', '', $code));
    }

    public static function mask(string $code): string
    {
        $compact = (string) preg_replace('/\s+/', '', $code);

        return '****'.mb_substr($compact, max(0, mb_strlen($compact) - 4));
    }
}
