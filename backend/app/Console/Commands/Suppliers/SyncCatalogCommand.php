<?php

namespace App\Console\Commands\Suppliers;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('suppliers:sync-catalog {--supplier= : One supplier id}')]
#[Description('Queue a catalog sync for active suppliers')]
class SyncCatalogCommand extends SyncSuppliersCommand
{
    protected function type(): string
    {
        return 'catalog';
    }
}
