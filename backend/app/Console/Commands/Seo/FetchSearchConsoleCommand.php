<?php

namespace App\Console\Commands\Seo;

use App\Domains\Seo\SearchConsole\SearchConsoleException;
use App\Domains\Seo\SearchConsole\SearchConsoleSync;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:fetch-search-console {--days=3 : Days of final data to (re)read, 1 to 90}')]
#[Description('Fetch Search Console clicks, impressions, CTR and position into search_console_metrics')]
class FetchSearchConsoleCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SearchConsoleSync $sync): int
    {
        if (! $sync->ready()) {
            $this->info('Search Console is not connected (awaiting credentials); nothing fetched.');

            return self::SUCCESS;
        }

        try {
            $rows = $sync->sync((int) $this->option('days'));
        } catch (SearchConsoleException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Stored {$rows} Search Console rows.");

        return self::SUCCESS;
    }
}
