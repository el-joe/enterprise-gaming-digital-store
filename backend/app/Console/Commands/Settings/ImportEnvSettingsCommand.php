<?php

namespace App\Console\Commands\Settings;

use App\Domains\Settings\Services\EnvSettingsImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('settings:import-env
    {--force : Overwrite settings that already have a value in the database}')]
#[Description('Copy the current .env values of the keys that moved to the admin settings into the database, once')]
class ImportEnvSettingsCommand extends Command
{
    /**
     * Prints only key names and a status, never a value.
     */
    public function handle(EnvSettingsImporter $importer): int
    {
        if ($this->laravel->configurationIsCached()) {
            $this->error('Configuration is cached, so the environment file is not read. Run php artisan config:clear first.');

            return self::FAILURE;
        }

        $results = $importer->import((bool) $this->option('force'));

        $this->table(['.env key', 'Setting', 'Result'], array_map(static fn (array $result): array => [$result['env'], $result['setting'], $result['status']], $results));

        $counts = array_count_values(array_column($results, 'status'));
        $this->info(sprintf(
            'Imported %d, skipped %d, empty %d, invalid %d. Remove the imported lines from .env; the admin settings are now the source of truth.',
            $counts[EnvSettingsImporter::IMPORTED] ?? 0,
            $counts[EnvSettingsImporter::SKIPPED] ?? 0,
            $counts[EnvSettingsImporter::EMPTY] ?? 0,
            $counts[EnvSettingsImporter::INVALID] ?? 0,
        ));

        return ($counts[EnvSettingsImporter::INVALID] ?? 0) > 0 ? self::FAILURE : self::SUCCESS;
    }
}
