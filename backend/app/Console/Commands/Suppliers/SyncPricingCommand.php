<?php

namespace App\Console\Commands\Suppliers;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;

#[Signature('suppliers:sync-pricing {--supplier= : One supplier id}')]
#[Description('Queue a pricing sync of mapped products for active suppliers')]
class SyncPricingCommand extends SyncSuppliersCommand
{
    protected function type(): string
    {
        return 'pricing';
    }
}
