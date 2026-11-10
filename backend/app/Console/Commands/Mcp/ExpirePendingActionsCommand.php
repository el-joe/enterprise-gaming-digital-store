<?php

namespace App\Console\Commands\Mcp;

use App\Domains\Mcp\Services\McpPendingActionService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('mcp:expire-pending-actions {--limit=500 : Most actions to expire in one run}')]
#[Description('Mark MCP pending actions past their expiry as expired')]
class ExpirePendingActionsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(McpPendingActionService $actions): int
    {
        $this->info('Expired '.$actions->expireDue((int) $this->option('limit')).' pending actions.');

        return self::SUCCESS;
    }
}
