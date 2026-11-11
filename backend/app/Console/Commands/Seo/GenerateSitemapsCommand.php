<?php

namespace App\Console\Commands\Seo;

use App\Domains\Seo\Sitemaps\SitemapGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('seo:generate-sitemaps')]
#[Description('Write the sitemap index and its children per type and language (SEO_STRATEGY.md section 5)')]
class GenerateSitemapsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(SitemapGenerator $sitemaps): int
    {
        $files = $sitemaps->generate();

        $this->info('Wrote the sitemap index and '.count($files).' child sitemaps.');

        return self::SUCCESS;
    }
}
