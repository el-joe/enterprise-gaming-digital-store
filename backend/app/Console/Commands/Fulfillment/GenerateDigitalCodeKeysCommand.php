<?php

namespace App\Console\Commands\Fulfillment;

use App\Domains\Fulfillment\Exceptions\DigitalCodeKeyChangeRefused;
use App\Domains\Fulfillment\Services\DigitalCodeKeyManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('codes:generate-keys')]
#[Description('First install: generate the code-vault keyring and blind index key (encrypted with APP_KEY in the settings)')]
class GenerateDigitalCodeKeysCommand extends Command
{
    /**
     * Prints the key id and counts only, never a key.
     */
    public function handle(DigitalCodeKeyManager $keys): int
    {
        try {
            $keys->generate();
        } catch (DigitalCodeKeyChangeRefused $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $status = $keys->status();
        $this->info("Code vault keys generated: active key {$status['active_kid']}, {$status['key_count']} key(s), blind index set. Audited.");
        $this->line('They are encrypted with APP_KEY: back up APP_KEY separately from the database (docs/SECURITY.md 5).');

        return self::SUCCESS;
    }
}
