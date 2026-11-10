<?php

namespace App\Console\Commands\Fulfillment;

use App\Domains\Fulfillment\Services\DigitalCodeKeyRotator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('digital-codes:rotate-keys')]
#[Description('Re-encrypt digital codes that are not on the active key')]
class RotateDigitalCodeKeysCommand extends Command
{
    public function handle(DigitalCodeKeyRotator $rotator): int
    {
        $this->info('Re-encrypted '.$rotator->rotate().' codes.');

        return self::SUCCESS;
    }
}
