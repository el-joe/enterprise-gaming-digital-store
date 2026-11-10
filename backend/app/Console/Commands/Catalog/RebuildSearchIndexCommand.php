<?php

namespace App\Console\Commands\Catalog;

use App\Domains\Catalog\Services\SearchIndexer;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('catalog:rebuild-search-index')]
#[Description('Rebuild search_documents for every product, platform and category')]
class RebuildSearchIndexCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SearchIndexer $indexer): int
    {
        $this->info('Indexed '.$indexer->rebuild().' entities.');

        return self::SUCCESS;
    }
}
