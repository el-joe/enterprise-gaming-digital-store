<?php

namespace App\Console\Commands\Fulfillment;

use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyChangeRefused;
use App\Domains\Fulfillment\Services\DigitalCodeKeyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('codes:rotate-key
    {--no-reencrypt : Only add and activate the new key; codes on older keys stay readable and can be re-encrypted later with digital-codes:rotate-keys}
    {--chunk=200 : Codes re-encrypted per batch}')]
#[Description('Add a new code-vault key, make it active and re-encrypt the codes on older keys')]
class RotateDigitalCodeKeyCommand extends Command
{
    /**
     * Prints key ids and counts only, never a key.
     */
    public function handle(DigitalCodeKeyManager $keys): int
    {
        try {
            $result = $keys->rotate(! $this->option('no-reencrypt'), max(1, (int) $this->option('chunk')));
        } catch (DigitalCodeKeyChangeRefused $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Key {$result['kid']} is active (was {$result['previous_kid']}); the keyring holds {$result['key_count']} keys. Audited.");
        $this->line($result['reencrypted'] === null
            ? 'Codes on older keys were not re-encrypted; they stay readable. Run php artisan digital-codes:rotate-keys to re-encrypt them.'
            : "Re-encrypted {$result['reencrypted']} codes.");

        return self::SUCCESS;
    }
}
